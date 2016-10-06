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
        Answer::observe(ModelObserver::class);
        Question::observe(ModelObserver::class);
        Role::observe(ModelObserver::class);
        Tag::observe(ModelObserver::class);
        User::observe(ModelObserver::class);
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