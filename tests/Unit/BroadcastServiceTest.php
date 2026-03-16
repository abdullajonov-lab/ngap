<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Jobs\DispatchBroadcast;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use AbdullajonovLab\NutgramAdminPanel\Services\BroadcastService;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class BroadcastServiceTest extends TestCase
{
    use RefreshDatabase;

    private BroadcastService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BroadcastService();
    }

    public function test_create_and_dispatch_creates_broadcast_record(): void
    {
        Queue::fake();

        $broadcast = $this->service->createAndDispatch('Hello <b>World</b>');

        $this->assertInstanceOf(Broadcast::class, $broadcast);
        $this->assertTrue($broadcast->exists);
        $this->assertEquals('Hello <b>World</b>', $broadcast->message);
        $this->assertEquals(BroadcastStatus::Pending, $broadcast->status);
    }

    public function test_create_and_dispatch_dispatches_job(): void
    {
        Queue::fake();

        $broadcast = $this->service->createAndDispatch('Test message');

        Queue::assertPushed(DispatchBroadcast::class, function (DispatchBroadcast $job) use ($broadcast) {
            return $job->broadcastId === $broadcast->id;
        });
    }

    public function test_cancel_sending_broadcast(): void
    {
        $broadcast = Broadcast::create([
            'message' => 'Test',
            'status' => BroadcastStatus::Sending,
            'total_users' => 100,
            'sent_count' => 10,
            'failed_count' => 0,
            'blocked_count' => 0,
        ]);

        $result = $this->service->cancel($broadcast);

        $this->assertTrue($result);
        $broadcast->refresh();
        $this->assertEquals(BroadcastStatus::Cancelled, $broadcast->status);
        $this->assertNotNull($broadcast->completed_at);
    }

    public function test_cancel_non_sending_broadcast_returns_false(): void
    {
        $broadcast = Broadcast::create([
            'message' => 'Test',
            'status' => BroadcastStatus::Pending,
            'total_users' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'blocked_count' => 0,
        ]);

        $result = $this->service->cancel($broadcast);

        $this->assertFalse($result);
        $broadcast->refresh();
        $this->assertEquals(BroadcastStatus::Pending, $broadcast->status);
        $this->assertNull($broadcast->completed_at);
    }
}
