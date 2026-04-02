<?php

namespace App\Filament\Resources\SneakerResource\Pages;

use App\Enums\SneakerDecision;
use App\Filament\Resources\SneakerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSneakers extends ListRecords
{
    protected static string $resource = SneakerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SneakerStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-o-squares-2x2'),

            'en_venta' => Tab::make('En Venta')
                ->icon('heroicon-o-tag')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('decision', [
                    SneakerDecision::VENTA,
                    SneakerDecision::POSIBLE_VENTA,
                    SneakerDecision::VENTA_CONDICIONAL,
                    SneakerDecision::VENTA_GANCHO,
                ])),

            'uso_personal' => Tab::make('Uso Personal')
                ->icon('heroicon-o-user')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('decision', SneakerDecision::USO_PERSONAL)),

            'no_venta' => Tab::make('No Venta')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('decision', [
                    SneakerDecision::NO_VENTA,
                    SneakerDecision::ULTIMO_RECURSO,
                ])),

            'pendientes' => Tab::make('Pendientes')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('decision', [
                    SneakerDecision::PENDIENTE,
                    SneakerDecision::POR_REVISAR,
                ])),
        ];
    }
}
