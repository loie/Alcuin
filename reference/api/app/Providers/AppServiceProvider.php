<?php

namespace App\Providers;

use App\User;
use App\Role;
use App\Question;
use App\Answer;
use App\Tag;
use App\Observers\UserObserver;
use App\Observers\RoleObserver;
use App\Observers\QuestionObserver;
use App\Observers\AnswerObserver;
use App\Observers\TagObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Answer::observe(AnswerObserver::class);
        Question::observe(QuestionObserver::class);
        Role::observe(RoleObserver::class);
        Tag::observe(TagObserver::class);
        User::observe(UserObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}