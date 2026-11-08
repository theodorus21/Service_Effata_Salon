<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'learning_class_lesson_id',
    'student_id',
    'attendance_status',
    'attendance_at',
    'lesson_description',
    'notes',
    'reason',
    'lesson_photo',
])]
class LearningClassLessonStudent extends Model
{
    public const ATTENDANCE_STATUSES = [
        'present' => 'Hadir',
        'permission' => 'Izin',
        'sick' => 'Sakit',
        'absent' => 'Tanpa keterangan',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LearningClassLesson::class, 'learning_class_lesson_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
