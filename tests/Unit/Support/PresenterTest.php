<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Unit\Support;

use GeekCo\FilamentMaxUsers\Support\ChatPresenter;
use GeekCo\FilamentMaxUsers\Support\UserPresenter;
use GeekCo\FilamentMaxUsers\Tests\TestCase;
use GeekCo\LaravelMaxClient\Enums\MaxChatStatus;
use GeekCo\LaravelMaxClient\Models\MaxChat;
use GeekCo\LaravelMaxClient\Models\MaxChatUser;
use GeekCo\LaravelMaxClient\Models\MaxUser;
use GeekCo\MaxPhpClient\Enum\ChatType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;

class PresenterTest extends TestCase
{
    use RefreshDatabase;

    public function testChatTitleIsUsedForGroupsAndChannels(): void
    {
        $chat = $this->makeChat(10, ChatType::Chat, 'Проектный чат');

        $this->assertSame('Проектный чат', ChatPresenter::displayName($chat));
        $this->assertTrue(ChatPresenter::hasMetadata($chat));
    }

    public function testDialogFallsBackToLinkedUserName(): void
    {
        $chat = $this->makeChat(11, ChatType::Dialog);

        MaxUser::create([
            'user_id' => 20,
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'is_bot' => false,
        ]);

        MaxChatUser::create(['chat_id' => 11, 'user_id' => 20]);

        $this->assertSame('Иван Петров', ChatPresenter::displayName($chat));
    }

    public function testGroupWithoutTitleFallsBackToLinkedUserName(): void
    {
        $chat = $this->makeChat(13, ChatType::Chat);

        MaxUser::create([
            'user_id' => 21,
            'first_name' => 'Автор',
            'is_bot' => false,
        ]);

        MaxChatUser::create(['chat_id' => 13, 'user_id' => 21]);

        $this->assertSame('Автор', ChatPresenter::displayName($chat));
        $this->assertFalse(ChatPresenter::hasMetadata($chat));
    }

    public function testUnknownChatFallsBackToIdentifier(): void
    {
        $chat = $this->makeChat(12, null);

        $this->assertSame('chat 12', ChatPresenter::displayName($chat));
        $this->assertFalse(ChatPresenter::hasMetadata($chat));
    }

    public function testOverriddenChatModelUsesTitleAttribute(): void
    {
        $chat = new MaxUser(['user_id' => 31]);
        $chat->setAttribute('title', 'Рекламный канал');

        $this->assertSame('Рекламный канал', ChatPresenter::displayName($chat));
        $this->assertTrue(ChatPresenter::hasMetadata($chat));
    }

    public function testOverriddenChatModelWithoutTitleUsesIdentifier(): void
    {
        $chat = new MaxUser(['user_id' => 32]);

        $this->assertSame('chat 32', ChatPresenter::displayName($chat));
        $this->assertFalse(ChatPresenter::hasMetadata($chat));
    }

    public function testRecordWithoutIdentifierAndTitleFallsBackToGenericName(): void
    {
        $chat = new MaxUser();

        $this->assertSame('chat', ChatPresenter::displayName($chat));
        $this->assertFalse(ChatPresenter::hasMetadata($chat));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('phoneStates')]
    public function testPhoneIconReflectsVerification(?string $expectedIcon, array $attributes): void
    {
        $this->assertSame($expectedIcon, UserPresenter::phoneIcon(new MaxUser($attributes)));
    }

    /**
     * @return iterable<string, array{?string, array<string, mixed>}>
     */
    public static function phoneStates(): iterable
    {
        yield 'нет телефона' => [null, ['user_id' => 1]];

        yield 'пустой телефон' => [null, ['user_id' => 1, 'phone' => '']];

        yield 'телефон без подтверждения' => ['heroicon-m-question-mark-circle', [
            'user_id' => 1,
            'phone' => '+79991234567',
        ]];

        yield 'подтверждённый телефон' => ['heroicon-m-check-badge', [
            'user_id' => 1,
            'phone' => '+79991234567',
            'phone_verified_at' => '2026-01-01 00:00:00',
        ]];
    }

    private function makeChat(int $chatId, ?ChatType $chatType, ?string $title = null): MaxChat
    {
        return MaxChat::create([
            'chat_id' => $chatId,
            'status' => MaxChatStatus::Active,
            'chat_type' => $chatType,
            'title' => $title,
        ]);
    }
}
