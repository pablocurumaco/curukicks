---
name: filament-5
description: >
  Filament 5.4 patterns for resources, forms, tables, widgets, and enums.
  Trigger: When creating or modifying Filament resources, pages, widgets, or admin panel features.
license: Apache-2.0
metadata:
  author: pablocurumaco
  version: "1.0"
  scope: [root]
  auto_invoke: "Creating or modifying Filament resources, widgets, or admin panel"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash
---

## When to Use

- Creating a new Filament resource (CRUD)
- Adding/modifying table columns, filters, or actions
- Creating or editing forms with sections
- Building stats widgets
- Integrating PHP enums with Filament
- Adding inline editable columns
- Conditional row styling in tables

## Critical Patterns

### ALWAYS

- Use `Schema` (not `Form`) for form definitions in Filament 5.4
- Implement `HasLabel` and `HasColor` on enums used in Filament
- Use `getStateUsing()` for computed/accessor fields in tables
- Use `recordClasses()` for conditional row styling (not row actions)
- Group form fields in `Section::make()` with `->columns(N)`
- Use enum classes directly in `->options(EnumClass::class)`
- Use heroicon-o-* (outline) icons for stats and UI

### NEVER

- Use `Form` instead of `Schema` in form method signature
- Hardcode enum options as arrays — always reference the enum class
- Put business logic in Resources — keep it in Models
- Create service classes — use Active Record pattern with Eloquent

## Form Schema

Filament 5.4 uses `Schema` not `Form`:

```php
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        Section::make('Section Title')
            ->columns(3)
            ->schema([
                TextInput::make('field')
                    ->label('Label')
                    ->required()
                    ->placeholder('Placeholder'),

                TextInput::make('price')
                    ->label('Precio (Q)')
                    ->numeric()
                    ->prefix('Q'),

                Select::make('status')
                    ->label('Estado')
                    ->options(MyEnum::class)
                    ->required()
                    ->default(MyEnum::Default),

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(false),

                DatePicker::make('checked_at')
                    ->label('Fecha'),

                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]),
    ]);
}
```

## Table Columns

### Standard Columns

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

// Text with search and sort
TextColumn::make('model')
    ->label('Modelo')
    ->searchable()
    ->sortable()
    ->limit(25),

// Monospace copyable
TextColumn::make('code')
    ->searchable()
    ->copyable()
    ->fontFamily('mono')
    ->size('sm'),

// Numeric with prefix
TextColumn::make('cost')
    ->label('Costo (Q)')
    ->numeric()
    ->prefix('Q')
    ->sortable(),

// Boolean icon
IconColumn::make('has_box')
    ->label('Caja')
    ->boolean()
    ->width('50px'),

// Enum with badge (auto-uses HasColor)
TextColumn::make('condition')
    ->label('Estado')
    ->badge()
    ->sortable(),

// URL with icon
TextColumn::make('external_url')
    ->label('Link')
    ->url(fn ($record) => $record->external_url)
    ->openUrlInNewTab()
    ->icon('heroicon-o-arrow-top-right-on-square')
    ->iconPosition('after')
    ->formatStateUsing(fn ($state) => $state ? 'Ver' : ''),

// Toggleable (hidden by default)
TextColumn::make('optional_field')
    ->toggleable(isToggledHiddenByDefault: true),
```

### Inline Editable Columns

```php
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ToggleColumn;

// Editable text
TextInputColumn::make('sale_price')
    ->label('Precio')
    ->rules(['nullable', 'numeric'])
    ->sortable(),

// Editable select (enum)
SelectColumn::make('decision')
    ->label('Decision')
    ->options(MyEnum::class)
    ->sortable(),

// Editable toggle
ToggleColumn::make('is_public')
    ->label('Publico'),
