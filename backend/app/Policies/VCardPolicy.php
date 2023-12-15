<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VCard;

class VCardPolicy
{
    public function view(User $user, VCard $model)
    {
        return $user->user_type == 'A' || $user->username == $model->phone_number;
    }

    public function updatePassword(User $user, VCard $model)
    {
        return $user->username == $model->phone_number;
    }
    public function update(User $user, VCard $model)
    {
        return $user->user_type == 'A' || $user->username == $model->phone_number;
    }

    public function updateSavings(User $user, VCard $model)
    {
        return $user->username == $model->phone_number;
    }

    public function delete(User $user, VCard $model)
    {
        return $user->user_type == 'A' || $user->username == $model->phone_number;
    }
}
