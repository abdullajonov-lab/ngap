<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources;

use AbdullajonovLab\NutgramAdminPanel\Enums\AdminRole;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\AdminResource\Pages\CreateAdmin;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\AdminResource\Pages\EditAdmin;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\AdminResource\Pages\ListAdmins;
use AbdullajonovLab\NutgramAdminPanel\Models\Admin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Bot Management';

    public static function getModelLabel(): string
    {
        return __('nutgram-admin-panel::admin.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('nutgram-admin-panel::admin.plural_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('telegram_id')
                    ->required()
                    ->numeric()
                    ->unique(ignoreRecord: true)
                    ->label(__('nutgram-admin-panel::admin.fields.telegram_id')),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('nutgram-admin-panel::admin.fields.name')),

                Forms\Components\Select::make('role')
                    ->options(AdminRole::class)
                    ->default(AdminRole::Admin)
                    ->required()
                    ->label(__('nutgram-admin-panel::admin.fields.role')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('telegram_id')
                    ->sortable()
                    ->searchable()
                    ->label(__('nutgram-admin-panel::admin.fields.telegram_id')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label(__('nutgram-admin-panel::admin.fields.name')),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->label(__('nutgram-admin-panel::admin.fields.role')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action) {
                        if (Admin::count() <= 1) {
                            Notification::make()
                                ->danger()
                                ->title(__('nutgram-admin-panel::admin.messages.last_admin_warning'))
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }
}
