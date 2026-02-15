<?php

namespace App\Data;

/**
 * Ujednolicony format payloadu dla Live Activity Feed na dashboardzie.
 * Każdy event broadcastowany na kanał dashboard powinien zawierać te pola.
 */
final class DashboardFeedPayload
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
        public readonly string $time,
        public readonly ?string $link = null,
    ) {}

    public function toArray(): array
    {
        return [
            'feed_type' => $this->type,
            'feed_message' => $this->message,
            'feed_time' => $this->time,
            'feed_link' => $this->link,
        ];
    }
}
