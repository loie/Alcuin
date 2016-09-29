<?php

namespace App\Policies;

use App\User;
use App\Role;

class TagPolicy
{
    public function create(User $user)
    {
        $isAllowed = false;
        foreach ($user->roles as $role) {
            if (in_array($role->type, ['user', 'admin'])) {
                $isAllowed = true;
                break;
            }
        }
        return $isAllowed;
    }

    public function view (User $user, Tag $tag) {
        $isAllowed = false;
        foreach ($user->roles as $role) {
            if (in_array($role->type, ['user', 'admin'])) {
                $isAllowed = true;
                break;
            }
        }
        return $isAllowed;
    }

    public function update (User $user, Tag $tag) {
        $isAllowed = false;
        foreach ($user->roles as $role) {
            if (in_array($role->type, ['user', 'admin'])) {
                $isAllowed = true;
                break;
            }
        }
        return $isAllowed;
    }
    
    public function delete (User $user, Tag $tag) {
        $isAllowed = false;
        foreach ($user->roles as $role) {
            if (in_array($role->type, ['user', 'admin'])) {
                $isAllowed = true;
                break;
            }
        }
        return $isAllowed;
    }
}