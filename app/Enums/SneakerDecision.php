<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SneakerDecision: string implements HasLabel, HasColor
{
    case VENTA = 'VENTA';
    case POSIBLE_VENTA = 'POSIBLE VENTA';
    case VENTA_CONDICIONAL = 'VENTA CONDICIONAL';
    case ULTIMO_RECURSO = 'ÚLTIMO RECURSO';
    case NO_VENTA = 'NO VENTA';
    case PENDIENTE = 'PENDIENTE';
    case USO_PERSONAL = 'USO PERSONAL';
    case VENTA_GANCHO = 'VENTA (GANCHO)';
    case POR_REVISAR = 'POR REVISAR';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::VENTA => 'success',
            self::POSIBLE_VENTA => 'info',
            self::VENTA_CONDICIONAL => 'warning',
            self::ULTIMO_RECURSO => 'danger',
            self::NO_VENTA => 'gray',
            self::PENDIENTE => 'warning',
            self::USO_PERSONAL => 'gray',
            self::VENTA_GANCHO => 'info',
            self::POR_REVISAR => 'warning',
        };
    }

    public function isForSale(): bool
    {
        return in_array($this, [
            self::VENTA,
            self::POSIBLE_VENTA,
            self::VENTA_CONDICIONAL,
            self::VENTA_GANCHO,
        ]);
    }
}
