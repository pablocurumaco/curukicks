<?php

namespace App\Filament\Widgets;

use App\Enums\SneakerDecision;
use App\Enums\SneakerStatus;
use App\Models\Sneaker;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SneakerStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $sneakers = Sneaker::where('status', SneakerStatus::Available)->get();

        $total = $sneakers->count();

        $forSale = $sneakers->filter(fn ($s) => $s->decision->isForSale())->count();

        $totalCost = $sneakers->sum('cost_paid');

        $totalSaleValue = $sneakers->whereNotNull('sale_price_gt')->sum('sale_price_gt');

        $totalProfit = $sneakers
            ->filter(fn ($s) => $s->profit !== null)
            ->sum(fn ($s) => $s->profit);

        return [
            Stat::make('Total Pares', $total)
                ->icon('heroicon-o-cube'),

            Stat::make('En Venta', $forSale)
                ->icon('heroicon-o-tag')
                ->color('success'),

            Stat::make('Costo Invertido', 'Q' . number_format($totalCost))
                ->icon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Valor Venta', 'Q' . number_format($totalSaleValue))
                ->icon('heroicon-o-currency-dollar')
                ->color('info'),

            Stat::make('Ganancia Potencial', 'Q' . number_format($totalProfit))
                ->icon('heroicon-o-arrow-trending-up')
                ->color($totalProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}
