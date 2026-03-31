---
name: pest
description: >
  Pest 4.4 testing patterns for Laravel 13 and Filament 5.4.
  Trigger: When writing or modifying tests, creating test files, or running tests.
license: Apache-2.0
metadata:
  author: pablocurumaco
  version: "1.0"
  scope: [root]
  auto_invoke: "Writing tests, creating test files, running test suite"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash
---

## When to Use

- Writing new tests (unit or feature)
- Creating factories for models
- Testing Filament resources, pages, or widgets
- Testing public routes (catalog)
- Testing Artisan commands
- Testing Eloquent models, scopes, and accessors

## Critical Patterns

### ALWAYS

- Use Pest closure syntax (`test()`, `it()`), never PHPUnit classes
- Use `expect()` API for assertions, not `$this->assert*()`
- Use `RefreshDatabase` trait for any test that touches the database
- Create factories for every model that needs test data
- Keep tests in correct directory: `tests/Feature/` or `tests/Unit/`
- Name test files matching the thing they test: `SneakerTest.php`, `CatalogTest.php`

### NEVER

- Use PHPUnit class-based syntax
- Mock the database when you can use RefreshDatabase + factories
- Put database tests in `tests/Unit/` — those go in `tests/Feature/`
- Skip creating a factory — always create one for testable models

## Test Syntax

### Feature Tests (with Laravel context)

```php
// tests/Feature/CatalogTest.php
use App\Models\Sneaker;

it('shows public sneakers on catalog', function () {
    $sneaker = Sneaker::factory()->public()->create();

    $this->get('/')
        ->assertStatus(200)
        ->assertSee($sneaker->model);
});

it('hides private sneakers from catalog', function () {
    $sneaker = Sneaker::factory()->create(['is_public' => false]);

    $this->get('/')
        ->assertStatus(200)
        ->assertDontSee($sneaker->model);
});

it('shows sneaker detail page', function () {
    $sneaker = Sneaker::factory()->public()->create();

    $this->get("/sneaker/{$sneaker->slug}")
        ->assertStatus(200)
        ->assertSee($sneaker->model)
        ->assertSee($sneaker->colorway);
});

it('returns 404 for private sneaker detail', function () {
    $sneaker = Sneaker::factory()->create(['is_public' => false]);

    $this->get("/sneaker/{$sneaker->slug}")
        ->assertStatus(404);
});
```

### Unit Tests (pure logic, no Laravel)

```php
// tests/Unit/SneakerMarginTest.php
test('profit is calculated correctly', function () {
    expect(1500 - 1000)->toBe(500);
});

test('margin percentage is correct', function () {
    $profit = 500;
    $cost = 1000;
    $margin = round(($profit / $cost) * 100, 1);

    expect($margin)->toBe(50.0);
});
```

## Expect API (Assertions)

```php
// Equality
expect($value)->toBe(42);
expect($value)->toEqual($other);
expect($value)->not->toBe(0);

// Truthiness
expect($value)->toBeTrue();
expect($value)->toBeFalse();
expect($value)->toBeNull();
expect($value)->not->toBeNull();

// Types
expect($value)->toBeString();
expect($value)->toBeInt();
expect($value)->toBeFloat();
expect($value)->toBeArray();
expect($value)->toBeInstanceOf(Sneaker::class);

// Collections/Arrays
expect($array)->toHaveCount(3);
expect($array)->toContain('value');
expect($array)->toHaveKey('key');
expect($array)->each->toBeString();

// Strings
expect($string)->toContain('substring');
expect($string)->toStartWith('prefix');
expect($string)->toMatch('/regex/');

// Numbers
expect($number)->toBeGreaterThan(0);
expect($number)->toBeLessThan(100);
expect($number)->toBeBetween(1, 10);

// Exceptions
expect(fn () => riskyOperation())->toThrow(Exception::class);
expect(fn () => riskyOperation())->toThrow(Exception::class, 'message');
```

## Model Factory

