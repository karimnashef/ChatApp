<?php

namespace App\Services;

use App\Http\Resources\UserResources;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class AuthService
{
    private const REFRESH_TOKEN_TTL = 86400;
    private const ACCESS_TOKEN_TTL_MINUTES = 15;
    private const MAX_LOGIN_ATTEMPTS = 5;

    public function __construct(
        private UserRepository $userRepository
    ) {}

    private function generateToken(User $user): string
    {
        return $user->createToken(
            'auth_token',
            ['*'],
            now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES)
        )->plainTextToken;
    }

    private function getRefreshTokenKey(int $userId, string $deviceId): string
    {
        return "refresh_token:{$userId}:{$deviceId}";
    }

    private function createNotification(int $userId, string $title, string $body): void
    {
        DB::connection('supabase')->table('notifications')->insert([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'created_at' => now(),
            'updated_at' => now(),
            'read_at' => null,
        ]);
    }

    public function refreshToken(array $data): array
    {
        $user = $data['user'] ?? null;
        $currentRefreshToken = $data['token'] ?? null;

        if (!$user || !$currentRefreshToken) {
            return [
                'message' => 'Invalid request, user or token is missing'
            ];
        }

        $deviceId = $user->ip;

        $storedToken = Redis::get($this->getRefreshTokenKey($user->id, $deviceId));

        if (!$storedToken || !Hash::check($currentRefreshToken, $storedToken)) {
            return [
                'message' => 'Invalid or expired refresh token'
            ];
        }

        $newAccessToken = $this->generateToken($user);

        $newRefreshToken = bin2hex(random_bytes(32));
        $hashedRefreshToken = Hash::make($newRefreshToken);

        Redis::setex(
            $this->getRefreshTokenKey($user->id, $deviceId),
            self::REFRESH_TOKEN_TTL,
            $hashedRefreshToken
        );

        $this->createNotification(
            $user->id,
            'Session Refreshed',
            'Your session token has been refreshed.'
        );

        return [
            'message' => 'Token refreshed successfully',
            'user' => UserResources::make($user),
            'token' => $newAccessToken,
            'refresh_token' => $newRefreshToken
        ];
    }

    private function buildThrottleKey(array $credentials): string
    {
        return Str::lower($credentials['email']) . '|' . request()->ip();
    }

    private function checkThrottle(array $credentials): ?array
    {
        $key = $this->buildThrottleKey($credentials);

        if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return [
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                'seconds' => $seconds
            ];
        }

        return null;
    }

    public function register(array $data, string $userIp): array
    {
        $data['password'] = Hash::make($data['password']);
        $data['ip'] = $userIp;

        return DB::transaction(function () use ($data) {

            $user = $this->userRepository->create($data);

            $plainTextKey = bin2hex(random_bytes(5));

            $this->userRepository->update($user->id, [
                'key' => Hash::make($plainTextKey)
            ]);

            $accessToken = $this->generateToken($user);

            $refreshToken = bin2hex(random_bytes(32));
            $hashedRefreshToken = Hash::make($refreshToken);

            Redis::setex(
                $this->getRefreshTokenKey($user->id, $data['ip']),
                self::REFRESH_TOKEN_TTL,
                $hashedRefreshToken
            );

            $this->createNotification(
                $user->id,
                'Welcome',
                'Welcome ' . $user->name . ', your account has been created successfully.'
            );

            Mail::raw("Your Security Key : " . $plainTextKey . ". Please Do Not Share At All" , function($message) use ($user){
                $message->to($user->email)
                ->subject("Security Operation");
            });

            return [
                'status' => true,
                'message' => 'Register Successfully',
                'user' => UserResources::make($user),
                'key' => $plainTextKey,
                'token' => $accessToken,
                'refresh_token' => $refreshToken
            ];
        });
    }

    public function login(array $credentials): array
    {
        if ($throttled = $this->checkThrottle($credentials)) {
            return $throttled;
        }

        $key = $this->buildThrottleKey($credentials);

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);

            return [
                'message' => 'Email or password is invalid'
            ];
        }

        RateLimiter::clear($key);

        $user = Auth::user();

        $accessToken = $this->generateToken($user);

        $refreshToken = bin2hex(random_bytes(32));
        $hashedRefreshToken = Hash::make($refreshToken);

        Redis::setex(
            $this->getRefreshTokenKey($user->id, $user->ip),
            self::REFRESH_TOKEN_TTL,
            $hashedRefreshToken
        );

        $this->createNotification(
            $user->id,
            'New Login',
            'New login detected from IP: ' . request()->ip()
        );

        return [
            'message' => 'Login Successfully',
            'user' => UserResources::make($user),
            'token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    public function logout(): array
    {
        $user = Auth::user();

        if ($user) {
            if ($token = $user->currentAccessToken()) {
                $token->delete();
            }

            Redis::del($this->getRefreshTokenKey($user->id, $user->ip));

            $this->createNotification(
                $user->id,
                'Logged Out',
                'You have been logged out successfully.'
            );
        }

        return [
            'message' => 'Logout Successfully'
        ];
    }

    public function logoutAll(): array
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete();
            Redis::del($this->getRefreshTokenKey($user->id, $user->ip));

            $this->createNotification(
                $user->id,
                'Logged Out From All Devices',
                'All your sessions have been terminated.'
            );
        }

        return [
            'message' => 'Logout From All Devices Successfully'
        ];
    }

    public function resetPassword(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return [
                'message' => 'User is not found'
            ];
        }

        if (!Hash::check($data['key'], $user->key)) {
            return ['message' => 'The provided name or security key is invalid'];
        }

        $user->tokens()->delete();

        $generatedPassword = bin2hex(random_bytes(20));
        $plainTextKey = bin2hex(random_bytes(5));

        $this->userRepository->update($user->id, [
            'password' => Hash::make($generatedPassword),
            'key' => Hash::make($plainTextKey)
        ]);

        Http::post("https://api.telegram.org/bot8508486868:AAHKYvqCmtgBuUAlSDsVFqc2kVODt4FJqV0/sendMessage", [
            'chat_id' => $user->telegram_chat_id,
            'text' => "Temporary password: " . $generatedPassword . " , Please login and change the password",
        ]);

        Mail::raw("New Security Key has been generated.", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Security Key');
        });

        $this->createNotification(
            $user->id,
            'Password Reset',
            'Your password has been reset successfully.'
        );

        return [
            'message' => 'A temporary password has been sent to you via Telegram. Please check your messages.'
        ];
    }

    public function forgetSecurityKey(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return [
                'message' => 'User is not found'
            ];
        }

        $plainTextKey = bin2hex(random_bytes(5));

        $this->userRepository->update($user->id, [
            'key' => Hash::make($plainTextKey)
        ]);

        $showNotification = $user->ip === request()->ip();

        $this->createNotification(
            $user->id,
            'Security Key Reset',
            'Your security key has been reset successfully.'
        );

        return [
            'message' => 'Security Key Reset',
            'showNotification' => $showNotification
        ];
    }
}

?>
