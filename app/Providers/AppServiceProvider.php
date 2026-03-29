<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\User;
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
        Event::listen(TopicPublished::class, SendTopicPublishedNotification::class);
    }
}