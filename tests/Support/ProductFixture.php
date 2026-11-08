<?php

namespace Tests\Support;

use App\Models\LookupValue;
use App\Models\Product;

class ProductFixture
{
    public static function make(int $programId, int $categoryId, bool $monthly = true): Product
    {
        $level = LookupValue::firstOrCreate(['category' => 'class_level', 'value' => 'Fixture'], ['sort_order' => 0]);
        $frequencyValue = $monthly ? 'Fixture' : 'Fixture Harian';
        $frequency = LookupValue::firstOrCreate(
            ['category' => 'class_frequency', 'value' => $frequencyValue],
            ['sort_order' => 0, 'is_monthly' => $monthly],
        );
        $suffix = $monthly ? '' : '-DATED';

        return Product::firstOrCreate(
            ['code' => "FX-{$programId}-{$categoryId}{$suffix}"],
            [
                'name' => 'Produk Fixture',
                'level_id' => $level->id,
                'program_id' => $programId,
                'category_id' => $categoryId,
                'frequency_id' => $frequency->id,
                'duration_minutes' => 60,
                'price' => 100000,
                'status' => 'active',
            ],
        );
    }
}
