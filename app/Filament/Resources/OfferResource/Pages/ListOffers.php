<?php

namespace App\Filament\Resources\OfferResource\Pages;

use App\Enums\OfferStatus;
use App\Filament\Resources\OfferResource;
use App\Models\Offer;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOffers extends ListRecords
{
    protected static string $resource = OfferResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(Offer::count()),

            'pending' => Tab::make('Pendientes')
                ->modifyQueryUsing(fn ($query) => $query->where('status', OfferStatus::Pending))
                ->badge(Offer::where('status', OfferStatus::Pending)->count())
                ->badgeColor('warning'),

            'accepted' => Tab::make('Aceptadas')
                ->modifyQueryUsing(fn ($query) => $query->where('status', OfferStatus::Accepted))
                ->badge(Offer::where('status', OfferStatus::Accepted)->count())
                ->badgeColor('success'),

            'rejected' => Tab::make('Rechazadas')
                ->modifyQueryUsing(fn ($query) => $query->where('status', OfferStatus::Rejected))
                ->badge(Offer::where('status', OfferStatus::Rejected)->count())
                ->badgeColor('danger'),
        ];
    }
}
