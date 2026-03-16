<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Widgets;

use AbdullajonovLab\NutgramAdminPanel\Contracts\StatisticsServiceInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ChannelMembershipChart extends ChartWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('nutgram-admin-panel::statistics.sections.channel_membership');
    }

    protected function getData(): array
    {
        /** @var StatisticsServiceInterface $service */
        $service = app(StatisticsServiceInterface::class);

        $data = $service->getChannelMembershipStats();

        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    ['label' => 'Members', 'data' => [], 'backgroundColor' => '#6366f1'],
                ],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Members',
                    'data' => $data->pluck('member_count')->toArray(),
                    'backgroundColor' => '#6366f1',
                ],
            ],
            'labels' => $data->map(fn ($channel) => $channel->username
                ? '@'.$channel->username
                : $channel->title
            )->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
        ];
    }
}
