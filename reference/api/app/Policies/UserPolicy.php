<?php
namespace App\Policies;

use App\User;
use App\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create (User $user) {
        return true;
    }

    public function view (User $user, $viewUser) {
        if (is_string($viewUser)) {
            return true;
        }
        return true;
        // return $user->id === $viewUser->id;
    }

    public function update (User $user, User $viewUser) {
        return $user->id === $viewUser->id;
    }
    
    public function delete (User $user, User $viewUser) {
        return $user->id === $viewUser->id;
    }

    public function patch (User $user, User $viewUser) {
        return true;
    }

    public function viewRole (User $user, Role $role) {
        return true;
    }

    public function viewQuestion (User $user, Question $question) {
        return false;
    }
}