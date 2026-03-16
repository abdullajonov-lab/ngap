<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources;

use AbdullajonovLab\NutgramAdminPanel\Contracts\ChannelServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource\Pages\CreateChannel;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource\Pages\EditChannel;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource\Pages\ListChannels;
use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SergiX44\Nutgram\Nutgram;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Bot Management';

    public static function getModelLabel(): string
    {
        return __('nutgram-admin-panel::channel.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('nutgram-admin-panel::channel.plural_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('chat_id')
                    ->required()
                    ->visibleOn('create')
                    ->label(__('nutgram-admin-panel::channel.fields.chat_id')),

                Forms\Components\TextInput::make('title')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit')
                    ->label(__('nutgram-admin-panel::channel.fields.title')),

                Forms\Components\TextInput::make('username')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit')
                    ->label(__('nutgram-admin-panel::channel.fields.username')),

                Forms\Components\Toggle::make('is_mandatory')
                    ->default(false)
                    ->label(__('nutgram-admin-panel::channel.fields.is_mandatory')),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label(__('nutgram-admin-panel::channel.fields.is_active')),

                Forms\Components\TextInput::make('member_count')
                    ->disabled()
                    ->visibleOn('edit')
                    ->label(__('nutgram-admin-panel::channel.fields.member_count')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chat_id')
                    ->sortable()
                    ->searchable()
                    ->label(__('nutgram-admin-panel::channel.fields.chat_id')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label(__('nutgram-admin-panel::channel.fields.title')),

                Tables\Columns\TextColumn::make('username')
                    ->label(__('nutgram-admin-panel::channel.fields.username')),

                Tables\Columns\IconColumn::make('is_mandatory')
                    ->boolean()
                    ->label(__('nutgram-admin-panel::channel.fields.is_mandatory')),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('nutgram-admin-panel::channel.fields.is_active')),

                Tables\Columns\TextColumn::make('member_count')
                    ->sortable()
                    ->label(__('nutgram-admin-panel::channel.fields.member_count')),
            ])
            ->actions([
                Tables\Actions\Action::make('sync_member_count')
                    ->label(__('nutgram-admin-panel::channel.table_actions.sync_member_count'))
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Channel $record): void {
                        $bot = app(Nutgram::class);
                        $channelService = app(ChannelServiceInterface::class);
                        $count = $channelService->syncMemberCount($bot, $record);

                        Notification::make()
                            ->success()
                            ->title(__('nutgram-admin-panel::channel.messages.member_count_synced', ['count' => $count]))
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChannels::route('/'),
            'create' => CreateChannel::route('/create'),
            'edit' => EditChannel::route('/{record}/edit'),
        ];
    }
}
