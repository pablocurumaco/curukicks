<?php

namespace App\Filament\Resources\SneakerResource\RelationManagers;

use App\Enums\OfferStatus;
use App\Models\Offer;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';

    protected static ?string $title = 'Ofertas';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Comprador')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Oferta (Q)')
                    ->numeric()
                    ->prefix('Q')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Actions\Action::make('accept')
                    ->label('Aceptar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Offer $record) => $record->isPending())
                    ->action(function (Offer $record) {
                        $record->update([
                            'status' => OfferStatus::Accepted,
                            'responded_at' => now(),
                        ]);
                    }),

                \Filament\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Offer $record) => $record->isPending())
                    ->action(function (Offer $record) {
                        $record->update([
                            'status' => OfferStatus::Rejected,
                            'responded_at' => now(),
                        ]);
                    }),
            ]);
    }
}
