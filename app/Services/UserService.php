<?php

namespace App\Services;

use App\Http\Resources\UserResources;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function getAll()
    {
        return $this->userRepository->all();
    }

    public function getById(string $id)
    {
        $user = $this->userRepository->find($id);

        return UserResources::make($user);
    }

    public function update(array $data, string $id)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->userRepository->update($id, $data);

        return UserResources::make($user);
    }

    public function delete(string $id)
    {
        return $this->userRepository->delete($id);
    }
}
