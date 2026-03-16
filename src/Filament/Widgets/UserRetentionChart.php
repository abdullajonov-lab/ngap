<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Widgets;

use AbdullajonovLab\NutgramAdminPanel\Contracts\StatisticsServiceInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class UserRetentionChart extends ChartWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'month';

    public function getHeading(): string|Htmlable|null
    {
        return __('nutgram-admin-panel::statistics.sections.user_retention');
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => __('nutgram-admin-panel::statistics.filters.week'),
            'month' => __('nutgram-admin-panel::statistics.filters.month'),
            'year' => __('nutgram-admin-panel::statistics.filters.year'),
        ];
    }

    protected function getData(): array
    {
        /** @var StatisticsServiceInterface $service */
        $service = app(StatisticsServiceInterface::class);

        $data = $service->getUserRetention($this->filter);

        return [
            'datasets' => [
                [
                    'label' => __('nutgram-admin-panel::statistics.fields.returning_users'),
                    'data' => $data->pluck('aggregate')->toArray(),
                    'fill' => true,
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
