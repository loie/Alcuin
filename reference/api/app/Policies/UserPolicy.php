<?php

namespace App\Policies;

use App\User;
use App\Post;

class UserPolicy
{

    public function create (User $user) {
        return true;
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