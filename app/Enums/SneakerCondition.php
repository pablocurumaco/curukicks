<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SneakerCondition: string implements HasLabel, HasColor
{
    case DS = 'DS';
    case Used = 'Used';

    public function getLabel(): string
    {
        return match ($this) {
            self::DS => 'DS (Deadstock)',
            self::Used => 'Used',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DS => 'success',
            self::Used => 'warning',
        };
    }
}
