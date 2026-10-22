<?php

namespace Tests\Feature;

use App\Models\LearningClass;
use App\Models\LearningClassLesson;
use App\Models\LookupValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class LearningClassLessonTest extends TestCase
{
    use RefreshDatabase;
    use AuthenticatesAsMasterAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    public function test_class_list_can_generate_monthly_lesson_records_from_weekly_schedules(): void
    {
        $learningClass = $this->createMondayClass();

        $this->get(route('learning-classes.index', ['month' => '2026-10']))
            ->assertOk()
            ->assertSee('value="2026-10"', false)
            ->assertSee(route('learning-classes.lessons.generate', $learningClass), false)
            ->assertSee('Generate jadwal');

        $this->post(route('learning-classes.lessons.generate', $learningClass), [
            'month' => '2026-10',
        ])
            ->assertRedirect(route('learning-classes.index', ['month' => '2026-10']))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('learning_class_lessons', 4);
        $this->assertDatabaseHas('learning_class_lessons', [
            'learning_class_id' => $learningClass->id,
            'lesson_date' => '2026-10-05',
            'lesson_time' => '09:30',
            'duration_minutes' => 60,
            'status' => 0,
            'teacher_attendance_at' => null,
            'lesson_photo' => null,
            'lesson_description' => null,
            'notes' => null,
        ]);
        $this->assertDatabaseHas('learning_class_lessons', [
            'learning_class_id' => $learningClass->id,
            'lesson_date' => '2026-10-26',
            'lesson_time' => '09:30',
        ]);

        $lesson = LearningClassLesson::query()->firstOrFail();
        $this->assertSame($learningClass->id, $lesson->learningClass->id);
    }

    public function test_generating_same_month_again_does_not_duplicate_or_overwrite_lesson_records(): void
    {
        $learningClass = $this->createMondayClass();
        $route = route('learning-classes.lessons.generate', $learningClass);

        $this->post($route, ['month' => '2026-10'])->assertRedirect();
        $lesson = LearningClassLesson::query()->firstOrFail();
        $lesson->update([
            'teacher_attendance_at' => '2026-10-05 09:20:00',
            'lesson_description' => 'Catatan sesi sudah berlangsung.',
        ]);

        $this->post($route, ['month' => '2026-10'])
            ->assertRedirect(route('learning-classes.index', ['month' => '2026-10']))
            ->assertSessionHas('status', fn (string $status): bool => str_starts_with($status, '0 jadwal les'));

        $this->assertDatabaseCount('learning_class_lessons', 4);
        $this->assertDatabaseHas('learning_class_lessons', [
            'id' => $lesson->id,
            'teacher_attendance_at' => '2026-10-05 09:20:00',
            'lesson_description' => 'Catatan sesi sudah berlangsung.',
        ]);
    }

    public function test_generation_requires_a_valid_month(): void
    {
        $learningClass = $this->createMondayClass();

        $this->from(route('learning-classes.index'))
            ->post(route('learning-classes.lessons.generate', $learningClass), ['month' => 'not-a-month'])
            ->assertRedirect(route('learning-classes.index'))
            ->assertSessionHasErrors('month');

        $this->assertDatabaseCount('learning_class_lessons', 0);
    }

    private function createMondayClass(): LearningClass
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'murid']);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();
        $category = LookupValue::query()->where('category', 'class_category')->firstOrFail();
        $learningClass = LearningClass::query()->create([
            'name' => 'Kelas Senin',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
        ]);
        $learningClass->schedules()->create([
            'day' => 'senin',
            'lesson_time' => '09:30',
            'duration_minutes' => 60,
        ]);

        return $learningClass;
    }
}
