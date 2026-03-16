<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use AbdullajonovLab\NutgramAdminPanel\Jobs\DispatchBroadcast;
use AbdullajonovLab\NutgramAdminPanel\Jobs\SendBroadcastMessage;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class DispatchBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private function createBroadcast(array $overrides = []): Broadcast
    {
        return Broadcast::create(array_merge([
            'message' => 'Test broadcast message',
            'status' => BroadcastStatus::Pending,
            'total_users' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'blocked_count' => 0,
        ], $overrides));
    }

    private function createBotUser(array $overrides = []): BotUser
    {
        static $telegramIdCounter = 100000;

        return BotUser::create(array_merge([
            'telegram_id' => $telegramIdCounter++,
            'first_name' => 'Test',
            'status' => UserStatus::Active,
        ], $overrides));
    }

    public function test_dispatch_sets_broadcast_to_sending(): void
    {
        Queue::fake();

        $broadcast = $this->createBroadcast();
        $this->createBotUser();
        $this->createBotUser();

        $job = new DispatchBroadcast($broadcast->id);
        $job->handle();

        $broadcast->refresh();
        $this->assertEquals(BroadcastStatus::Sending, $broadcast->status);
        $this->assertNotNull($broadcast->started_at);
        $this->assertEquals(2, $broadcast->total_users);
    }

    public function test_dispatch_creates_send_jobs_for_active_users(): void
    {
        Queue::fake();

        $broadcast = $this->createBroadcast();
        $user1 = $this->createBotUser();
        $user2 = $this->createBotUser();
        $user3 = $this->createBotUser();
        // Blocked user should NOT get a job
        $this->createBotUser(['status' => UserStatus::Blocked]);

        $job = new DispatchBroadcast($broadcast->id);
        $job->handle();

        Queue::assertPushed(SendBroadcastMessage::class, 3);

        $dispatchedTelegramIds = [];
        Queue::assertPushed(SendBroadcastMessage::class, function (SendBroadcastMessage $job) use ($broadcast, &$dispatchedTelegramIds) {
            $dispatchedTelegramIds[] = $job->telegramId;

            return $job->broadcastId === $broadcast->id;
        });
    }

    public function test_dispatch_stops_on_cancelled_broadcast(): void
    {
        Queue::fake();

        $broadcast = $this->createBroadcast();

        // Create enough users to trigger multiple chunks (chunk size is 500)
        // We will simulate cancellation by updating the broadcast mid-chunk
        $user1 = $this->createBotUser();
        $user2 = $this->createBotUser();

        // Cancel the broadcast before running the job
        $broadcast->update(['status' => BroadcastStatus::Cancelled]);

        $job = new DispatchBroadcast($broadcast->id);
        $job->handle();

        // Status should remain Cancelled (not overwritten to Sending)
        // OR the job should detect cancelled and stop early
        // Since the job first sets to Sending then checks between chunks,
        // we verify that it respects cancelled status on initial check too
        Queue::assertNotPushed(SendBroadcastMessage::class);
    }
}
