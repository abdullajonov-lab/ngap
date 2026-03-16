<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Contracts;

interface BroadcastServiceInterface
{
    public function createAndDispatch(string $message): \AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;

    public function cancel(\AbdullajonovLab\NutgramAdminPanel\Models\Broadcast $broadcast): bool;
}
