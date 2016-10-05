<?php

namespace App\Policies;
use App\User;
use App\Role;

class UserPolicy
{

    public function create (User $user) {
        return true;
    }

    public function view (User $user, $viewUser) {
        if (is_string($viewUser)) {
            return true;
        }
        return $user->id === $viewUser->id;
    }

    public function update (User $user, User $viewUser) {
        echo 'destroy';
        return $user->id === $viewUser->id;
    }
    
    public function delete (User $user, User $viewUser) {
        return $user->id === $viewUser->id;
    }

    public function patch (User $user, User $viewUser) {
        echo 'asdf';
        return true;
    }
}