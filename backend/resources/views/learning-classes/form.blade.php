@extends('layouts.dashboard')

@php($isEditing = $learningClass->exists)
@section('title', ($isEditing ? 'Ubah' : 'Tambah').' Kelas')
@section('breadcrumb', 'Kelas / '.($isEditing ? 'Ubah' : 'Tambah'))
@section('scripts')
    @vite('resources/js/class-schedules.js')
@endsection

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN</p>
            <h1>{{ $isEditing ? 'Ubah Kelas' : 'Tambah Kelas' }}</h1>
            <p>Lengkapi data dan jadwal kelas.</p>
        </div>
        @if (auth()->user()->hasPermission('learning_classes.view'))
            <a class="secondary-action" href="{{ route('learning-classes.index') }}">Kembali</a>
        @endif
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" action="{{ $isEditing ? route('learning-classes.update', $learningClass) : route('learning-classes.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="master-data-field">
                <label for="name">Nama kelas</label>
                <input id="name" name="name" type="text" value="{{ old('name', $learningClass->name) }}" required maxlength="255">
                @error('name')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            <div class="master-data-field">
                <label for="student_id">Nama murid</label>
                <select id="student_id" name="student_id" required>
                    <option value="">Pilih murid</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((string) old('student_id', $learningClass->student_id) === (string) $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                @if ($students->isEmpty())
                    <small>Belum ada akun dengan role murid. Tambahkan murid melalui menu Master Data.</small>
                @endif
                @error('student_id')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            <div class="master-data-field">
                <label for="teacher_id">Nama guru</label>
                <select id="teacher_id" name="teacher_id" required>
                    <option value="">Pilih guru</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $learningClass->teacher_id) === (string) $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
                @if ($teachers->isEmpty())
                    <small>Belum ada akun dengan role guru. Tambahkan guru melalui menu Master Data.</small>
                @endif
                @error('teacher_id')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            <div class="master-data-field">
                <label for="program_id">Program</label>
                <select id="program_id" name="program_id" required>
                    <option value="">Pilih program</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected((string) old('program_id', $learningClass->program_id) === (string) $program->id)>{{ $program->value }}</option>
                    @endforeach
                </select>
                @if ($programs->isEmpty())
                    <small>Belum ada pilihan program. Tambahkan melalui menu Pengaturan.</small>
                @endif
                @error('program_id')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            <div class="master-data-field">
                <label for="category_id">Kategori</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $learningClass->category_id) === (string) $category->id)>{{ $category->value }}</option>
                    @endforeach
                </select>
                @if ($categories->isEmpty())
                    <small>Belum ada pilihan kategori kelas.</small>
                @endif
                @error('category_id')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            <div class="master-data-field">
                <div class="schedule-heading">
                    <div>
                        <label>Jadwal kelas</label>
                        <small>Tambahkan satu atau lebih jadwal.</small>
                    </div>
                    <button class="secondary-action schedule-add-button" id="add-schedule" type="button">+ Tambah jadwal</button>
                </div>
                <div class="schedule-rows" id="schedule-rows">
                    @foreach ($scheduleRows as $index => $schedule)
                        <div class="schedule-row">
                            <div class="schedule-input">
                                <label for="schedules-{{ $index }}-day">Hari</label>
                                <select id="schedules-{{ $index }}-day" data-field="day" name="schedules[{{ $index }}][day]" required>
                                    <option value="">Pilih hari</option>
                                    @foreach ($days as $dayValue => $dayLabel)
                                        <option value="{{ $dayValue }}" @selected(($schedule['day'] ?? '') === $dayValue)>{{ $dayLabel }}</option>
                                    @endforeach
                                </select>
                                @error("schedules.$index.day")<p class="master-data-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="schedule-input">
                                <label for="schedules-{{ $index }}-lesson-time">Jam les</label>
                                <input id="schedules-{{ $index }}-lesson-time" data-field="lesson_time" name="schedules[{{ $index }}][lesson_time]" type="time" value="{{ $schedule['lesson_time'] ?? '' }}" required>
                                @error("schedules.$index.lesson_time")<p class="master-data-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="schedule-input">
                                <label for="schedules-{{ $index }}-duration">Durasi (menit)</label>
                                <input id="schedules-{{ $index }}-duration" data-field="duration_minutes" name="schedules[{{ $index }}][duration_minutes]" type="number" value="{{ $schedule['duration_minutes'] ?? '' }}" required min="1" max="1440" step="1">
                                @error("schedules.$index.duration_minutes")<p class="master-data-error">{{ $message }}</p>@enderror
                            </div>
                            <button class="schedule-remove-button" type="button" aria-label="Hapus jadwal">Hapus</button>
                        </div>
                    @endforeach
                </div>
                @error('schedules')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            <div class="master-data-form-actions">
                @if (auth()->user()->hasPermission('learning_classes.view'))
                    <a class="secondary-action" href="{{ route('learning-classes.index') }}">Batal</a>
                @endif
                <button class="primary-action" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan kelas' }}</button>
            </div>
        </form>
    </section>

    <template id="schedule-row-template">
        <div class="schedule-row">
            <div class="schedule-input">
                <label>Hari</label>
                <select data-field="day" required>
                    <option value="">Pilih hari</option>
                    @foreach ($days as $dayValue => $dayLabel)
                        <option value="{{ $dayValue }}">{{ $dayLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="schedule-input">
                <label>Jam les</label>
                <input data-field="lesson_time" type="time" required>
            </div>
            <div class="schedule-input">
                <label>Durasi (menit)</label>
                <input data-field="duration_minutes" type="number" required min="1" max="1440" step="1">
            </div>
            <button class="schedule-remove-button" type="button" aria-label="Hapus jadwal">Hapus</button>
        </div>
    </template>
@endsection
