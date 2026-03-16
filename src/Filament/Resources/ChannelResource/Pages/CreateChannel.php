<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource\Pages;

use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;

class CreateChannel extends CreateRecord
{
    protected static string $resource = ChannelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $bot = app(Nutgram::class);
        $channelService = app(ChannelServiceInterface::class);

        try {
            $validated = $channelService->validateChannel($bot, $data['chat_id']);
        } catch (\InvalidArgumentException $e) {
            Notification::make()
                ->danger()
                ->title($e->getMessage())
                ->send();

            $this->halt();
        } catch (TelegramException) {
            Notification::make()
                ->danger()
                ->title(__('nutgram-admin-panel::channel.messages.validation_failed'))
                ->send();

            $this->halt();
        }

        return array_merge($data, $validated);
    }
}
