---
name: tailwind-4
description: >
  Tailwind CSS 4 patterns and best practices for Blade templates.
  Trigger: When styling with Tailwind in Blade views.
license: Apache-2.0
metadata:
  author: pablocurumaco
  version: "2.0"
  scope: [root]
  auto_invoke: "Styling Blade templates with Tailwind CSS"
---

## Styling Decision Tree

```
Tailwind class exists?  → class="..."
Dynamic value?          → style="width: {{ $percentage }}%"
Conditional styles?     → @class([...]) directive
Static only?            → class="..." (plain string)
```

## Critical Rules

### Never Use var() in class

```blade
{{-- NEVER: var() in class --}}
<div class="bg-[var(--color-primary)]"></div>

{{-- ALWAYS: Use Tailwind semantic classes --}}
<div class="bg-primary"></div>
<div class="bg-amber-500"></div>
```

### Never Use Hex Colors

```blade
{{-- NEVER: Hex colors in class --}}
<p class="text-[#ffffff]"></p>
<div class="bg-[#1e293b]"></div>

{{-- ALWAYS: Use Tailwind color classes --}}
<p class="text-white"></p>
<div class="bg-slate-800"></div>
```

## Blade @class Directive (Conditional Styles)

```blade
{{-- Conditional classes with @class --}}
<div @class([
    'rounded-lg border p-4',
    'bg-green-900/20 border-green-700' => $sneaker->profit > 0,
    'bg-red-900/20 border-red-700' => $sneaker->profit < 0,
    'bg-yellow-900/20 border-yellow-700' => !$sneaker->sale_price_gt,
    'opacity-50' => $sneaker->decision->value === 'USO_PERSONAL',
])>
    {{ $sneaker->model }}
</div>

{{-- Multiple conditions --}}
<span @class([
    'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
    'bg-green-500/10 text-green-400' => $sneaker->condition === 'DS',
    'bg-yellow-500/10 text-yellow-400' => $sneaker->condition === 'Used',
])>
    {{ $sneaker->condition }}
</span>
```

### When NOT to use @class

```blade
{{-- Static classes - just use class directly --}}
<div class="flex items-center gap-2"></div>

{{-- DON'T wrap static strings in @class --}}
<div @class(['flex items-center gap-2'])></div>
```

## Dynamic Values

```blade
{{-- style attribute for truly dynamic values --}}
<div style="width: {{ $percentage }}%"></div>
<div style="opacity: {{ $isVisible ? 1 : 0 }}"></div>

{{-- Inline styles for calculated values --}}
<div class="rounded-full bg-amber-500" style="width: {{ $margin }}%"></div>
```

## Common Patterns

### Flexbox

```blade
<div class="flex items-center justify-between gap-4"></div>
<div class="flex flex-col gap-2"></div>
<div class="inline-flex items-center"></div>
```

### Grid

```blade
<div class="grid grid-cols-3 gap-4"></div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"></div>
```

### Spacing

```blade
<div class="p-4"></div>
<div class="px-4 py-2"></div>
<div class="mx-auto"></div>
```

### Typography

```blade
<h1 class="text-2xl font-bold text-white"></h1>
<p class="text-sm text-zinc-400"></p>
<span class="text-xs font-medium uppercase tracking-wide"></span>
```

### States

```blade
<button class="hover:bg-amber-600 focus:ring-2 active:scale-95"></button>
<a class="transition-colors hover:text-amber-400"></a>
```

### Responsive

```blade
<div class="w-full md:w-1/2 lg:w-1/3"></div>
<div class="hidden md:block"></div>
<div class="text-sm md:text-base lg:text-lg"></div>
```

## CuruKicks Theme

Project uses dark theme with these conventions:
- **Background:** zinc-950, zinc-900
- **Accent:** amber-500, amber-400
- **Text primary:** white
- **Text secondary:** zinc-400
- **Borders:** zinc-700, zinc-800
- **Success:** green-500
- **Danger:** red-500
- **Warning:** yellow-500

## Arbitrary Values (Escape Hatch)

```blade
{{-- OK for one-off values not in design system --}}
<div class="w-[327px]"></div>
<div class="grid-cols-[1fr_2fr_1fr]"></div>

{{-- DON'T use for colors - use theme instead --}}
<div class="bg-[#1e293b]"></div>  {{-- NO --}}
```
