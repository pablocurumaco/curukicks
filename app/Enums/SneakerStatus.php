<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SneakerStatus: string implements HasLabel, HasColor
{
    case Available = 'available';
    case Sold = 'sold';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Sold => 'Vendido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Sold => 'danger',
        };
    }
}