```php
// database/factories/SneakerFactory.php
namespace Database\Factories;

use App\Enums\SneakerCondition;
use App\Enums\SneakerDecision;
use App\Enums\SneakerStatus;
use App\Models\Sneaker;
use Illuminate\Database\Eloquent\Factories\Factory;

class SneakerFactory extends Factory
{
    protected $model = Sneaker::class;

    public function definition(): array
    {
        return [
            'inventory_number' => $this->faker->unique()->numberBetween(1, 999),
            'model' => $this->faker->randomElement([
                'Air Jordan 4 Retro', 'Nike Dunk Low', 'Air Force 1',
                'New Balance 550', 'Yeezy Boost 350',
            ]),
            'colorway' => $this->faker->words(2, true),
            'style_code' => strtoupper($this->faker->bothify('??####-###')),
            'size' => $this->faker->randomElement([8, 8.5, 9, 9.5, 10, 10.5, 11]),
            'condition' => $this->faker->randomElement(SneakerCondition::cases()),
            'has_box' => $this->faker->boolean(80),
            'store' => $this->faker->randomElement(['StockX', 'GOAT', 'Meat Pack', 'No Love']),
            'cost_paid' => $this->faker->numberBetween(500, 3000),
            'decision' => $this->faker->randomElement(SneakerDecision::cases()),
            'status' => SneakerStatus::Available,
            'is_public' => false,
        ];
    }

    // State: public and for sale
    public function public(): static
    {
        return $this->state(fn () => [
            'is_public' => true,
            'sale_price_gt' => $this->faker->numberBetween(1000, 5000),
            'decision' => SneakerDecision::VENTA,
            'status' => SneakerStatus::Available,
        ]);
    }

    // State: sold
    public function sold(): static
    {
        return $this->state(fn () => [
            'status' => SneakerStatus::Sold,
            'is_public' => false,
        ]);
    }

    // State: personal use
    public function personal(): static
    {
        return $this->state(fn () => [
            'decision' => SneakerDecision::USO_PERSONAL,
            'is_public' => false,
        ]);
    }

    // State: with StockX pricing
    public function withStockxPrice(): static
    {
        return $this->state(fn () => [
            'stockx_price_usd' => $this->faker->numberBetween(100, 500),
            'usd_multiplier' => 11,
            'stockx_checked_at' => now(),
        ]);
    }

    // State: profitable (sale > cost)
    public function profitable(): static
    {
        return $this->state(function () {
            $cost = $this->faker->numberBetween(500, 1500);
            return [
                'cost_paid' => $cost,
                'sale_price_gt' => $cost + $this->faker->numberBetween(200, 1000),
                'is_public' => true,
                'decision' => SneakerDecision::VENTA,
            ];
        });
    }
}
```

## Testing Eloquent Models

```php
// tests/Feature/Models/SneakerTest.php
use App\Models\Sneaker;
use App\Enums\SneakerDecision;
use App\Enums\SneakerStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('profit accessor returns correct value', function () {
    $sneaker = Sneaker::factory()->create([
        'cost_paid' => 1000,
        'sale_price_gt' => 1500,
    ]);

    expect($sneaker->profit)->toBe(500);
});

test('profit is null when sale price missing', function () {
    $sneaker = Sneaker::factory()->create([
        'cost_paid' => 1000,
        'sale_price_gt' => null,
    ]);

    expect($sneaker->profit)->toBeNull();
});

test('margin accessor calculates percentage', function () {
    $sneaker = Sneaker::factory()->create([
        'cost_paid' => 1000,
        'sale_price_gt' => 1500,
    ]);

    expect($sneaker->margin)->toBe(50.0);
});

test('stockx_gt converts USD to quetzales', function () {
    $sneaker = Sneaker::factory()->create([
        'stockx_price_usd' => 200,
        'usd_multiplier' => 11,
    ]);

    expect($sneaker->stockx_gt)->toBe(2200);
});

test('public scope filters visible sneakers', function () {
    Sneaker::factory()->create(['is_public' => true]);
    Sneaker::factory()->create(['is_public' => false]);

    expect(Sneaker::public()->count())->toBe(1);
});

test('forSale scope filters sale decisions', function () {
    Sneaker::factory()->create(['decision' => SneakerDecision::VENTA]);
    Sneaker::factory()->create(['decision' => SneakerDecision::USO_PERSONAL]);

    expect(Sneaker::forSale()->count())->toBe(1);
});

test('slug is auto-generated on create', function () {
    $sneaker = Sneaker::factory()->create([
        'model' => 'Air Jordan 4',
        'colorway' => 'Bred',
        'size' => 9.5,
    ]);

    expect($sneaker->slug)->not->toBeNull()
        ->and($sneaker->slug)->toContain('air-jordan-4');
});
```

