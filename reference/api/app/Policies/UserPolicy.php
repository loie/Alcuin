<?php

namespace App\Policies;

use App\User;

class UserPolicy
{

    public function create (User $user) {
        return true;
    }

    public function view (User $user, $viewUser) {
        var_dump($viewUser);
        return $user->id === $viewUser->id;
    }

    public function update (User $user, User $viewUser) {
        return $user->id === $viewUser->id;
    }
    
    public function delete (User $user, User $viewUser) {
        return $user->id === $viewUser->id;
    }
}