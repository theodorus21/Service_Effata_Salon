<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category', 'value', 'sort_order'])]
class LookupValue extends Model
{
    public function learningClasses(): HasMany
    {
        return $this->hasMany(LearningClass::class, 'program_id');
    }

    public function categorizedClasses(): HasMany
    {
        return $this->hasMany(LearningClass::class, 'category_id');
    }
}
