<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('nutgram-admin-panel.table_names.channels', 'nutgram_channels');
    }

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
