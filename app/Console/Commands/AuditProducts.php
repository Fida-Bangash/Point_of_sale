<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class AuditProducts extends Command
{
    protected $signature = 'pos:audit-products';

    protected $description = 'List products with a missing or duplicate barcode / SKU';

    public function handle(): int
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'barcode']);

        $missing = $products->filter(fn ($p) => blank($p->barcode) && blank($p->sku));

        $dupBarcodes = $products->filter(fn ($p) => filled($p->barcode))
            ->groupBy('barcode')->filter(fn ($g) => $g->count() > 1);

        $dupSkus = $products->filter(fn ($p) => filled($p->sku))
            ->groupBy('sku')->filter(fn ($g) => $g->count() > 1);

        $this->info($products->count() . ' products checked.');

        if ($missing->isNotEmpty()) {
            $this->warn("\nNo barcode and no SKU ({$missing->count()}):");
            $this->table(['ID', 'Name'], $missing->map(fn ($p) => [$p->id, $p->name])->all());
        }

        foreach (['Barcode' => $dupBarcodes, 'SKU' => $dupSkus] as $label => $groups) {
            if ($groups->isNotEmpty()) {
                $this->warn("\nDuplicate {$label}:");
                $rows = [];
                foreach ($groups as $value => $group) {
                    foreach ($group as $p) {
                        $rows[] = [$value, $p->id, $p->name];
                    }
                }
                $this->table([$label, 'ID', 'Name'], $rows);
            }
        }

        if ($missing->isEmpty() && $dupBarcodes->isEmpty() && $dupSkus->isEmpty()) {
            $this->info('All products have a unique barcode or SKU.');

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}