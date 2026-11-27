<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class MarketplaceNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public array $data = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'data' => $this->data,
        ];
    }
}
