<?php

namespace App\Providers;

use App\Answer;
use App\Question;
use App\Tag;
use App\Role;
use App\User;
use App\Policies\AnswerPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\TagPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        Gate::policy(Answer::class, AnswerPolicy::class);
        Gate::policy(Question::class, QuestionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Auth::viaRequest('api', function ($request) {
            $user = null;
            $email = $request->hasHeader('X-email') ? $request->header('X-email') : null;
            $token = $request->hasHeader('X-token') ? $request->header('X-token') : null;
            try {
                $user = User::where('email', $email)->where('token', $token)->firstOrFail();
            } catch (ModelNotFoundException $e) {
                
            }
            return $user;
        });
    }
}
