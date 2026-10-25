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
                <label for="product_id">Produk</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Pilih produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-monthly="{{ $product->frequency?->is_monthly ? '1' : '0' }}" data-duration="{{ $product->duration_minutes }}" @selected((string) old('product_id', $learningClass->product_id) === (string) $product->id)>{{ $product->name }} ({{ $product->code }})</option>
                    @endforeach
                </select>
                <small>Nama kelas mengikuti nama produk.</small>
                <small id="duration-info"></small>
                @if ($products->isEmpty())
                    <small>Belum ada produk aktif. Tambahkan melalui menu Produk.</small>
                @endif
                @error('product_id')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            @if ($isEditing)
            <div class="master-data-field">
                <label for="price_display">Harga</label>
                <input id="price_display" type="text" value="Rp {{ number_format($learningClass->price, 0, ',', '.') }}" readonly>
            </div>
            @endif

            @if ($learningClass->exists)
            <div class="master-data-field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'complete' => 'Complete'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $learningClass->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            @endif

            <p id="section-placeholder" class="master-data-field"><small>Pilih produk terlebih dahulu untuk menentukan jadwal kelas.</small></p>

            <div class="master-data-field" data-section="monthly" hidden>
                <div class="schedule-heading">
                    <div>
                        <label>Jadwal kelas</label>
                        <small>Produk bulanan: jadwal mingguan ini di-generate menjadi pertemuan setiap bulan.</small>
                    </div>
                    <button class="secondary-action schedule-add-button" id="add-schedule" type="button">+ Tambah jadwal</button>
                </div>
                <div class="schedule-rows" id="schedule-rows" data-max="20">
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
                            <button class="schedule-remove-button" type="button" aria-label="Hapus jadwal">Hapus</button>
                        </div>
                    @endforeach
                </div>
                @error('schedules')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            <div class="master-data-field" data-section="dated" hidden>
                <div class="schedule-heading">
                    <div>
                        <label>Tanggal kelas</label>
                        <small>Pilih tanggal dan jam les. Tambahkan lebih dari satu tanggal bila diperlukan.</small>
                    </div>
                    <button class="secondary-action schedule-add-button" id="add-lesson" type="button">+ Tambah tanggal</button>
                </div>
                <div class="schedule-rows" id="lesson-rows" data-max="50">
                    @foreach ($lessonRows as $index => $lesson)
                        <div class="schedule-row">
                            <div class="schedule-input">
                                <label for="lessons-{{ $index }}-lesson-date">Tanggal</label>
                                <input id="lessons-{{ $index }}-lesson-date" data-field="lesson_date" name="lessons[{{ $index }}][lesson_date]" type="date" value="{{ $lesson['lesson_date'] ?? '' }}" required>
                                @error("lessons.$index.lesson_date")<p class="master-data-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="schedule-input">
                                <label for="lessons-{{ $index }}-lesson-time">Jam les</label>
                                <input id="lessons-{{ $index }}-lesson-time" data-field="lesson_time" name="lessons[{{ $index }}][lesson_time]" type="time" value="{{ $lesson['lesson_time'] ?? '' }}" required>
                                @error("lessons.$index.lesson_time")<p class="master-data-error">{{ $message }}</p>@enderror
                            </div>
                            <button class="schedule-remove-button" type="button" aria-label="Hapus tanggal">Hapus</button>
                        </div>
                    @endforeach
                </div>
                @error('lessons')<p class="master-data-error">{{ $message }}</p>@enderror
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
            <button class="schedule-remove-button" type="button" aria-label="Hapus jadwal">Hapus</button>
        </div>
    </template>

    <template id="lesson-row-template">
        <div class="schedule-row">
            <div class="schedule-input">
                <label>Tanggal</label>
                <input data-field="lesson_date" type="date" required>
            </div>
            <div class="schedule-input">
                <label>Jam les</label>
                <input data-field="lesson_time" type="time" required>
            </div>
            <button class="schedule-remove-button" type="button" aria-label="Hapus tanggal">Hapus</button>
        </div>
    </template>
@endsection
