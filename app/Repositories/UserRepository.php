<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->find($id);
        if ($user) {
            $user->update($data);
            return $user;
        }
        return null;
    }

    public function delete($id)
    {
        $user = $this->find($id);
        if ($user) {
            $user->delete();
            return true;
        }
        return false;
    }

    public function friends($id)
    {
        $user = $this->find($id);
        if($user) {
            return $user->friends()->get();
        }
        return null;
    }
    public function forceDelete($id)
    {
        $user = $this->model->withTrashed()->find($id);
        if ($user) {
            $user->forceDelete();
            return true;
        }
        return false;
    }
}

?>
