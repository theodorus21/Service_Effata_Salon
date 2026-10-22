<?php

namespace Tests\Feature;

use App\Models\LearningClass;
use App\Models\LearningClassLessonStatus;
use App\Models\LookupValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class LearningClassScheduleTest extends TestCase
{
    use RefreshDatabase;
    use AuthenticatesAsMasterAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    public function test_schedule_menu_lists_classes_and_filters_by_teacher_student_month_and_day(): void
    {
        [$teacher, $student, $learningClass] = $this->createClassWithSchedule(
            'Kelas Senin',
            'Guru Satu',
            'Murid Satu',
            'senin',
        );
        $this->createClassWithSchedule('Kelas Selasa', 'Guru Dua', 'Murid Dua', 'selasa');

        $this->get(route('class-schedules.index', [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'month' => '2026-10',
            'day' => 'senin',
        ]))
            ->assertOk()
            ->assertSee(route('class-schedules.index'), false)
            ->assertSee('Kelas Senin')
            ->assertSee('Guru Satu')
            ->assertSee('Murid Satu')
            ->assertSee('Oktober 2026')
            ->assertDontSee('Kelas Selasa')
            ->assertSee('month=10&amp;year=2026', false);
    }

    public function test_class_schedule_detail_lists_generated_lesson_records_filtered_by_month_and_year(): void
    {
        [, , $learningClass] = $this->createClassWithSchedule(
            'Kelas Daftar',
            'Guru Daftar',
            'Murid Daftar',
            'senin',
        );
        $learningClass->lessons()->create([
            'lesson_date' => '2026-10-05',
            'lesson_time' => '09:30',
            'status' => LearningClassLessonStatus::query()
                ->where('system_key', 'sedang_berlangsung')
                ->firstOrFail()
                ->code,
            'teacher_attendance_at' => '2026-10-05 09:25:00',
            'lesson_description' => 'Belajar membaca.',
            'notes' => 'Murid hadir.',
        ]);
        $learningClass->lessons()->create([
            'lesson_date' => '2026-11-02',
            'lesson_time' => '09:30',
            'lesson_description' => 'Pelajaran bulan lain.',
        ]);

        $this->get(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertSee('Oktober 2026')
            ->assertSee('Tanggal les')
            ->assertSee('Durasi')
            ->assertSee('60 menit')
            ->assertSee('Sedang Berlangsung')
            ->assertSee('lesson-status-badge', false)
            ->assertSee('Detail')
            ->assertDontSee('Belajar membaca.')
            ->assertDontSee('Murid hadir.')
            ->assertDontSee('Pelajaran bulan lain.');

        $lesson = $learningClass->lessons()->whereDate('lesson_date', '2026-10-05')->firstOrFail();
        $this->get(route('class-schedules.lessons.show', [
            'learningClass' => $learningClass,
            'lesson' => $lesson,
        ]))
            ->assertOk()
            ->assertSee('Belajar membaca.')
            ->assertSee('Murid hadir.')
            ->assertSee('09:25');

        $this->get(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 11,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertDontSee('Pelajaran bulan lain.')
            ->assertDontSee('Belajar membaca.');

        $this->get(route('class-schedules.lessons.show', [
            'learningClass' => $learningClass,
            'lesson' => $learningClass->lessons()->whereDate('lesson_date', '2026-11-02')->firstOrFail(),
        ]))
            ->assertOk()
            ->assertSee('Pelajaran bulan lain.');
    }

    /**
     * @return array{User, User, LearningClass}
     */
    private function createClassWithSchedule(string $className, string $teacherName, string $studentName, string $day): array
    {
        $teacher = User::factory()->create(['name' => $teacherName, 'role' => 'guru']);
        $student = User::factory()->create(['name' => $studentName, 'role' => 'murid']);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();
        $category = LookupValue::query()->where('category', 'class_category')->firstOrFail();
        $learningClass = LearningClass::query()->create([
            'name' => $className,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
        ]);
        $learningClass->schedules()->create([
            'day' => $day,
            'lesson_time' => '09:30',
            'duration_minutes' => 60,
        ]);

        return [$teacher, $student, $learningClass];
    }
}
