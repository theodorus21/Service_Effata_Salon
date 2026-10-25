<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'student_id', 'teacher_id', 'program_id', 'category_id'])]
class LearningClass extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'program_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'category_id');
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
}
