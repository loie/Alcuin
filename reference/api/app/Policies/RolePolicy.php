<?php

namespace App\Policies;

use App\User;
use App\Role;

class RolePolicy
{

    public function create (User $user) {
        return true;
    }

    public function view (User $user, $role) {
        if (is_string($role)) {
            return true;
        }
        return $user->id === $role->id;
    }

    public function update (User $user, Role $role) {
        return $user->id === $role->id;
    }
    
    public function delete (User $user, Role $role) {
        return $user->id === $role->id;
    }
}