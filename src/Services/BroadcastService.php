<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Services;

use AbdullajonovLab\NutgramAdminPanel\Contracts\BroadcastServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Jobs\DispatchBroadcast;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;

class BroadcastService implements BroadcastServiceInterface
{
    public function createAndDispatch(string $message): Broadcast
    {
        $broadcast = Broadcast::create([
            'message' => $message,
            'status' => BroadcastStatus::Pending,
            'total_users' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'blocked_count' => 0,
        ]);

        DispatchBroadcast::dispatch($broadcast->id);

        return $broadcast;
    }

    public function cancel(Broadcast $broadcast): bool
    {
        if ($broadcast->status !== BroadcastStatus::Sending) {
            return false;
        }

        $broadcast->update([
            'status' => BroadcastStatus::Cancelled,
            'completed_at' => now(),
        ]);

        return true;
    }
}
