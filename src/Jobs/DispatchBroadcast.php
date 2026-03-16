<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Jobs;

use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchBroadcast implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $broadcastId,
    ) {
        $this->onQueue(config('nutgram-admin-panel.broadcast.queue', 'default'));
    }

    public function handle(): void
    {
        $broadcast = Broadcast::find($this->broadcastId);

        if ($broadcast === null) {
            return;
        }

        // Do not proceed if broadcast was already cancelled
        if ($broadcast->status === BroadcastStatus::Cancelled) {
            return;
        }

        $totalUsers = BotUser::active()->count();

        $broadcast->update([
            'status' => BroadcastStatus::Sending,
            'started_at' => now(),
            'total_users' => $totalUsers,
        ]);

        BotUser::active()
            ->select(['id', 'telegram_id'])
            ->chunkById(500, function ($users) use ($broadcast) {
                foreach ($users as $user) {
                    SendBroadcastMessage::dispatch($broadcast->id, $user->telegram_id);
                }

                // Check if cancelled between chunks
                $broadcast->refresh();

                if ($broadcast->status === BroadcastStatus::Cancelled) {
                    return false;
                }
            });
    }
}
