<?php

namespace Tests\Feature;

use App\Models\LearningClass;
use App\Models\LearningClassLessonStatus;
use App\Models\LookupValue;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class LearningClassScheduleTest extends TestCase
{
    use AuthenticatesAsMasterAdmin;
    use RefreshDatabase;

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
            ->assertSee('name="teacher_id"', false)
            ->assertSee('name="student_id"', false)
            ->assertSee(route('class-schedules.index'), false)
            ->assertSee('Kelas Senin')
            ->assertSee('Guru Satu')
            ->assertDontSee('<th>Nama guru</th>', false)
            ->assertDontSee('<td>Guru Satu</td>', false)
            ->assertSee('Murid Satu')
            ->assertSee('Oktober 2026')
            ->assertDontSee('Kelas Selasa')
            ->assertSee('month=10&amp;year=2026', false);
    }

    public function test_teacher_only_sees_classes_with_lessons_assigned_to_them_in_selected_month(): void
    {
        [$teacher] = $this->createClassWithSchedule('Kelas Guru Tetap', 'Guru Login', 'Murid Tetap', 'senin');
        [, , $movedAwayClass] = $this->createClassWithSchedule('Kelas Guru Berubah', 'Guru Login', 'Murid Pindah', 'senin');
        [$otherTeacher, , $movedInClass] = $this->createClassWithSchedule('Kelas Guru Pengganti', 'Guru Lain', 'Murid Pengganti', 'senin');
        [, , $notGeneratedClass] = $this->createClassWithSchedule('Kelas Belum Generate', 'Guru Login', 'Murid Baru', 'senin');
        $movedAwayClass->update(['teacher_id' => $teacher->id]);
        $notGeneratedClass->update(['teacher_id' => $teacher->id]);

        $movedAwayClass->lessons()->create([
            'teacher_id' => $otherTeacher->id,
            'lesson_date' => '2026-10-05',
            'lesson_time' => '09:30',
        ]);
        $movedInClass->lessons()->create([
            'teacher_id' => $teacher->id,
            'lesson_date' => '2026-10-05',
            'lesson_time' => '09:30',
        ]);
        $movedAwayClass->lessons()->create([
            'teacher_id' => $otherTeacher->id,
            'lesson_date' => '2026-10-12',
            'lesson_time' => '09:30',
        ]);

        Role::query()->where('code', 'guru')->firstOrFail()->permissions()
            ->syncWithoutDetaching(['class_schedules.view']);
        $this->assertTrue($teacher->fresh()->hasPermission('class_schedules.view'));
        $this->actingAs($teacher);

        $response = $this->get(route('class-schedules.index', [
            'month' => '2026-10',
            'teacher_id' => $otherTeacher->id,
        ]))
            ->assertOk()
            ->assertDontSee('name="teacher_id"', false)
            ->assertSee('name="student_id"', false)
            ->assertSee('Murid Tetap')
            ->assertSee('Murid Pindah')
            ->assertSee('Murid Baru')
            ->assertSee('Murid Pengganti')
            ->assertDontSee('Murid Lain');

        $this->assertEqualsCanonicalizing(
            ['Kelas Guru Tetap', 'Kelas Guru Pengganti', 'Kelas Belum Generate'],
            $response->viewData('classes')->getCollection()->pluck('name')->all(),
        );
    }

    public function test_teachers_and_students_only_see_their_own_schedule_classes_and_sessions(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01 00:00:00'));
        [$teacher, $student, $learningClass] = $this->createClassWithSchedule(
            'Kelas Sesi Terbagi',
            'Guru Pemilik',
            'Murid Pemilik',
            'senin',
        );
        [$otherTeacher, $otherStudent, $otherClass] = $this->createClassWithSchedule(
            'Kelas Lain',
            'Guru Lain',
            'Murid Lain',
            'senin',
        );
        $teacherLesson = $learningClass->lessons()->create([
            'teacher_id' => $teacher->id,
            'lesson_date' => '2026-10-05',
            'lesson_time' => '09:30',
        ]);
        $otherTeacherLesson = $learningClass->lessons()->create([
            'teacher_id' => $otherTeacher->id,
            'lesson_date' => '2026-10-12',
            'lesson_time' => '09:30',
        ]);
        $otherClass->lessons()->create([
            'teacher_id' => $otherTeacher->id,
            'lesson_date' => '2026-10-05',
            'lesson_time' => '10:30',
        ]);

        Role::query()->whereIn('code', ['guru', 'murid'])->get()->each(
            fn (Role $role) => $role->permissions()->syncWithoutDetaching(['class_schedules.view']),
        );

        $teacherResponse = $this->actingAs($teacher)->get(route('class-schedules.index', ['month' => '2026-10']))
            ->assertOk();
        $this->assertEquals([$learningClass->id], $teacherResponse->viewData('classes')->getCollection()->pluck('id')->all());

        $teacherSchedule = $this->get(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]))->assertOk();
        $this->assertEquals(
            [$teacherLesson->id],
            $teacherSchedule->viewData('lessons')->getCollection()->pluck('id')->all(),
        );
        $this->get(route('class-schedules.show', ['learningClass' => $otherClass, 'month' => 10, 'year' => 2026]))
            ->assertNotFound();
        $this->get(route('class-schedules.lessons.show', [
            'learningClass' => $learningClass,
            'lesson' => $otherTeacherLesson,
        ]))->assertNotFound();

        $studentResponse = $this->actingAs($student)->get(route('class-schedules.index', ['month' => '2026-10']))
            ->assertOk()
            ->assertDontSee('name="teacher_id"', false)
            ->assertDontSee('name="student_id"', false);
        $this->assertEquals([$learningClass->id], $studentResponse->viewData('classes')->getCollection()->pluck('id')->all());
        $studentSchedule = $this->get(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]))->assertOk();
        $this->assertEqualsCanonicalizing(
            [$teacherLesson->id, $otherTeacherLesson->id],
            $studentSchedule->viewData('lessons')->getCollection()->pluck('id')->all(),
        );
        $this->get(route('class-schedules.show', ['learningClass' => $otherClass, 'month' => 10, 'year' => 2026]))
            ->assertNotFound();
        $this->get(route('class-schedules.lessons.show', [
            'learningClass' => $otherClass,
            'lesson' => $otherClass->lessons()->firstOrFail(),
        ]))->assertNotFound();
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
        $replacementTeacher = User::factory()->create(['name' => 'Guru Pengganti', 'role' => 'guru']);
        $learningClass->lessons()->create([
            'teacher_id' => $replacementTeacher->id,
            'lesson_date' => '2026-11-02',
            'lesson_time' => '09:30',
            'lesson_description' => 'Pelajaran bulan lain.',
        ]);
        $learningClass->lessons()->create([
            'teacher_id' => $replacementTeacher->id,
            'lesson_date' => '2026-10-12',
            'lesson_time' => '09:30',
        ]);

        $this->get(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertSee('Oktober 2026')
            ->assertSee('Tanggal les')
            ->assertSee('Guru')
            ->assertSee('Guru Daftar')
            ->assertSee('Guru Pengganti')
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
            ->assertSee('Guru Pengganti')
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
            'product_id' => \Tests\Support\ProductFixture::make($program->id, $category->id)->id,
        ]);
        $learningClass->schedules()->create([
            'day' => $day,
            'lesson_time' => '09:30',
            'duration_minutes' => 60,
            'teacher_id' => $learningClass->teacher_id,
        ]);

        return [$teacher, $student, $learningClass];
    }
}
