<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'code', 'level_id', 'program_id', 'category_id', 'frequency_id', 'duration_minutes', 'price', 'description', 'status', 'created_by', 'updated_by'])]
class Product extends Model
{
    public const STATUSES = ['active', 'inactive'];

    public function frequency(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'frequency_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'level_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'program_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
