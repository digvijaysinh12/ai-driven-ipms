<?php

namespace App\Providers;

use App\Listeners\RecordLoginAttendance;
use App\Listeners\RecordLogoutAttendance;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.ipms');
        Paginator::defaultSimpleView('vendor.pagination.ipms-simple');

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return rtrim(config('app.url'), '/').route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], false);
        });

        Gate::define('approve-users', fn (User $user) => $user->role?->name === 'hr');
        Gate::define('assign-mentors', fn (User $user) => $user->role?->name === 'hr');
        Gate::define('view-reports', fn (User $user) => in_array($user->role?->name, ['hr', 'mentor'], true));

        RateLimiter::for('ai-generations', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });


        Paginator::useTailwind();
    }
}
