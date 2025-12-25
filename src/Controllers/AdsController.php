<?php

namespace Ngap\Controllers;

use Ngap\Models\Ads;
use Ngap\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;

class AdsController
{
    protected Nutgram $bot;
    protected int $batchSize;
    protected int $batchDelay;

    public function __construct(Nutgram $bot)
    {
        $this->bot = $bot;
        $this->batchSize = config('ngap.ads.batch_size', 25);
        $this->batchDelay = config('ngap.ads.batch_delay', 1000);
    }

    public static function create(
        int $adminTelegramId,
        int $messageId,
        int $fromChatId
    ): Ads {
        return Ads::create([
            'admin_telegram_id' => $adminTelegramId,
            'message_id' => $messageId,
            'from_chat_id' => $fromChatId,
            'status' => Ads::STATUS_PENDING,
        ]);
    }

    public function send(Ads $ad): array
    {
        $users = User::active()->get();
        $totalUsers = $users->count();

        if ($totalUsers === 0) {
            $ad->markAsCancelled();
            return [
                'success' => false,
                'message' => 'No active users to send ads to',
                'sent' => 0,
                'failed' => 0,
            ];
        }

        $ad->markAsSending($totalUsers);

        $sent = 0;
        $failed = 0;
        $processed = 0;

        foreach ($users as $user) {
            try {
                $this->bot->copyMessage(
                    chat_id: $user->telegram_id,
                    from_chat_id: $ad->from_chat_id,
                    message_id: $ad->message_id
                );
                $ad->incrementSent();
                $sent++;
            } catch (TelegramException $e) {
                $ad->incrementFailed();
                $failed++;

                // If user blocked the bot, mark them as blocked
                if ($this->isBlockedByUser($e)) {
                    $user->block();
                }
            } catch (\Exception $e) {
                $ad->incrementFailed();
                $failed++;
            }

            $processed++;

            // Rate limiting
            if ($processed % $this->batchSize === 0) {
                usleep($this->batchDelay * 1000);
            }
        }

        $ad->markAsCompleted();

        return [
            'success' => true,
            'message' => 'Ads sent successfully',
            'total' => $totalUsers,
            'sent' => $sent,
            'failed' => $failed,
            'success_rate' => $ad->getSuccessRate(),
        ];
    }

    public function sendAsync(Ads $ad, callable $progressCallback = null): void
    {
        $users = User::active()->cursor();
        $totalUsers = User::active()->count();

        if ($totalUsers === 0) {
            $ad->markAsCancelled();
            if ($progressCallback) {
                $progressCallback(0, 0, 0, true, 'No active users');
            }
            return;
        }

        $ad->markAsSending($totalUsers);

        $processed = 0;

        foreach ($users as $user) {
            try {
                $this->bot->copyMessage(
                    chat_id: $user->telegram_id,
                    from_chat_id: $ad->from_chat_id,
                    message_id: $ad->message_id
                );
                $ad->incrementSent();
            } catch (TelegramException $e) {
                $ad->incrementFailed();
                if ($this->isBlockedByUser($e)) {
                    $user->block();
                }
            } catch (\Exception $e) {
                $ad->incrementFailed();
            }

            $processed++;

            // Progress callback
            if ($progressCallback && $processed % 100 === 0) {
                $progressCallback(
                    $processed,
                    $ad->sent_count,
                    $ad->failed_count,
                    false
                );
            }

            // Rate limiting
            if ($processed % $this->batchSize === 0) {
                usleep($this->batchDelay * 1000);
            }
        }

        $ad->markAsCompleted();

        if ($progressCallback) {
            $progressCallback(
                $processed,
                $ad->sent_count,
                $ad->failed_count,
                true,
                'Completed'
            );
        }
    }

    protected function isBlockedByUser(TelegramException $e): bool
    {
        $blockedErrors = [
            'bot was blocked by the user',
            'user is deactivated',
            'chat not found',
            'bot was kicked from the group',
            'PEER_ID_INVALID',
        ];

        $message = strtolower($e->getMessage());

        foreach ($blockedErrors as $error) {
            if (str_contains($message, strtolower($error))) {
                return true;
            }
        }

        return false;
    }

    public static function getRecentAds(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Ads::orderBy('created_at', 'desc')->limit($limit)->get();
    }

    public static function getAdsByAdmin(int $adminTelegramId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Ads::byAdmin($adminTelegramId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
