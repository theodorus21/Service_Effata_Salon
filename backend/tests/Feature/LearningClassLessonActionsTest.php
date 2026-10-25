<?php

namespace Tests\Feature;

use App\Models\LearningClass;
use App\Models\LearningClassLesson;
use App\Models\LearningClassLessonRescheduleHistory;
use App\Models\LearningClassLessonStatus;
use App\Models\LookupValue;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class LearningClassLessonActionsTest extends TestCase
{
    use AuthenticatesAsMasterAdmin;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    public function test_teacher_attendance_sets_attendance_time_and_in_progress_status(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $status = LearningClassLessonStatus::query()->where('system_key', 'sedang_berlangsung')->firstOrFail();

        $this->get(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertSee('Absen guru')
            ->assertSee('Pindah jadwal')
            ->assertSee('Cancel kelas')
            ->assertSee('lesson-cancel-dialog', false)
            ->assertSee('Detail')
            ->assertSee('Absen guru')
            ->assertDontSee('Absen guru</th>')
            ->assertDontSee('Foto pelajaran</th>')
            ->assertDontSee('Deskripsi pelajaran</th>')
            ->assertDontSee('Notes</th>')
            ->assertSee(route('class-schedules.lessons.show', [
                'learningClass' => $learningClass,
                'lesson' => $lesson,
            ]), false);

        $this->get(route('class-schedules.lessons.show', [
            'learningClass' => $learningClass,
            'lesson' => $lesson,
        ]))
            ->assertOk()
            ->assertSee('Detail Jadwal Les')
            ->assertSee('>-</dd>', false)
            ->assertDontSee('Belum ada foto pelajaran.')
            ->assertDontSee('Belum ada deskripsi pelajaran.')
            ->assertDontSee('Belum ada notes.')
            ->assertDontSee('Riwayat Pindah Jadwal')
            ->assertDontSee('<details class="lesson-history-section">', false);

        $this->post($this->actionRoute('attendance', $learningClass, $lesson))
            ->assertRedirect(route('class-schedules.show', [
                'learningClass' => $learningClass,
                'month' => 10,
                'year' => 2026,
            ]))
            ->assertSessionHas('status');

        $lesson->refresh();
        $this->assertNotNull($lesson->teacher_attendance_at);
        $this->assertSame($status->code, $lesson->status);

        $this->get(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertSee('Laporan Kelas')
            ->assertDontSee('Pindah jadwal')
            ->assertDontSee('Cancel kelas');
    }

    public function test_class_report_requires_photo_and_description(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $lesson->update([
            'teacher_attendance_at' => now(),
            'status' => LearningClassLessonStatus::query()->where('system_key', 'sedang_berlangsung')->value('code'),
        ]);

        $this->get($this->actionRoute('report.edit', $learningClass, $lesson))
            ->assertOk()
            ->assertSee('Laporan Kelas')
            ->assertSee('Foto pelajaran')
            ->assertSee('Deskripsi pelajaran');

        $this->from($this->actionRoute('report.edit', $learningClass, $lesson))
            ->post($this->actionRoute('report.update', $learningClass, $lesson), [
                'notes' => 'Catatan tambahan.',
            ])
            ->assertRedirect($this->actionRoute('report.edit', $learningClass, $lesson))
            ->assertSessionHasErrors(['lesson_photo', 'lesson_description']);

        $this->assertSame(
            LearningClassLessonStatus::query()->where('system_key', 'sedang_berlangsung')->value('code'),
            $lesson->refresh()->status,
        );
    }

    public function test_submitting_class_report_saves_photo_and_changes_status_to_completed(): void
    {
        Storage::fake('local');
        [$learningClass, $lesson] = $this->createLesson();
        $lesson->update([
            'teacher_attendance_at' => now(),
            'status' => LearningClassLessonStatus::query()->where('system_key', 'sedang_berlangsung')->value('code'),
        ]);
        $this->post($this->actionRoute('report.update', $learningClass, $lesson), [
            'lesson_photo' => UploadedFile::fake()->image('lesson.jpg'),
            'lesson_description' => 'Belajar membaca dan memahami teks.',
        ])->assertRedirect(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]));

        $lesson->refresh();
        $completedStatus = LearningClassLessonStatus::query()->where('system_key', 'terlaksana')->firstOrFail();
        $this->assertSame($completedStatus->code, $lesson->status);
        $this->assertSame('Belajar membaca dan memahami teks.', $lesson->lesson_description);
        $this->assertNull($lesson->notes);
        $this->assertNotEmpty($lesson->lesson_photo);
        Storage::disk('local')->assertExists($lesson->lesson_photo);

        $this->get($this->actionRoute('photo', $learningClass, $lesson))->assertOk();
        $this->get(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertSee('Detail')
            ->assertDontSee('Absen guru')
            ->assertDontSee('Laporan Kelas')
            ->assertDontSee('Pindah jadwal')
            ->assertDontSee('Cancel kelas');
    }

    public function test_report_cannot_be_opened_or_submitted_for_a_lesson_not_in_progress(): void
    {
        [$learningClass, $lesson] = $this->createLesson();

        $this->get($this->actionRoute('report.edit', $learningClass, $lesson))->assertUnprocessable();
        $this->post($this->actionRoute('report.update', $learningClass, $lesson), [
            'lesson_photo' => UploadedFile::fake()->image('lesson.jpg'),
            'lesson_description' => 'Deskripsi.',
        ])->assertUnprocessable();

        $this->assertNull($lesson->refresh()->lesson_photo);
    }

    public function test_reschedule_and_cancel_are_rejected_after_class_starts(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $lesson->update([
            'teacher_attendance_at' => now(),
            'status' => LearningClassLessonStatus::query()->where('system_key', 'sedang_berlangsung')->value('code'),
        ]);

        $this->get($this->actionRoute('reschedule.edit', $learningClass, $lesson))->assertUnprocessable();
        $this->post($this->actionRoute('cancel', $learningClass, $lesson), [
            'reason' => 'Alasan pembatalan.',
        ])->assertUnprocessable();
    }

    public function test_rescheduling_updates_lesson_and_records_previous_and_new_times_with_actor(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $actor = User::factory()->create(['role' => 'admin']);
        Role::query()->where('code', 'admin')->firstOrFail()->permissions()
            ->syncWithoutDetaching(['class_schedules.view', 'class_schedules.reschedule']);
        $this->assertTrue($actor->hasPermission('class_schedules.reschedule'));
        $this->actingAs($actor);
        $this->assertSame($actor->id, auth()->id());
        $this->assertTrue(auth()->user()->hasPermission('class_schedules.reschedule'));

        $this->get($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->assertOk()
            ->assertSee('Pindah Jadwal Les')
            ->assertSee('2026-10-05')
            ->assertSee('Durasi les (menit)')
            ->assertSee('name="teacher_id"', false)
            ->assertSee('name="reason"', false)
            ->assertSee('value="60"', false);

        $this->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
            'lesson_date' => '2026-10-12',
            'lesson_time' => '11:15',
            'duration_minutes' => 75,
            'teacher_id' => $learningClass->teacher_id,
            'reason' => 'Permintaan penyesuaian jadwal.',
        ])->assertRedirect(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]));

        $lesson->refresh();
        $this->assertSame('2026-10-12', $lesson->lesson_date);
        $this->assertSame('11:15', substr($lesson->lesson_time, 0, 5));
        $this->assertSame(75, $lesson->duration_minutes);
        $this->assertDatabaseHas('learning_class_lesson_reschedule_histories', [
            'learning_class_lesson_id' => $lesson->id,
            'old_lesson_date' => '2026-10-05',
            'old_lesson_time' => '09:30',
            'new_lesson_date' => '2026-10-12',
            'new_lesson_time' => '11:15',
            'reason' => 'Permintaan penyesuaian jadwal.',
            'old_teacher_id' => $learningClass->teacher_id,
            'new_teacher_id' => $learningClass->teacher_id,
            'updated_by' => $actor->id,
        ]);
        $this->assertInstanceOf(LearningClassLessonRescheduleHistory::class, $lesson->rescheduleHistories()->first());

        $this->get($this->actionRoute('show', $learningClass, $lesson))
            ->assertOk()
            ->assertSee('<details class="lesson-history-section">', false)
            ->assertSee('Jadwal sebelumnya')
            ->assertSee('Jadwal baru')
            ->assertSee('09:30')
            ->assertSee('11:15')
            ->assertSee('Permintaan penyesuaian jadwal.')
            ->assertSee($learningClass->teacher->name);
    }

    public function test_reschedule_rejects_duplicate_session_datetime_for_same_class(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $otherLesson = $learningClass->lessons()->create([
            'lesson_date' => '2026-10-12',
            'lesson_time' => '11:15',
        ]);

        $this->from($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
                'lesson_date' => '2026-10-12',
                'lesson_time' => '11:15',
                'duration_minutes' => 60,
                'teacher_id' => $learningClass->teacher_id,
                'reason' => 'Jadwal bentrok.',
            ])
            ->assertRedirect($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->assertSessionHasErrors('lesson_time');

        $this->assertDatabaseMissing('learning_class_lesson_reschedule_histories', [
            'learning_class_lesson_id' => $lesson->id,
        ]);
        $this->assertSame('2026-10-12', $otherLesson->refresh()->lesson_date);
    }

    public function test_reschedule_can_change_teacher_for_one_lesson_and_records_teacher_history(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $oldTeacher = $learningClass->teacher;
        $newTeacher = User::factory()->create(['role' => 'guru', 'name' => 'Guru Pengganti']);

        $this->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
            'lesson_date' => '2026-10-12',
            'lesson_time' => '11:15',
            'duration_minutes' => 75,
            'teacher_id' => $newTeacher->id,
            'reason' => 'Guru lama berhalangan.',
        ])->assertRedirect(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]));

        $this->assertSame($newTeacher->id, $lesson->refresh()->teacher_id);
        $this->assertSame($oldTeacher->id, $learningClass->refresh()->teacher_id);
        $this->assertDatabaseHas('learning_class_lesson_reschedule_histories', [
            'learning_class_lesson_id' => $lesson->id,
            'reason' => 'Guru lama berhalangan.',
            'old_teacher_id' => $oldTeacher->id,
            'new_teacher_id' => $newTeacher->id,
        ]);

        $this->get($this->actionRoute('show', $learningClass, $lesson))
            ->assertOk()
            ->assertSee('Guru Pengganti')
            ->assertSee($oldTeacher->name.' → Guru Pengganti')
            ->assertSee('Guru lama berhalangan.');
    }

    public function test_reschedule_checks_conflicts_for_the_selected_replacement_teacher(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $replacementTeacher = User::factory()->create(['role' => 'guru']);
        $otherClass = $this->createClass(['teacher_id' => $replacementTeacher->id]);
        $otherClass->lessons()->create([
            'lesson_date' => '2026-10-12',
            'lesson_time' => '10:00',
            'duration_minutes' => 60,
        ]);

        $this->from($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
                'lesson_date' => '2026-10-12',
                'lesson_time' => '09:30',
                'duration_minutes' => 60,
                'teacher_id' => $replacementTeacher->id,
                'reason' => 'Mengganti guru.',
            ])
            ->assertRedirect($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->assertSessionHasErrors('lesson_time');

        $this->assertDatabaseMissing('learning_class_lesson_reschedule_histories', [
            'learning_class_lesson_id' => $lesson->id,
        ]);

        $this->from($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
                'lesson_date' => '2026-10-13',
                'lesson_time' => '09:30',
                'duration_minutes' => 60,
                'teacher_id' => $learningClass->student_id,
                'reason' => 'Guru tidak valid.',
            ])
            ->assertRedirect($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->assertSessionHasErrors('teacher_id');
    }

    public function test_reschedule_rejects_time_range_overlap_with_shared_teacher_or_student(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $otherClass = $this->createClass([
            'teacher_id' => $learningClass->teacher_id,
        ]);
        $otherClass->lessons()->create([
            'lesson_date' => '2026-10-12',
            'lesson_time' => '10:00',
            'duration_minutes' => 60,
        ]);

        $this->from($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
                'lesson_date' => '2026-10-12',
                'lesson_time' => '09:30',
                'duration_minutes' => 60,
                'teacher_id' => $learningClass->teacher_id,
                'reason' => 'Jadwal bentrok.',
            ])
            ->assertRedirect($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->assertSessionHasErrors('lesson_time');

        $this->assertDatabaseMissing('learning_class_lesson_reschedule_histories', [
            'learning_class_lesson_id' => $lesson->id,
        ]);
        $this->assertSame('2026-10-05', $lesson->refresh()->lesson_date);
    }

    public function test_longer_duration_is_rejected_when_it_overlaps_another_session(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $otherClass = $this->createClass([
            'teacher_id' => $learningClass->teacher_id,
        ]);
        $otherClass->lessons()->create([
            'lesson_date' => '2026-10-12',
            'lesson_time' => '10:00',
            'duration_minutes' => 60,
        ]);

        $this->from($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
                'lesson_date' => '2026-10-12',
                'lesson_time' => '09:00',
                'duration_minutes' => 75,
                'teacher_id' => $learningClass->teacher_id,
                'reason' => 'Jadwal bentrok.',
            ])
            ->assertRedirect($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->assertSessionHasErrors('lesson_time');

        $this->assertSame('2026-10-05', $lesson->refresh()->lesson_date);
    }

    public function test_reschedule_allows_adjacent_sessions_and_requires_valid_duration(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $otherClass = $this->createClass([
            'student_id' => $learningClass->student_id,
        ]);
        $otherClass->lessons()->create([
            'lesson_date' => '2026-10-12',
            'lesson_time' => '10:30',
            'duration_minutes' => 60,
        ]);

        $this->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
            'lesson_date' => '2026-10-12',
            'lesson_time' => '09:30',
            'duration_minutes' => 60,
            'teacher_id' => $learningClass->teacher_id,
            'reason' => 'Jadwal berdekatan.',
        ])->assertRedirect(route('class-schedules.show', [
            'learningClass' => $learningClass,
            'month' => 10,
            'year' => 2026,
        ]));

        $this->assertSame(60, $lesson->refresh()->duration_minutes);

        $this->from($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->put($this->actionRoute('reschedule.update', $learningClass, $lesson), [
                'lesson_date' => '2026-10-13',
                'lesson_time' => '09:30',
                'duration_minutes' => 0,
                'teacher_id' => $learningClass->teacher_id,
                'reason' => 'Durasi tidak valid.',
            ])
            ->assertRedirect($this->actionRoute('reschedule.edit', $learningClass, $lesson))
            ->assertSessionHasErrors('duration_minutes');
    }

    public function test_cancellation_saves_reason_separately_and_shows_it_in_detail(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $cancelledStatus = LearningClassLessonStatus::query()->where('system_key', 'dibatalkan')->firstOrFail();

        $this->post($this->actionRoute('cancel', $learningClass, $lesson), [
            'reason' => 'Guru berhalangan hadir.',
        ])
            ->assertRedirect(route('class-schedules.show', [
                'learningClass' => $learningClass,
                'month' => 10,
                'year' => 2026,
            ]))
            ->assertSessionHas('status');

        $lesson->refresh();
        $this->assertSame('Guru berhalangan hadir.', $lesson->reason);
        $this->assertNull($lesson->lesson_description);
        $this->assertSame($cancelledStatus->code, $lesson->status);
        $this->get($this->actionRoute('show', $learningClass, $lesson))
            ->assertOk()
            ->assertSee('Alasan pembatalan')
            ->assertSee('Guru berhalangan hadir.');
    }

    public function test_actions_reject_a_lesson_that_does_not_belong_to_the_class(): void
    {
        [$learningClass, $lesson] = $this->createLesson();
        $otherClass = $this->createClass();

        $this->post($this->actionRoute('attendance', $otherClass, $lesson))->assertNotFound();

        $lesson->refresh();
        $this->assertNull($lesson->teacher_attendance_at);
    }

    /**
     * @return array{LearningClass, LearningClassLesson}
     */
    private function createLesson(): array
    {
        $learningClass = $this->createClass();
        $lesson = $learningClass->lessons()->create([
            'lesson_date' => '2026-10-05',
            'lesson_time' => '09:30',
            'duration_minutes' => 60,
        ]);

        return [$learningClass, $lesson];
    }

    private function createClass(array $overrides = []): LearningClass
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'murid']);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();
        $category = LookupValue::query()->where('category', 'class_category')->firstOrFail();

        return LearningClass::query()->create([
            'name' => 'Kelas Aksi',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
            ...$overrides,
        ]);
    }

    private function actionRoute(string $action, LearningClass $learningClass, LearningClassLesson $lesson): string
    {
        return route('class-schedules.lessons.'.$action, [
            'learningClass' => $learningClass,
            'lesson' => $lesson,
        ]);
    }
}
