<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BroadcastResource\Pages;

use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BroadcastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBroadcasts extends ListRecords
{
    protected static string $resource = BroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
