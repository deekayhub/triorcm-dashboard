<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeamMemberUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public User $updatedUser,
        public ?User $updatedBy = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Team member updated',
            'message' => ($this->updatedBy ? $this->updatedBy->name : 'Someone') . ' updated ' . $this->updatedUser->name . '\'s profile.',
            'type' => 'team',
            'user_id' => $this->updatedUser->id,
            'updated_by' => $this->updatedBy?->id,
            'action_url' => route('users.index'),
        ];
    }
}
