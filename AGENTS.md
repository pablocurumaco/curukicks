# CuruKicks — Sistema de Inventario y Venta de Sneakers

## Proyecto

Aplicación web para gestionar inventario de sneakers (~30-40 pares), publicar pares en venta y recibir contraofertas de compradores. Doble propósito: catálogo personal de Pablo y base/demo para un cliente revendedor.

- **Dominio:** kicks.curumakito.com
- **Fase actual:** MVP en desarrollo (Fase 1 parcialmente implementada)

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13 (PHP) |
| Admin Panel | Filament 5.4 |
| Frontend público | Blade + Tailwind CSS 4 (CDN en layout, Vite para assets) |
| Base de datos | SQLite (desarrollo) |
| Build | Vite 8 |
| Testing | Pest 4.4 |
| Excel import | Maatwebsite Excel 3.1 |
| Imágenes | Intervention Image 4.0 |
| Storage | League Flysystem S3 (preparado para Cloudflare R2) |

## Estructura del proyecto

```
app/
├── Console/Commands/         # ImportSneakersFromExcel
├── Enums/                    # SneakerCondition, SneakerDecision, SneakerStatus
├── Filament/
│   ├── Resources/            # SneakerResource (CRUD completo) + Pages/
│   └── Widgets/              # SneakerStatsWidget (KPIs)
├── Http/Controllers/         # CatalogController (index, show)
├── Models/                   # User, Sneaker
└── Providers/                # AppServiceProvider, AdminPanelProvider
database/
├── factories/                # UserFactory
├── migrations/               # users, cache, jobs, sneakers
└── seeders/                  # DatabaseSeeder (test user)
resources/views/
├── layouts/catalog.blade.php # Layout dark theme (zinc-950, amber accents)
└── catalog/                  # index.blade.php, show.blade.php
routes/
├── web.php                   # GET / (catalog.index), GET /sneaker/{slug} (catalog.show)
└── console.php               # Default inspire command
```

## Modelo principal: Sneaker

32 campos. Los más importantes:

- **Identificación:** inventory_number (unique), model, colorway, style_code, size, slug (auto-generado)
- **Condición:** condition (enum: DS/Used), has_box
- **Adquisición:** store, cost_paid (Q)
- **Pricing:** stockx_price_usd, usd_multiplier (default 11), sale_price_gt (Q)
- **Decisión:** decision (enum: VENTA, POSIBLE_VENTA, VENTA_CONDICIONAL, ULTIMO_RECURSO, NO_VENTA, PENDIENTE, USO_PERSONAL, VENTA_GANCHO, POR_REVISAR)
- **Estado:** status (enum: Available, Sold)
- **Visibilidad:** is_public (boolean)

**Campos calculados (accessors):** stockx_gt, profit, margin

**Scopes:** public(), available(), forSale()

## Enums

- `SneakerCondition`: DS, Used
- `SneakerDecision`: VENTA, POSIBLE_VENTA, VENTA_CONDICIONAL, ULTIMO_RECURSO, NO_VENTA, PENDIENTE, USO_PERSONAL, VENTA_GANCHO, POR_REVISAR
- `SneakerStatus`: Available, Sold

## Rutas

| Método | URI | Controller | Descripción |
|--------|-----|-----------|-------------|
| GET | `/` | CatalogController@index | Catálogo público (sneakers public + available) |
| GET | `/sneaker/{sneaker:slug}` | CatalogController@show | Detalle de sneaker (valida public + available) |
| — | `/admin/*` | Filament | Panel admin con login |

## Comandos Artisan

- `php artisan sneakers:import {file?}` — Importa inventario desde Excel. Default: `storage/app/imports/Inventario_Sneakers_Pablo_Nuevo_Final.xlsx`. Usa updateOrCreate por inventory_number.

## Filament Admin (/admin)

- **SneakerResource:** CRUD completo con tabla sorteable, filtros (decision, condition, status, has_box, is_public), edición inline de sale_price_gt, colores condicionales (verde=ganancia, rojo=pérdida, amarillo=sin precio, gris=uso personal)
- **SneakerStatsWidget:** Total pares, pares en venta, costo invertido, valor de venta, ganancia potencial

## Fases del roadmap

### Fase 1 — MVP (en progreso)
- [x] Modelo Sneaker con migraciones y enums
- [x] Import desde Excel
- [x] Filament Resource con tabla interactiva, filtros, KPIs
- [x] Catálogo público (Blade) con grid de cards
- [x] Detalle de sneaker público
- [ ] Subida de fotos (modelo preparado, UI pendiente)
- [ ] Edición inline completa en tabla

### Fase 2 — Catálogo y Ofertas
- [ ] Registro simple de compradores (nombre, WhatsApp, email opcional)
- [ ] Sistema de ofertas/contraofertas
- [ ] Vista de negociación para el owner
- [ ] Modelo Offer con historial

### Fase 3 — Ventas y Notificaciones
- [ ] Registro de ventas (estado VENDIDO, historial)
- [ ] Notificaciones (email, manual WhatsApp)
- [ ] Expiración automática de ofertas (default 7 días)

### Fase 4 — Nice to Have
- [ ] WhatsApp Business API
- [ ] Actualización automática precios StockX
- [ ] Modo oscuro en admin
- [ ] PWA o React Native
- [ ] Links individuales compartibles por par

## Convenciones

- **Moneda:** Q (Quetzales guatemaltecos). Costos y precios se almacenan como integers.
- **Multiplicador USD→Q:** Configurable por sneaker, default 11
- **Slugs:** Auto-generados desde model + colorway + size
- **Enums:** Backed string enums en `app/Enums/`, con labels y colores para Filament
- **Admin auth:** Filament login en `/admin`, color primario amber
- **Frontend público:** Dark theme (zinc-950), acentos amber, font Inter (Google Fonts CDN)

## Skills (Auto-invoke)

When performing these actions, ALWAYS read the corresponding skill FIRST:

| Action | Skill |
|--------|-------|
| Creating or modifying Filament resources, widgets, admin panel | `Skills/filament-5/SKILL.md` |
| Writing tests, creating test files, running test suite | `Skills/pest/SKILL.md` |
| Styling Blade templates with Tailwind CSS | `Skills/tailwind-4/SKILL.md` |
| Committing changes to git | `Skills/commit/SKILL.md` |
| Creating a pull request, opening PR | `Skills/create-pr/SKILL.md` |
| Creating new skills | `Skills/skill-creator/SKILL.md` |
| After creating/modifying a skill | `Skills/skill-sync/SKILL.md` |

## Comandos útiles

```bash
# Desarrollo
php artisan serve                    # Servidor local
npm run dev                          # Vite dev server

# Base de datos
php artisan migrate                  # Correr migraciones
php artisan migrate:fresh --seed     # Reset completo + seed
php artisan sneakers:import          # Importar Excel

# Testing
php artisan test                     # Correr tests con Pest

# Filament
php artisan make:filament-resource   # Nuevo resource
php artisan make:filament-widget     # Nuevo widget

# Código
./vendor/bin/pint                    # Laravel Pint (linter/formatter)
```
