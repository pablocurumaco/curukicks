<?php

namespace App\Filament\Resources\SneakerResource\Pages;

use App\Filament\Resources\SneakerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSneaker extends EditRecord
{
    protected static string $resource = SneakerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
