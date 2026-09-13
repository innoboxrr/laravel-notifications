<?php

namespace Innoboxrr\LaravelNotifications\Tests\Fixtures;

use Illuminate\Notifications\Notification;

class TestNotification extends Notification
{
    public function __construct(
        private string $message,
        private ?string $action = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return array_filter([
            'message' => $this->message,
            'action' => $this->action,
        ], fn (?string $value): bool => $value !== null);
    }
}
