<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $response = $this->authService->register(
            $request->validated(),
            $request->ip()
        );

        return response()->json($response);
    }

    public function login(LoginRequest $request)
    {
        $response = $this->authService->login($request->only('email' , 'password'));

        return response()->json($response);
    }

    public function logout()
    {
        $response = $this->authService->logout();

        return response()->json($response);
    }

    public function logoutAll()
    {
        $response = $this->authService->logoutAll();

        return response()->json($response);
    }

    public function refreshToken(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return response()->json([
                'message' => 'There is no refresh token'
            ], 400);
        }

        $response = $this->authService->refreshToken([
            'user' => Auth::user(),
            'token' => $refreshToken
        ]);

        return response()->json($response);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $response = $this->authService->resetPassword($request->validated());

        return response()->json($response);
    }

    public function forgetSecurityKey(Request $request)
    {
        $response = $this->authService->forgetSecurityKey($request->only('email'));

        return response()->json($response);
    }
}

?>
