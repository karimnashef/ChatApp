<?php

namespace App\Policies;

use App\Models\User;

class UserPolicies
{
    public function view()
    {
        return true;
    }

    public function update(User $user, User $model)
    {
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model)
    {
        return $user->id === $model->id;
    }
}
