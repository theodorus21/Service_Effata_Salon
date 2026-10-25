<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'learning_class_id',
    'teacher_id',
    'student_id',
    'program_id',
    'period_start',
    'period_end',
    'development_evaluation',
    'additional_notes',
    'created_by',
    'updated_by',
])]
class LearningClassReport extends Model
{
    public function learningClass(): BelongsTo
    {
        return $this->belongsTo(LearningClass::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'program_id');
    }
}
