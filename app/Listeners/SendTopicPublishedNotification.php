<?php

namespace App\Listeners;

use App\Events\TopicPublished;
use App\Notifications\TopicPublishedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Notification;

class SendTopicPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TopicPublished $event)
    {
        $mentors = User::whereHas('role', function($q) {
            $q->where('name', 'mentor');
        })->where('status', 'approved')->get();

        Notification::send($mentors, new TopicPublishedNotification($event->topic));
    }
}