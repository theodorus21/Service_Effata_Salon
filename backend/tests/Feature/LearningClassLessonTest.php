<?php

namespace Tests\Feature;

use App\Models\LearningClass;
use App\Models\LearningClassLesson;
use App\Models\LookupValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class LearningClassLessonTest extends TestCase
{
    use AuthenticatesAsMasterAdmin;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    public function test_class_list_can_generate_monthly_lesson_records_from_weekly_schedules(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01 00:00:00'));
        $learningClass = $this->createMondayClass();

        $this->get(route('learning-classes.index', ['month' => '2026-10']))
            ->assertOk()
            ->assertSee('value="2026-10"', false)
            ->assertSee(route('learning-classes.lessons.generate.form', $learningClass), false)
            ->assertSee('Generate jadwal');

        $this->get(route('learning-classes.lessons.generate.form', $learningClass))
            ->assertOk()
            ->assertSee('Generate Jadwal Les')
            ->assertSee('name="month"', false)
            ->assertSee('name="year"', false);

        $this->post(route('learning-classes.lessons.generate', $learningClass), [
            'month' => 10,
            'year' => 2026,
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
        $this->travelTo(Carbon::parse('2026-10-01 00:00:00'));
        $learningClass = $this->createMondayClass();
        $route = route('learning-classes.lessons.generate', $learningClass);

        $this->post($route, ['month' => 10, 'year' => 2026])->assertRedirect();
        $lesson = LearningClassLesson::query()->firstOrFail();
        $lesson->update([
            'teacher_attendance_at' => '2026-10-05 09:20:00',
            'lesson_description' => 'Catatan sesi sudah berlangsung.',
        ]);

        $this->post($route, ['month' => 10, 'year' => 2026])
            ->assertRedirect(route('learning-classes.index', ['month' => '2026-10']))
            ->assertSessionHas('status', fn (string $status): bool => str_starts_with($status, '0 jadwal les'));

        $this->assertDatabaseCount('learning_class_lessons', 4);
        $this->assertDatabaseHas('learning_class_lessons', [
            'id' => $lesson->id,
            'teacher_attendance_at' => '2026-10-05 09:20:00',
            'lesson_description' => 'Catatan sesi sudah berlangsung.',
        ]);
    }

    public function test_generation_only_creates_sessions_after_today(): void
    {
        $this->travelTo(Carbon::parse('2026-10-19 09:30:00'));
        $learningClass = $this->createMondayClass();

        $this->post(route('learning-classes.lessons.generate', $learningClass), [
            'month' => 10,
            'year' => 2026,
        ])->assertRedirect();

        $this->assertDatabaseCount('learning_class_lessons', 1);
        $this->assertDatabaseHas('learning_class_lessons', [
            'learning_class_id' => $learningClass->id,
            'lesson_date' => '2026-10-26',
        ]);
        $this->assertDatabaseMissing('learning_class_lessons', [
            'learning_class_id' => $learningClass->id,
            'lesson_date' => '2026-10-19',
        ]);
    }

    public function test_generation_requires_a_valid_month(): void
    {
        $learningClass = $this->createMondayClass();

        $this->from(route('learning-classes.lessons.generate.form', $learningClass))
            ->post(route('learning-classes.lessons.generate', $learningClass), ['month' => 13, 'year' => 2026])
            ->assertRedirect(route('learning-classes.lessons.generate.form', $learningClass))
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
