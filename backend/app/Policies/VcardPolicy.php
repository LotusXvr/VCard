<?php

namespace App\Policies;

use App\Models\User;

/* class VcardPolicy
{
    public function viewAny(User $user, User $model)
    {
        return $user->user_type == 'A' || $user->id == $model->id;
    }

    public function view(User $user, User $model)
    {
<<<<<<< HEAD
        return $user->user_type == 'A' || $user->id == $model->phone_number;
=======
        return $user->user_type == 'A' || $user->username == $model->username;
>>>>>>> 343db0d44002f135cf452c80668ea71ac707ea29
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

} */
