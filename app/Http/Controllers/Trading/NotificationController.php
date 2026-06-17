<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Services\Trading\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    /** GET /trade/notifications — recent + unread count (polled). */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        return response()->json([
            'unread' => $this->notifications->unreadCount($userId),
            'items' => $this->notifications->recent($userId, 12)->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'icon' => $n->icon,
                'action_url' => $n->action_url,
                'read_at' => $n->read_at?->toISOString(),
                'created_at' => $n->created_at?->toISOString(),
            ]),
        ]);
    }

    /** POST /trade/notifications/read — mark all read. */
    public function readAll(): JsonResponse
    {
        $count = $this->notifications->markAllRead(Auth::id());

        return response()->json(['marked' => $count]);
    }
}
