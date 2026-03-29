<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService
    ) {}

    public function index()
    {
        return response()->json($this->userRepository->all());
    }

    public function show($id)
    {
        $user = $this->userRepository->find($id);
        return response()->json($user);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $response = $this->userService->update($id, $request->validated());

        return response()->json($response);
        $user = $this->userRepository->find($id);}

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json($user);
    }

    public function destroy(string $id)
    {
        $user = $this->userRepository->find($id);
        $user->delete();
        return response()->json(['message' => 'User deleted'], 200);
    }
}
