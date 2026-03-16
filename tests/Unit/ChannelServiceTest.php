<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use AbdullajonovLab\NutgramAdminPanel\Services\ChannelService;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use SergiX44\Nutgram\Telegram\Properties\ChatMemberStatus;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMember;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberAdministrator;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberBanned;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberLeft;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberMember;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberOwner;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberRestricted;
use SergiX44\Nutgram\Telegram\Types\User\User;

class ChannelServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChannelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChannelService();
    }

    private function createMockBot(): Nutgram&MockObject
    {
        return $this->createMock(Nutgram::class);
    }

    private function makeChat(int $id = -1001234567890, ?string $title = 'Test Channel', ?string $username = 'testchannel'): Chat
    {
        $chat = new Chat();
        $chat->id = $id;
        $chat->title = $title;
        $chat->username = $username;

        return $chat;
    }

    private function makeBotUser(int $id = 999): User
    {
        $user = new User();
        $user->id = $id;
        $user->first_name = 'Bot';
        $user->is_bot = true;

        return $user;
    }

    private function makeChatMemberAdmin(int $userId = 999): ChatMemberAdministrator
    {
        $member = new ChatMemberAdministrator();
        $member->status = ChatMemberStatus::ADMINISTRATOR;
        $member->user = $this->makeBotUser($userId);
        $member->can_be_edited = false;
        $member->is_anonymous = false;
        $member->can_manage_chat = true;
        $member->can_delete_messages = false;
        $member->can_manage_video_chats = false;
        $member->can_restrict_members = false;
        $member->can_promote_members = false;
        $member->can_change_info = false;
        $member->can_invite_users = false;

        return $member;
    }

    private function makeChatMemberOwner(int $userId = 999): ChatMemberOwner
    {
        $member = new ChatMemberOwner();
        $member->status = ChatMemberStatus::CREATOR;
        $member->user = $this->makeBotUser($userId);
        $member->is_anonymous = false;

        return $member;
    }

    private function makeChatMemberMember(int $userId = 123456): ChatMemberMember
    {
        $user = new User();
        $user->id = $userId;
        $user->first_name = 'Test';
        $user->is_bot = false;

        $member = new ChatMemberMember();
        $member->status = ChatMemberStatus::MEMBER;
        $member->user = $user;

        return $member;
    }

    private function makeChatMemberRestricted(int $userId = 123456, bool $isMember = true): ChatMemberRestricted
    {
        $user = new User();
        $user->id = $userId;
        $user->first_name = 'Test';
        $user->is_bot = false;

        $member = new ChatMemberRestricted();
        $member->status = ChatMemberStatus::RESTRICTED;
        $member->user = $user;
        $member->is_member = $isMember;
        $member->can_send_messages = true;
        $member->can_send_audios = true;
        $member->can_send_documents = true;
        $member->can_send_photos = true;
        $member->can_send_videos = true;
        $member->can_send_video_notes = true;
        $member->can_send_voice_notes = true;
        $member->can_send_polls = true;
        $member->can_send_other_messages = true;
        $member->can_add_web_page_previews = true;
        $member->can_edit_tag = false;
        $member->can_change_info = false;
        $member->can_invite_users = false;
        $member->can_pin_messages = false;
        $member->can_manage_topics = false;
        $member->until_date = 0;

        return $member;
    }

    private function makeChatMemberLeft(int $userId = 123456): ChatMemberLeft
    {
        $user = new User();
        $user->id = $userId;
        $user->first_name = 'Test';
        $user->is_bot = false;

        $member = new ChatMemberLeft();
        $member->status = ChatMemberStatus::LEFT;
        $member->user = $user;

        return $member;
    }

    private function makeChatMemberBanned(int $userId = 123456): ChatMemberBanned
    {
        $user = new User();
        $user->id = $userId;
        $user->first_name = 'Test';
        $user->is_bot = false;

        $member = new ChatMemberBanned();
        $member->status = ChatMemberStatus::KICKED;
        $member->user = $user;
        $member->until_date = 0;

        return $member;
    }

    private function createChannel(array $overrides = []): Channel
    {
        return Channel::create(array_merge([
            'chat_id' => -1001234567890,
            'title' => 'Test Channel',
            'username' => 'testchannel',
            'is_mandatory' => true,
            'is_active' => true,
            'member_count' => 100,
        ], $overrides));
    }

    // --- validateChannel tests ---

    public function test_validate_channel_success(): void
    {
        $bot = $this->createMockBot();
        $chat = $this->makeChat();
        $botUser = $this->makeBotUser();
        $adminMember = $this->makeChatMemberAdmin();

        $bot->method('getChat')->willReturn($chat);
        $bot->method('getMe')->willReturn($botUser);
        $bot->method('getChatMember')->willReturn($adminMember);
        $bot->method('getChatMemberCount')->willReturn(1500);

        $result = $this->service->validateChannel($bot, '@testchannel');

        $this->assertIsArray($result);
        $this->assertEquals(-1001234567890, $result['chat_id']);
        $this->assertEquals('Test Channel', $result['title']);
        $this->assertEquals('testchannel', $result['username']);
        $this->assertEquals(1500, $result['member_count']);
    }

    public function test_validate_channel_success_with_owner_status(): void
    {
        $bot = $this->createMockBot();
        $chat = $this->makeChat();
        $botUser = $this->makeBotUser();
        $ownerMember = $this->makeChatMemberOwner();

        $bot->method('getChat')->willReturn($chat);
        $bot->method('getMe')->willReturn($botUser);
        $bot->method('getChatMember')->willReturn($ownerMember);
        $bot->method('getChatMemberCount')->willReturn(500);

        $result = $this->service->validateChannel($bot, '@testchannel');

        $this->assertIsArray($result);
        $this->assertEquals(-1001234567890, $result['chat_id']);
    }

    public function test_validate_channel_not_found(): void
    {
        $bot = $this->createMockBot();
        $bot->method('getChat')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);

        $this->service->validateChannel($bot, '@nonexistent');
    }

    public function test_validate_channel_bot_not_admin(): void
    {
        $bot = $this->createMockBot();
        $chat = $this->makeChat();
        $botUser = $this->makeBotUser();
        $regularMember = $this->makeChatMemberMember(999);

        $bot->method('getChat')->willReturn($chat);
        $bot->method('getMe')->willReturn($botUser);
        $bot->method('getChatMember')->willReturn($regularMember);

        $this->expectException(InvalidArgumentException::class);

        $this->service->validateChannel($bot, '@testchannel');
    }

    // --- isUserMember tests ---

    public function test_is_user_member_with_member_status(): void
    {
        $bot = $this->createMockBot();
        $member = $this->makeChatMemberMember();

        $bot->method('getChatMember')->willReturn($member);

        $result = $this->service->isUserMember($bot, -1001234567890, 123456);

        $this->assertTrue($result);
    }

    public function test_is_user_member_with_admin_status(): void
    {
        $bot = $this->createMockBot();
        $member = $this->makeChatMemberAdmin(123456);

        $bot->method('getChatMember')->willReturn($member);

        $result = $this->service->isUserMember($bot, -1001234567890, 123456);

        $this->assertTrue($result);
    }

    public function test_is_user_member_with_creator_status(): void
    {
        $bot = $this->createMockBot();
        $member = $this->makeChatMemberOwner(123456);

        $bot->method('getChatMember')->willReturn($member);

        $result = $this->service->isUserMember($bot, -1001234567890, 123456);

        $this->assertTrue($result);
    }

    public function test_is_user_member_with_restricted_is_member_true(): void
    {
        $bot = $this->createMockBot();
        $member = $this->makeChatMemberRestricted(isMember: true);

        $bot->method('getChatMember')->willReturn($member);

        $result = $this->service->isUserMember($bot, -1001234567890, 123456);

        $this->assertTrue($result);
    }

    public function test_is_user_member_with_restricted_is_member_false(): void
    {
        $bot = $this->createMockBot();
        $member = $this->makeChatMemberRestricted(isMember: false);

        $bot->method('getChatMember')->willReturn($member);

        $result = $this->service->isUserMember($bot, -1001234567890, 123456);

        $this->assertFalse($result);
    }

    public function test_is_user_member_with_left_status(): void
    {
        $bot = $this->createMockBot();
        $member = $this->makeChatMemberLeft();

        $bot->method('getChatMember')->willReturn($member);

        $result = $this->service->isUserMember($bot, -1001234567890, 123456);

        $this->assertFalse($result);
    }

    public function test_is_user_member_with_kicked_status(): void
    {
        $bot = $this->createMockBot();
        $member = $this->makeChatMemberBanned();

        $bot->method('getChatMember')->willReturn($member);

        $result = $this->service->isUserMember($bot, -1001234567890, 123456);

        $this->assertFalse($result);
    }

    public function test_is_user_member_returns_false_for_null(): void
    {
        $bot = $this->createMockBot();
        $bot->method('getChatMember')->willReturn(null);

        $result = $this->service->isUserMember($bot, -1001234567890, 123456);

        $this->assertFalse($result);
    }

    // --- getMissingChannels tests ---

    public function test_get_missing_channels_caches_result(): void
    {
        $this->createChannel();

        $bot = $this->createMockBot();
        $leftMember = $this->makeChatMemberLeft();

        $bot->expects($this->once())
            ->method('getChatMember')
            ->willReturn($leftMember);

        // First call -- should hit API
        $missing1 = $this->service->getMissingChannels($bot, 123456);
        $this->assertCount(1, $missing1);

        // Second call -- should use cache
        $missing2 = $this->service->getMissingChannels($bot, 123456);
        $this->assertCount(1, $missing2);
    }

    public function test_get_missing_channels_graceful_on_telegram_error(): void
    {
        $this->createChannel();

        $bot = $this->createMockBot();
        $bot->method('getChatMember')
            ->willThrowException(new TelegramException('Chat not found'));

        // Should not throw -- graceful degradation means user is allowed through
        $missing = $this->service->getMissingChannels($bot, 123456);

        // Channel should be skipped (user allowed through), so missing should be empty
        $this->assertCount(0, $missing);
    }

    public function test_get_missing_channels_returns_empty_when_user_is_member(): void
    {
        $this->createChannel();

        $bot = $this->createMockBot();
        $memberStatus = $this->makeChatMemberMember();
        $bot->method('getChatMember')->willReturn($memberStatus);

        $missing = $this->service->getMissingChannels($bot, 123456);

        $this->assertCount(0, $missing);
    }

    public function test_get_missing_channels_only_checks_mandatory_active(): void
    {
        // Mandatory + active -- should be checked
        $this->createChannel(['chat_id' => -1001, 'is_mandatory' => true, 'is_active' => true]);
        // Not mandatory -- should be skipped
        $this->createChannel(['chat_id' => -1002, 'is_mandatory' => false, 'is_active' => true]);
        // Not active -- should be skipped
        $this->createChannel(['chat_id' => -1003, 'is_mandatory' => true, 'is_active' => false]);

        $bot = $this->createMockBot();
        $leftMember = $this->makeChatMemberLeft();

        // Only one call should be made (for the mandatory+active channel)
        $bot->expects($this->once())
            ->method('getChatMember')
            ->willReturn($leftMember);

        $missing = $this->service->getMissingChannels($bot, 123456);

        $this->assertCount(1, $missing);
        $this->assertEquals(-1001, $missing[0]->chat_id);
    }

    // --- getMissingChannelsFresh tests ---

    public function test_get_missing_channels_fresh_bypasses_cache(): void
    {
        $this->createChannel();

        $bot = $this->createMockBot();
        $leftMember = $this->makeChatMemberLeft();

        // Pre-fill cache with "is member" result
        $cacheKey = 'nutgram:channel_member:-1001234567890:123456';
        Cache::put($cacheKey, true, 300);

        // getChatMember should be called because fresh bypasses cache
        $bot->expects($this->once())
            ->method('getChatMember')
            ->willReturn($leftMember);

        $missing = $this->service->getMissingChannelsFresh($bot, 123456);

        $this->assertCount(1, $missing);
    }

    // --- syncMemberCount tests ---

    public function test_sync_member_count_updates_channel(): void
    {
        $channel = $this->createChannel(['member_count' => 100]);

        $bot = $this->createMockBot();
        $bot->method('getChatMemberCount')->willReturn(2500);

        $count = $this->service->syncMemberCount($bot, $channel);

        $this->assertEquals(2500, $count);
        $this->assertEquals(2500, $channel->fresh()->member_count);
    }
}
