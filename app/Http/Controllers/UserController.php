<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResources;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index()
    {
        $response = $this->userService->getAll();
        return response()->json($response);
    }

    public function show($id)
    {
        $response = $this->userService->getById($id);
        return response()->json($response);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $response = $this->userService->update($request->validated() , $id );

        return response()->json($response);
    }

    public function destroy(string $id)
    {
        $response = $this->userService->delete($id);

        return response()->json($response);
    }
}
