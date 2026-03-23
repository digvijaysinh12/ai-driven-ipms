<?php

namespace App\Policies;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TopicPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'mentor' || $user->role->name === 'hr';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Topic $topic): bool
    {
        return $user->role->name === 'hr' || ($user->role->name === 'mentor' && $user->id === $topic->mentor_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->name === 'mentor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Topic $topic): bool
    {
        return $user->role->name === 'mentor' && $user->id === $topic->mentor_id && in_array($topic->status, ['draft', 'ai_generated']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Topic $topic): bool
    {
        return $user->role->name === 'mentor' && $user->id === $topic->mentor_id && $topic->status === 'draft';
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Topic $topic): bool
    {
        return $user->role->name === 'mentor' && $user->id === $topic->mentor_id && in_array($topic->status, ['ai_generated', 'reviewed']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Topic $topic): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Topic $topic): bool
    {
        return false;
    }
}
