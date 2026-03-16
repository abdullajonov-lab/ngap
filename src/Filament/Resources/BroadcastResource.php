<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources;

use AbdullajonovLab\NutgramAdminPanel\Contracts\BroadcastServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BroadcastResource\Pages\CreateBroadcast;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BroadcastResource\Pages\ListBroadcasts;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Bot Management';

    public static function getModelLabel(): string
    {
        return __('nutgram-admin-panel::broadcast.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('nutgram-admin-panel::broadcast.plural_label');
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(6)
                    ->label(__('nutgram-admin-panel::broadcast.fields.message'))
                    ->helperText(__('nutgram-admin-panel::broadcast.messages.html_supported')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->label(__('nutgram-admin-panel::broadcast.fields.message')),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (BroadcastStatus $state): string => match ($state) {
                        BroadcastStatus::Pending => 'gray',
                        BroadcastStatus::Sending => 'warning',
                        BroadcastStatus::Completed => 'success',
                        BroadcastStatus::Cancelled => 'danger',
                        BroadcastStatus::Failed => 'danger',
                    })
                    ->label(__('nutgram-admin-panel::broadcast.fields.status')),

                Tables\Columns\TextColumn::make('total_users')
                    ->label(__('nutgram-admin-panel::broadcast.fields.total_users')),

                Tables\Columns\TextColumn::make('sent_count')
                    ->label(__('nutgram-admin-panel::broadcast.fields.sent_count')),

                Tables\Columns\TextColumn::make('failed_count')
                    ->label(__('nutgram-admin-panel::broadcast.fields.failed_count')),

                Tables\Columns\TextColumn::make('blocked_count')
                    ->label(__('nutgram-admin-panel::broadcast.fields.blocked_count')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('nutgram-admin-panel::broadcast.fields.created_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('cancel')
                    ->label(__('nutgram-admin-panel::broadcast.actions.cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Broadcast $record): bool => $record->status === BroadcastStatus::Sending)
                    ->action(function (Broadcast $record): void {
                        app(BroadcastServiceInterface::class)->cancel($record);

                        Notification::make()
                            ->success()
                            ->title(__('nutgram-admin-panel::broadcast.messages.cancelled'))
                            ->send();
                    }),
            ])
            ->poll('5s')
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBroadcasts::route('/'),
            'create' => CreateBroadcast::route('/create'),
        ];
    }
}
