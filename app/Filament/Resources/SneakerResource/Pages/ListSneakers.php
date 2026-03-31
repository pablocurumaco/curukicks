<?php

namespace App\Filament\Resources\SneakerResource\Pages;

use App\Filament\Resources\SneakerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
}
