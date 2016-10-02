<?php

namespace App\Providers;

use App\User;
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