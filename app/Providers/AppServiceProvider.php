<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\User;
use App\Models\Role;
use App\Events\TopicPublished;
use App\Listeners\SendTopicPublishedNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cache roles for performance
        Cache::rememberForever('roles', function () {
            return Role::all();
        });

        Gate::define('approve-users', function (User $user) {
            return $user->role->name === 'hr';
        });

        Gate::define('assign-mentors', function (User $user) {
            return $user->role->name === 'hr';
        });

        Gate::define('view-reports', function (User $user) {
            return in_array($user->role->name, ['hr', 'mentor']);
        });

        RateLimiter::for('ai-generations', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()->id);
        });

        RateLimiter::for('code-executions', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()->id);
        });

        Event::listen(TopicPublished::class, SendTopicPublishedNotification::class);
    }
}
