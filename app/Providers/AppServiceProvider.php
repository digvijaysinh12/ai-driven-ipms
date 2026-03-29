<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
<<<<<<< HEAD
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\User;
=======
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\User;
use App\Models\Role;
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
use App\Events\TopicPublished;
use App\Listeners\SendTopicPublishedNotification;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
<<<<<<< HEAD
        // Authorization gates
        Gate::define('approve-users', fn (User $user) => $user->role->name === 'hr');
        Gate::define('assign-mentors', fn (User $user) => $user->role->name === 'hr');
        Gate::define('view-reports', fn (User $user) => in_array($user->role->name, ['hr', 'mentor']));

        // Rate limiters
        RateLimiter::for('ai-generations', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id);
        });

        RateLimiter::for('code-executions', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id);
        });

        // Event listeners
=======
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

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        Event::listen(TopicPublished::class, SendTopicPublishedNotification::class);
    }
}