<?php

namespace App\Filament\Resources;

use App\Enums\SneakerCondition;
use App\Enums\SneakerDecision;
use App\Enums\SneakerStatus;
use App\Filament\Resources\SneakerResource\Pages;
use App\Models\Sneaker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SneakerResource extends Resource
{
    protected static ?string $model = Sneaker::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Inventario';

    protected static ?string $modelLabel = 'Sneaker';

    protected static ?string $pluralModelLabel = 'Sneakers';

    protected static ?string $recordTitleAttribute = 'model';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informacion del Par')
                    ->columns(3)
                    ->schema([
                        TextInput::make('inventory_number')
                            ->label('# Inventario')
                            ->numeric()
                            ->required(),
                        TextInput::make('model')
                            ->label('Modelo')
                            ->required()
                            ->placeholder('Air Jordan 4 Retro'),
                        TextInput::make('colorway')
                            ->label('Colorway')
                            ->required()
                            ->placeholder('Bred Reimagined'),
                        TextInput::make('style_code')
                            ->label('Style Code')
                            ->placeholder('FV5029-006'),
                        TextInput::make('size')
                            ->label('Talla')
                            ->required()
                            ->placeholder('9'),
                        Select::make('condition')
                            ->label('Estado')
                            ->options(SneakerCondition::class)
                            ->required(),
                        Toggle::make('has_box')
                            ->label('Tiene Caja')
                            ->default(false),
                        TextInput::make('store')
                            ->label('Tienda')
                            ->placeholder('StockX, Meat Pack, La Grieta...'),
                    ]),

                Section::make('Precios')
                    ->columns(3)
                    ->schema([
                        TextInput::make('cost_paid')
                            ->label('Costo Pagado (Q)')
                            ->numeric()
                            ->prefix('Q'),
                        TextInput::make('stockx_price_usd')
                            ->label('StockX Actual (USD)')
                            ->numeric()
                            ->prefix('$'),
                        DatePicker::make('stockx_checked_at')
                            ->label('Fecha Consulta StockX'),
                        TextInput::make('usd_multiplier')
                            ->label('Multiplicador USD→GT')
                            ->numeric()
                            ->default(11),
                        TextInput::make('sale_price_gt')
                            ->label('Precio Venta GT (Q)')
                            ->numeric()
                            ->prefix('Q'),
                    ]),

                Section::make('Decision y Visibilidad')
                    ->columns(2)
                    ->schema([
                        Select::make('decision')
                            ->label('Decision')
                            ->options(SneakerDecision::class)
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(SneakerStatus::class)
                            ->default(SneakerStatus::Available),
                        Toggle::make('is_public')
                            ->label('Visible para compradores')
                            ->default(false),
                    ]),

                Section::make('Links y Notas')
                    ->columns(1)
                    ->schema([
                        TextInput::make('stockx_url')
                            ->label('Link StockX')
                            ->url()
                            ->placeholder('https://stockx.com/...')
                            ->suffixAction(
                                \Filament\Actions\Action::make('openStockx')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->url(fn ($record) => $record?->stockx_url)
                                    ->openUrlInNewTab()
                                    ->visible(fn ($record) => filled($record?->stockx_url))
                            ),
                        Textarea::make('notes')
                            ->label('Notas Internas')
                            ->rows(3)
                            ->placeholder('Notas privadas sobre este par...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventory_number')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                TextColumn::make('colorway')
                    ->label('Colorway')
                    ->searchable()
                    ->limit(20),

                TextColumn::make('style_code')
                    ->label('Style Code')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('size')
                    ->label('Talla')
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('condition')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                IconColumn::make('has_box')
                    ->label('Caja')
                    ->boolean()
                    ->width('50px'),

                TextColumn::make('store')
                    ->label('Tienda')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cost_paid')
                    ->label('Costo (Q)')
                    ->numeric()
                    ->prefix('Q')
                    ->sortable(),

                TextColumn::make('stockx_price_usd')
                    ->label('StockX ($)')
                    ->numeric()
                    ->prefix('$')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('stockx_gt')
                    ->label('StockX→GT')
                    ->prefix('Q')
                    ->getStateUsing(fn (Sneaker $record) => $record->stockx_gt)
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextInputColumn::make('sale_price_gt')
                    ->label('Venta (Q)')
                    ->rules(['nullable', 'numeric'])
                    ->sortable(),

                TextColumn::make('profit')
                    ->label('Ganancia')
                    ->prefix('Q')
                    ->getStateUsing(fn (Sneaker $record) => $record->profit)
                    ->numeric()
                    ->color(fn (Sneaker $record) => match (true) {
                        $record->profit === null => 'gray',
                        $record->profit > 0 => 'success',
                        $record->profit < 0 => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('margin')
                    ->label('Margen')
                    ->suffix('%')
                    ->getStateUsing(fn (Sneaker $record) => $record->margin)
                    ->numeric()
                    ->color(fn (Sneaker $record) => match (true) {
                        $record->margin === null => 'gray',
                        $record->margin > 0 => 'success',
                        $record->margin < 0 => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                SelectColumn::make('decision')
                    ->label('Decision')
                    ->options(SneakerDecision::class)
                    ->sortable(),

                ToggleColumn::make('is_public')
                    ->label('Publico'),

                TextColumn::make('stockx_url')
                    ->label('StockX')
                    ->url(fn (Sneaker $record) => $record->stockx_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->formatStateUsing(fn ($state) => $state ? 'Ver' : '')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('inventory_number')
            ->filters([
                SelectFilter::make('decision')
                    ->label('Decision')
                    ->options(SneakerDecision::class)
                    ->multiple(),

                SelectFilter::make('condition')
                    ->label('Estado')
                    ->options(SneakerCondition::class),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(SneakerStatus::class),

                TernaryFilter::make('has_box')
                    ->label('Tiene Caja'),

                TernaryFilter::make('is_public')
                    ->label('Visible Publico'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordClasses(fn (Sneaker $record) => match (true) {
                $record->decision === SneakerDecision::USO_PERSONAL,
                $record->decision === SneakerDecision::NO_VENTA => 'opacity-50',
                $record->profit !== null && $record->profit < 0 => '!bg-red-50 dark:!bg-red-900/10',
                $record->sale_price_gt === null && $record->decision->isForSale() => '!bg-yellow-50 dark:!bg-yellow-900/10',
                $record->profit !== null && $record->profit > 0 => '!bg-green-50 dark:!bg-green-900/10',
                default => '',
            })
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSneakers::route('/'),
            'create' => Pages\CreateSneaker::route('/create'),
            'edit' => Pages\EditSneaker::route('/{record}/edit'),
        ];
    }
}
