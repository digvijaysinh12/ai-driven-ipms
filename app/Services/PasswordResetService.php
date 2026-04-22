<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class PasswordResetService
{
    public function sendResetLink(string $email): string
    {
        $userExists = User::query()->where('email', $email)->exists();

        if (! $userExists) {
            return Password::RESET_LINK_SENT;
        }

        return Password::sendResetLink(['email' => $email]);
    }

    public function invalidateUserSessions(User $user): void
    {
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
    }
}
