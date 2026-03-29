<?php

namespace App\Notifications;

use App\Models\Topic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopicPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $topic;

    public function __construct(Topic $topic)
    {
        $this->topic = $topic;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Topic Published')
                    ->line('A new topic has been published: ' . $this->topic->title)
                    ->action('View Topic', url('/mentor/topics/' . $this->topic->id))
                    ->line('You can now assign this topic to interns.');
    }
}