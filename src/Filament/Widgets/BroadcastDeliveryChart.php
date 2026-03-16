<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Widgets;

use AbdullajonovLab\NutgramAdminPanel\Contracts\StatisticsServiceInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class BroadcastDeliveryChart extends ChartWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('nutgram-admin-panel::statistics.sections.broadcast_delivery');
    }

    protected function getData(): array
    {
        /** @var StatisticsServiceInterface $service */
        $service = app(StatisticsServiceInterface::class);

        $data = $service->getBroadcastDeliveryStats(10);

        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    ['label' => 'Sent', 'data' => [], 'backgroundColor' => '#10b981'],
                    ['label' => 'Failed', 'data' => [], 'backgroundColor' => '#ef4444'],
                    ['label' => 'Blocked', 'data' => [], 'backgroundColor' => '#f97316'],
                ],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sent',
                    'data' => $data->pluck('sent_count')->toArray(),
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Failed',
                    'data' => $data->pluck('failed_count')->toArray(),
                    'backgroundColor' => '#ef4444',
                ],
                [
                    'label' => 'Blocked',
                    'data' => $data->pluck('blocked_count')->toArray(),
                    'backgroundColor' => '#f97316',
                ],
            ],
            'labels' => $data->map(fn ($item) => $item->created_at->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true],
            ],
        ];
    }
}
