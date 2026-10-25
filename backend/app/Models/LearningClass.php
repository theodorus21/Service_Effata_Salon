<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[Fillable(['name', 'product_id', 'price', 'student_id', 'teacher_id', 'status'])]
class LearningClass extends Model
{
    public const STATUSES = ['active', 'inactive', 'complete'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function program(): HasOneThrough
    {
        return $this->hasOneThrough(LookupValue::class, Product::class, 'id', 'id', 'product_id', 'program_id');
    }

    public function category(): HasOneThrough
    {
        return $this->hasOneThrough(LookupValue::class, Product::class, 'id', 'id', 'product_id', 'category_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LearningClassSchedule::class)->orderBy('day')->orderBy('lesson_time');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(LearningClassLesson::class)->orderBy('lesson_date')->orderBy('lesson_time');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(LearningClassReport::class);
    }

    public function isMonthly(): bool
    {
        return (bool) $this->product?->frequency?->is_monthly;
    }
}
