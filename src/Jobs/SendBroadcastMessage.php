<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Jobs;

use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use AbdullajonovLab\NutgramAdminPanel\Jobs\Middleware\RateLimitedBroadcast;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class SendBroadcastMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    public int $maxExceptions = 1;

    public int $timeout = 30;

    public function __construct(
        public int $broadcastId,
        public int $telegramId,
    ) {
        $this->tries = (int) config('nutgram-admin-panel.broadcast.max_tries', 3);
        $this->onQueue(config('nutgram-admin-panel.broadcast.queue', 'default'));
    }

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [new RateLimitedBroadcast()];
    }

    public function handle(Nutgram $bot): void
    {
        $broadcast = Broadcast::find($this->broadcastId);

        if ($broadcast === null || $broadcast->status === BroadcastStatus::Cancelled) {
            return;
        }

        try {
            $bot->sendMessage(
                text: $broadcast->message,
                chat_id: $this->telegramId,
                parse_mode: ParseMode::HTML,
            );

            $broadcast->increment('sent_count');
        } catch (TelegramException $e) {
            $this->handleTelegramError($e, $broadcast);

            return;
        }

        $this->checkCompletion($broadcast);
    }

    private function handleTelegramError(TelegramException $e, Broadcast $broadcast): void
    {
        $code = $e->getCode();

        if ($code === 403 || $code === 400) {
            $broadcast->increment('blocked_count');

            BotUser::where('telegram_id', $this->telegramId)
                ->update(['status' => UserStatus::Blocked]);

            $this->checkCompletion($broadcast);

            $this->fail($e);

            return;
        }

        if ($code === 429) {
            $retryAfter = (int) ($e->getParameter('retry_after') ?? 5);
            $this->release($retryAfter);

            return;
        }

        // Other transient errors
        $this->release(5);
    }

    private function checkCompletion(Broadcast $broadcast): void
    {
        $broadcast->refresh();

        $processed = $broadcast->sent_count + $broadcast->failed_count + $broadcast->blocked_count;

        if ($processed >= $broadcast->total_users && $broadcast->status === BroadcastStatus::Sending) {
            $broadcast->update([
                'status' => BroadcastStatus::Completed,
                'completed_at' => now(),
            ]);
        }
    }
}