## Testing Artisan Commands

```php
// tests/Feature/Commands/ImportSneakersTest.php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports sneakers from excel', function () {
    $this->artisan('sneakers:import', [
        'file' => base_path('tests/fixtures/test_inventory.xlsx'),
    ])
        ->assertExitCode(0);

    $this->assertDatabaseCount('sneakers', 5); // expected row count
});

it('skips rows with invalid data', function () {
    $this->artisan('sneakers:import', [
        'file' => base_path('tests/fixtures/invalid_inventory.xlsx'),
    ])
        ->expectsOutputToContain('Skipped')
        ->assertExitCode(0);
});
```

## Testing Filament Resources

```php
// tests/Feature/Filament/SneakerResourceTest.php
use App\Filament\Resources\SneakerResource;
use App\Filament\Resources\SneakerResource\Pages\ListSneakers;
use App\Filament\Resources\SneakerResource\Pages\CreateSneaker;
use App\Models\Sneaker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render list page', function () {
    $this->get(SneakerResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render create page', function () {
    $this->get(SneakerResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render edit page', function () {
    $sneaker = Sneaker::factory()->create();

    $this->get(SneakerResource::getUrl('edit', ['record' => $sneaker]))
        ->assertSuccessful();
});

it('can list sneakers', function () {
    $sneakers = Sneaker::factory()->count(3)->create();

    Livewire::test(ListSneakers::class)
        ->assertCanSeeTableRecords($sneakers);
});

it('can create a sneaker', function () {
    $data = Sneaker::factory()->make()->toArray();

    Livewire::test(CreateSneaker::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('sneakers', [
        'model' => $data['model'],
    ]);
});

it('can filter by decision', function () {
    $forSale = Sneaker::factory()->create(['decision' => SneakerDecision::VENTA]);
    $personal = Sneaker::factory()->create(['decision' => SneakerDecision::USO_PERSONAL]);

    Livewire::test(ListSneakers::class)
        ->filterTable('decision', SneakerDecision::VENTA->value)
        ->assertCanSeeTableRecords([$forSale])
        ->assertCanNotSeeTableRecords([$personal]);
});
```

## Dataset / Parameterized Tests

```php
it('calculates correct margin for different scenarios', function (int $cost, int $sale, float $expected) {
    $sneaker = Sneaker::factory()->create([
        'cost_paid' => $cost,
        'sale_price_gt' => $sale,
    ]);

    expect($sneaker->margin)->toBe($expected);
})->with([
    'break even' => [1000, 1000, 0.0],
    '50% profit' => [1000, 1500, 50.0],
    'loss' => [1000, 800, -20.0],
    'double' => [1000, 2000, 100.0],
]);
```

## Hooks (beforeEach, afterEach)

```php
// Global in tests/Pest.php
uses(RefreshDatabase::class)->in('Feature');

// Per-file
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function () {
    // cleanup if needed
});
```

## Test Organization

```
tests/
├── Feature/
│   ├── Catalog/
│   │   └── CatalogTest.php        # Public routes
│   ├── Commands/
│   │   └── ImportSneakersTest.php  # Artisan commands
│   ├── Filament/
│   │   └── SneakerResourceTest.php # Admin CRUD
│   └── Models/
│       └── SneakerTest.php         # Eloquent scopes, accessors
├── Unit/
│   └── Enums/
│       └── SneakerDecisionTest.php # Pure enum logic
├── Pest.php
└── TestCase.php
```

## Commands

```bash
# Run all tests
php artisan test

# Run specific file
php artisan test tests/Feature/Models/SneakerTest.php

# Run with filter
php artisan test --filter="profit"

# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run in parallel
php artisan test --parallel
```
