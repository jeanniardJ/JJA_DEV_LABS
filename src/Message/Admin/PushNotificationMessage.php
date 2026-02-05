<?php

namespace App\Message\Admin;

class PushNotificationMessage
{
    public function __construct(
        private readonly string $title,
        private readonly string $body,
        private readonly string $url = '/admin/dashboard'
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
