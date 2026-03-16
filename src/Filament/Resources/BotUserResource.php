<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources;

use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BotUserResource\Pages\ListBotUsers;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BotUserResource extends Resource
{
    protected static ?string $model = BotUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Bot Management';

    public static function getModelLabel(): string
    {
        return __('nutgram-admin-panel::user.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('nutgram-admin-panel::user.plural_label');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('telegram_id')
                    ->sortable()
                    ->searchable()
                    ->label(__('nutgram-admin-panel::user.fields.telegram_id')),

                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->label(__('nutgram-admin-panel::user.fields.first_name')),

                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->label(__('nutgram-admin-panel::user.fields.last_name')),

                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->label(__('nutgram-admin-panel::user.fields.username')),

                Tables\Columns\TextColumn::make('language_code')
                    ->label(__('nutgram-admin-panel::user.fields.language_code')),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (UserStatus $state): string => match ($state) {
                        UserStatus::Active => 'success',
                        UserStatus::Blocked => 'danger',
                        UserStatus::Deactivated => 'warning',
                    })
                    ->label(__('nutgram-admin-panel::user.fields.status')),

                Tables\Columns\TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('nutgram-admin-panel::user.fields.last_activity_at')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('nutgram-admin-panel::user.fields.created_at')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(UserStatus::class)
                    ->label(__('nutgram-admin-panel::user.filters.status')),
            ])
            ->defaultSort('last_activity_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBotUsers::route('/'),
        ];
    }
}
