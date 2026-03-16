<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BotUserResource\Pages;

use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BotUserResource;
use Filament\Resources\Pages\ListRecords;

class ListBotUsers extends ListRecords
{
    protected static string $resource = BotUserResource::class;
}
