<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeamMemberCreated extends Notification
{
    use Queueable;

    public function __construct(
        public User $createdUser,
        public ?User $createdBy = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New team member added',
            'message' => ($this->createdBy ? $this->createdBy->name : 'Someone') . ' added ' . $this->createdUser->name . ' to the team.',
            'type' => 'team',
            'user_id' => $this->createdUser->id,
            'created_by' => $this->createdBy?->id,
            'action_url' => route('users.index'),
        ];
    }
}
