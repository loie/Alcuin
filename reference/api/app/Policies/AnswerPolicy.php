<?php

namespace App\Policies;

use App\User;
use App\Role;
use App\Answer;

class AnswerPolicy
{
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return bool
     */
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

    public function view (User $user, $answer) {
        $isAllowed = false;
        if (is_string($answer)) {
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

    public function update (User $user, Answer $answer) {
        $isAllowed = false;
        foreach ($user->roles as $role) {
            if (in_array($role->type, ['user', 'admin'])) {
                $isAllowed = true;
                break;
            }
        }
        return $isAllowed;
    }
    
    public function delete (User $user, Answer $answer) {
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