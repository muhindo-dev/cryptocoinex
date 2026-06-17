<?php

namespace App\Services\Trading;

use App\Models\Trading\TradingNotification;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create an in-app notification for a user.
     */
    public function notify(
        int $userId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        ?string $icon = null,
        array $data = []
    ): TradingNotification {
        return TradingNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'icon' => $icon,
            'data' => $data ?: null,
            'read_at' => null,
            'created_at' => now(),
        ]);
    }

    public function unreadCount(int $userId): int
    {
        return TradingNotification::where('user_id', $userId)->whereNull('read_at')->count();
    }

    public function recent(int $userId, int $limit = 10): Collection
    {
        return TradingNotification::where('user_id', $userId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function markAllRead(int $userId): int
    {
        return TradingNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markRead(int $userId, int $id): void
    {
        TradingNotification::where('user_id', $userId)->where('id', $id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
