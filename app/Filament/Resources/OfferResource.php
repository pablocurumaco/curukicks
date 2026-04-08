<?php

namespace App\Filament\Resources;

use App\Enums\OfferStatus;
use App\Filament\Resources\OfferResource\Pages;
use App\Models\Offer;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Ofertas';

    protected static ?string $modelLabel = 'Oferta';

    protected static ?string $pluralModelLabel = 'Ofertas';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sneaker.model')
                    ->label('Sneaker')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Offer $record) => $record->sneaker->colorway . ' · ' . $record->sneaker->size),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Comprador')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Oferta (Q)')
                    ->numeric()
                    ->prefix('Q')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sneaker.sale_price_gt')
                    ->label('Precio pedido')
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

                Tables\Columns\TextColumn::make('responded_at')
                    ->label('Respondida')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('admin_notes')
                    ->label('Notas')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(OfferStatus::class),

                Tables\Filters\SelectFilter::make('sneaker_id')
                    ->label('Sneaker')
                    ->relationship('sneaker', 'model')
                    ->searchable()
                    ->preload(),
            ])
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
                    ->form([
                        \Filament\Forms\Components\Textarea::make('admin_notes')
                            ->label('Nota (opcional)')
                            ->rows(2),
                    ])
                    ->action(function (Offer $record, array $data) {
                        $record->update([
                            'status' => OfferStatus::Rejected,
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'responded_at' => now(),
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffers::route('/'),
        ];
    }
}
