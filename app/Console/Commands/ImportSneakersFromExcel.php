<?php

namespace App\Console\Commands;

use App\Enums\SneakerCondition;
use App\Enums\SneakerDecision;
use App\Enums\SneakerStatus;
use App\Models\Sneaker;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportSneakersFromExcel extends Command
{
    protected $signature = 'sneakers:import {file? : Path to the Excel file}';

    protected $description = 'Import sneakers from the inventory Excel file';

    private const DECISION_MAP = [
        'VENTA' => SneakerDecision::VENTA,
        'POSIBLE VENTA' => SneakerDecision::POSIBLE_VENTA,
        'VENTA CONDICIONAL' => SneakerDecision::VENTA_CONDICIONAL,
        'ÚLTIMO RECURSO' => SneakerDecision::ULTIMO_RECURSO,
        'NO VENTA' => SneakerDecision::NO_VENTA,
        'PENDIENTE' => SneakerDecision::PENDIENTE,
        'USO PERSONAL' => SneakerDecision::USO_PERSONAL,
        'VENTA (GANCHO)' => SneakerDecision::VENTA_GANCHO,
        'POR REVISAR' => SneakerDecision::POR_REVISAR,
    ];

    public function handle(): int
    {
        $file = $this->argument('file') ?? storage_path('app/imports/Inventario_Sneakers_Pablo_Nuevo_Final.xlsx');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $this->info("Importing from: {$file}");

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheet(0); // "Inventario Verificado"
        $rows = $sheet->toArray(null, true, true, true);

        $headers = array_shift($rows); // Remove header row
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $rowIndex => $row) {
            $inventoryNumber = $row['A'];

            // Stop at empty rows or summary section
            if (empty($inventoryNumber) || ! is_numeric($inventoryNumber)) {
                break;
            }

            $decision = $this->mapDecision(trim($row['P'] ?? ''));

            if (! $decision) {
                $this->warn("Row {$inventoryNumber}: Unknown decision '{$row['P']}', skipping");
                $skipped++;

                continue;
            }

            $costPaid = $this->parseNumeric($row['I']);
            $stockxUsd = $this->parseNumeric($row['J']);
            $salePriceGt = $this->parseNumeric($row['M']);
            $stockxDate = $this->parseDate($row['K']);

            $model = trim($row['B'] ?? '');
            $colorway = trim($row['C'] ?? '');
            $size = trim($row['E'] ?? '');

            $sneaker = Sneaker::updateOrCreate(
                ['inventory_number' => (int) $inventoryNumber],
                [
                    'model' => $model,
                    'colorway' => $colorway,
                    'style_code' => $this->cleanDash($row['D']),
                    'size' => $size,
                    'condition' => $row['F'] === 'DS' ? SneakerCondition::DS : SneakerCondition::Used,
                    'has_box' => strtolower(trim($row['G'] ?? '')) === 'sí',
                    'store' => $this->cleanDash($row['H']),
                    'cost_paid' => $costPaid,
                    'stockx_price_usd' => $stockxUsd,
                    'stockx_checked_at' => $stockxDate,
                    'usd_multiplier' => 11,
                    'sale_price_gt' => $salePriceGt,
                    'decision' => $decision,
                    'stockx_url' => $this->cleanDash($row['Q']),
                    'notes' => $this->cleanDash($row['R']),
                    'is_public' => false,
                    'status' => SneakerStatus::Available,
                    'slug' => Str::slug($model . ' ' . $colorway . ' ' . $size),
                ]
            );

            $imported++;
            $this->line("  [{$inventoryNumber}] {$model} - {$colorway} ({$size})");
        }

        $this->newLine();
        $this->info("Imported: {$imported} sneakers");

        if ($skipped > 0) {
            $this->warn("Skipped: {$skipped} rows");
        }

        return self::SUCCESS;
    }

    private function mapDecision(string $value): ?SneakerDecision
    {
        return self::DECISION_MAP[strtoupper($value)] ?? null;
    }

    private function parseNumeric(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 'None') {
            return null;
        }

        // Skip formula strings
        if (is_string($value) && str_starts_with($value, '=')) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return $cleaned !== '' ? (int) round((float) $cleaned) : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === 'None') {
            return null;
        }

        // Excel serial date number
        if (is_numeric($value) && (float) $value > 40000) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        // String date like "18/03/2026"
        if (is_string($value) && preg_match('#(\d{2})/(\d{2})/(\d{4})#', $value, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        return null;
    }

    private function cleanDash(?string $value): ?string
    {
        if ($value === null || $value === '' || $value === '—' || $value === 'None') {
            return null;
        }

        return trim($value);
    }
}
