<?php

namespace App\Policies;

use App\User;
use App\Role;
use App\Tag;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

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

    public function view (User $user, $tag) {
        $isAllowed = false;
        if (is_string($tag)) {
            return true;
        }
        foreach ($user->roles as $role) {
            if (in_array($role->type, ['user', 'admin'])) {
                $isAllowed = true;
                break;
            }
        }
        return $isAllowed;
    }

    public function update (User $user, Tag $tag) {
        return true;
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