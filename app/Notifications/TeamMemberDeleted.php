<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeamMemberDeleted extends Notification
{
    use Queueable;

    public function __construct(
        public string $deletedUserName,
        public ?string $deletedUserEmail = null,
        public ?\App\Models\User $deletedBy = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Team member removed',
            'message' => ($this->deletedBy ? $this->deletedBy->name : 'Someone') . ' removed ' . $this->deletedUserName . ' from the team.',
            'type' => 'team',
            'user_name' => $this->deletedUserName,
            'user_email' => $this->deletedUserEmail,
            'deleted_by' => $this->deletedBy?->id,
            'action_url' => route('users.index'),
        ];
    }
}
