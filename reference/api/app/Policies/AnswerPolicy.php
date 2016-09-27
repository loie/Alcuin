<?php

namespace App\Policies;

use App\User;
use App\Role;

class AnswerPolicy
{
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return bool
     */
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