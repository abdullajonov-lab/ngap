<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource\Pages;

use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChannel extends EditRecord
{
    protected static string $resource = ChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