```

### Computed/Accessor Columns

For model Attribute accessors, use `getStateUsing()`:

```php
TextColumn::make('profit')
    ->label('Ganancia')
    ->prefix('Q')
    ->getStateUsing(fn ($record) => $record->profit)
    ->numeric()
    ->color(fn ($record) => match (true) {
        $record->profit === null => 'gray',
        $record->profit > 0 => 'success',
        $record->profit < 0 => 'danger',
        default => 'gray',
    }),

TextColumn::make('margin')
    ->label('Margen')
    ->suffix('%')
    ->getStateUsing(fn ($record) => $record->margin)
    ->numeric()
    ->color(fn ($record) => match (true) {
        $record->margin === null => 'gray',
        $record->margin > 0 => 'success',
        $record->margin < 0 => 'danger',
        default => 'gray',
    }),
```

## Filters

```php
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

->filters([
    // Enum multi-select
    SelectFilter::make('decision')
        ->label('Decision')
        ->options(MyEnum::class)
        ->multiple(),

    // Enum single select
    SelectFilter::make('condition')
        ->label('Estado')
        ->options(MyEnum::class),

    // Boolean (true/false/all)
    TernaryFilter::make('is_public')
        ->label('Visible'),
])
```

## Conditional Row Styling

Use `recordClasses()` with `match(true)` and Tailwind classes:

```php
->recordClasses(fn ($record) => match (true) {
    $record->decision === MyEnum::INACTIVE => 'opacity-50',
    $record->profit !== null && $record->profit < 0 => '!bg-red-50 dark:!bg-red-900/10',
    $record->profit !== null && $record->profit > 0 => '!bg-green-50 dark:!bg-green-900/10',
    default => '',
})
->striped()
```

Note: Use `!` (important) prefix on background colors to override Filament defaults.

## Actions

```php
use Filament\Tables;

->actions([
    Tables\Actions\EditAction::make(),
    Tables\Actions\ViewAction::make(),
])
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\DeleteBulkAction::make(),
    ]),
])
```

### Page Header Actions

```php
// ListPage
protected function getHeaderActions(): array
{
    return [
        Actions\CreateAction::make(),
    ];
}

// EditPage
protected function getHeaderActions(): array
{
    return [
        Actions\DeleteAction::make(),
    ];
}
```

## Stats Widget

```php
use App\Enums\MyStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $items = MyModel::where('status', MyStatus::Active)->get();

        return [
            Stat::make('Total', $items->count())
                ->icon('heroicon-o-cube'),

            Stat::make('Costo Total', 'Q' . number_format($items->sum('cost')))
                ->icon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Ganancia', 'Q' . number_format($total))
                ->icon('heroicon-o-arrow-trending-up')
                ->color($total >= 0 ? 'success' : 'danger'),
        ];
    }
}
```

Register widget in ListPage:

```php
protected function getHeaderWidgets(): array
{
    return [
        \App\Filament\Widgets\MyStatsWidget::class,
    ];
}
```

## Enums for Filament

Every enum used in Filament forms/tables must implement `HasLabel` and `HasColor`:

```php
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MyEnum: string implements HasLabel, HasColor
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Inactive => 'Inactivo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'gray',
        };
    }
}
```

Filament color tokens: `success`, `warning`, `danger`, `info`, `gray`, `primary`.

## Resource Configuration

```php
class MyResource extends Resource
{
    protected static ?string $model = MyModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Mi Modulo';
    protected static ?string $modelLabel = 'Item';
    protected static ?string $pluralModelLabel = 'Items';
}
```

## Panel Provider

```php
// app/Providers/Filament/AdminPanelProvider.php
use Filament\Support\Colors\Color;

$panel
    ->default()
    ->id('admin')
    ->path('admin')
    ->login()
    ->colors(['primary' => Color::Amber])
    ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
    ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
    ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets');
```

## Commands

```bash
# Create new resource
php artisan make:filament-resource ModelName --generate

# Create widget
php artisan make:filament-widget WidgetName --stats-overview

# Create resource page
php artisan make:filament-page PageName --resource=ModelNameResource
```
