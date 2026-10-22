<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['learning_class_id', 'day', 'lesson_time', 'duration_minutes'])]
class LearningClassSchedule extends Model
{
    public function learningClass(): BelongsTo
    {
        return $this->belongsTo(LearningClass::class);
    }
}
