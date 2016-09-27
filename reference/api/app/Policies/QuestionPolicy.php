<?php

namespace App\Policies;

use App\User;
use App\Role;

class QuestionPolicy
{
    public function create(User $user, Question $question) {
        $isAllowed = true;
        // foreach ($user->roles as $role) {
        //     if (in_array($role->type, ['user', 'admin'])) {
        //         $isAllowed = true;
        //         break;
        //     }
        // }
        return $isAllowed;
    }

    public function view (User $user, Question $question) {
        return $user->id === $post->user_id;
    }

    public function update (User $user, Question $question) {
        return $user->id === $post->user_id;
    }
    
    public function delete (User $user, Question $question) {
        return $user->id === $post->user_id;
    }
}