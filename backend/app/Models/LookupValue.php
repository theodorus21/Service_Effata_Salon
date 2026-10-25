<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['category', 'value', 'sort_order', 'is_monthly'])]
class LookupValue extends Model
{
    protected function casts(): array
    {
        return [
            'is_monthly' => 'boolean',
        ];
    }

    public function learningClasses(): HasManyThrough
    {
        return $this->hasManyThrough(LearningClass::class, Product::class, 'program_id', 'product_id');
    }

    public function frequencyProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'frequency_id');
    }

    public function levelProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'level_id');
    }

    public function programProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'program_id');
    }

    public function categoryProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function categorizedClasses(): HasManyThrough
    {
        return $this->hasManyThrough(LearningClass::class, Product::class, 'category_id', 'product_id');
    }
}
