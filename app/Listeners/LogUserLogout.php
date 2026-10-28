<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class LogUserLogout
{
    public function handle(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        activity()
            ->causedBy($event->user)
            ->event('logout')
            ->withProperties([
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ])
            ->log('User logged out');
    }
}
