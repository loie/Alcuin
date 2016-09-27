<?php

namespace App\Policies;

use App\User;
use App\Role;

class TagPolicy
{
    public function create(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }

    public function view (User $user, Answer $answer) {
        return $user->id === $post->user_id;
    }

    public function update (User $user, Answer $answer) {
        return $user->id === $post->user_id;
    }
    
    public function delete (User $user, Answer $answer) {
        return $user->id === $post->user_id;
    }
}