<?php

namespace App\Policies;

use App\Models\User;

class VcardPolicy
{
    public function viewAny(User $user, User $model)
    {
        return $user->user_type == 'A' || $user->id == $model->id;
    }

    public function view(User $user, User $model)
    {
        return $user->user_type == 'A' || $user->id == $model->id;
    }

    public function create(User $user)
    {
        return $user->user_type == 'A';
    }

    public function update(User $user, User $model)
    {
        return $user->user_type == 'A' || $user->id == $model->id;
    }

    public function delete(User $user, User $model)
    {
        return $user->user_type == 'A' || $user->id == $model->id;
    }
}
