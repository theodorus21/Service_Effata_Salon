@extends('layouts.dashboard')

@section('title', 'Pengaturan')
@section('breadcrumb', 'Pengaturan')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">KONFIGURASI APLIKASI</p>
            <h1>Pengaturan</h1>
            <p>Kelola nilai pilihan yang digunakan pada data kelas.</p>
        </div>
    </section>

    @if (session('status'))
        <div class="master-data-alert" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->has('program'))
        <div class="master-data-alert master-data-alert-error" role="alert">{{ $errors->first('program') }}</div>
    @endif
    @if ($errors->has('class_category'))
        <div class="master-data-alert master-data-alert-error" role="alert">{{ $errors->first('class_category') }}</div>
    @endif
    @if ($errors->has('class_level'))
        <div class="master-data-alert master-data-alert-error" role="alert">{{ $errors->first('class_level') }}</div>
    @endif
    @if ($errors->has('class_frequency'))
        <div class="master-data-alert master-data-alert-error" role="alert">{{ $errors->first('class_frequency') }}</div>
    @endif
    @if ($errors->has('lesson_status'))
        <div class="master-data-alert master-data-alert-error" role="alert">{{ $errors->first('lesson_status') }}</div>
    @endif

    @if (auth()->user()->hasPermission('settings.programs.view'))
    <section class="panel master-data-panel" aria-label="Program kelas">
        <div class="panel-heading settings-panel-heading">
            <div>
                <h2>Program Kelas</h2>
                <p>Pilihan ini akan tersedia saat membuat atau mengubah kelas.</p>
            </div>
            @if (auth()->user()->hasPermission('settings.programs.create'))
                <a class="primary-action" href="{{ route('settings.programs.create') }}"><span aria-hidden="true">+</span> Tambah Program</a>
            @endif
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Nama program</th>
                        <th>Kelas digunakan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programs as $program)
                        <tr>
                            <td>{{ $program->sort_order }}</td>
                            <td><strong>{{ $program->value }}</strong></td>
                            <td>{{ $program->learning_classes_count }}</td>
                            <td class="master-data-actions">
                                @if (auth()->user()->hasPermission('settings.programs.update'))
                                    <a class="table-action" href="{{ route('settings.programs.edit', $program) }}">Ubah</a>
                                @endif
                                @if (auth()->user()->hasPermission('settings.programs.delete'))
                                <form method="POST" action="{{ route('settings.programs.destroy', $program) }}" onsubmit="return confirm('Hapus program ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="table-action table-action-danger" type="submit">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="4">Belum ada program. Tambahkan program agar dapat dipilih untuk kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($programs->hasPages())
            <div class="master-data-pagination">{{ $programs->links() }}</div>
        @endif
    </section>
    @endif

    @if (auth()->user()->hasPermission('settings.categories.view'))
    <section class="panel master-data-panel settings-category-panel" aria-label="Kategori kelas">
        <div class="panel-heading settings-panel-heading">
            <div>
                <h2>Kategori Kelas</h2>
                <p>Pilihan kategori saat membuat atau mengubah kelas.</p>
            </div>
            @if (auth()->user()->hasPermission('settings.categories.create'))
                <a class="primary-action" href="{{ route('settings.class-categories.create') }}"><span aria-hidden="true">+</span> Tambah Kategori</a>
            @endif
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Nama kategori</th>
                        <th>Kelas digunakan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classCategories as $classCategory)
                        <tr>
                            <td>{{ $classCategory->sort_order }}</td>
                            <td><strong>{{ $classCategory->value }}</strong></td>
                            <td>{{ $classCategory->categorized_classes_count }}</td>
                            <td class="master-data-actions">
                                @if (auth()->user()->hasPermission('settings.categories.update'))
                                    <a class="table-action" href="{{ route('settings.class-categories.edit', $classCategory) }}">Ubah</a>
                                @endif
                                @if (auth()->user()->hasPermission('settings.categories.delete'))
                                <form method="POST" action="{{ route('settings.class-categories.destroy', $classCategory) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="table-action table-action-danger" type="submit">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="4">Belum ada kategori kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif

    @if (auth()->user()->hasPermission('settings.levels.view'))
    <section class="panel master-data-panel settings-category-panel" aria-label="Level kelas">
        <div class="panel-heading settings-panel-heading">
            <div>
                <h2>Level Kelas</h2>
                <p>Pilihan level saat membuat atau mengubah produk.</p>
            </div>
            @if (auth()->user()->hasPermission('settings.levels.create'))
                <a class="primary-action" href="{{ route('settings.class-levels.create') }}"><span aria-hidden="true">+</span> Tambah Level</a>
            @endif
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Nama level</th>
                        <th>Produk digunakan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classLevels as $classLevel)
                        <tr>
                            <td>{{ $classLevel->sort_order }}</td>
                            <td><strong>{{ $classLevel->value }}</strong></td>
                            <td>{{ $classLevel->level_products_count }}</td>
                            <td class="master-data-actions">
                                @if (auth()->user()->hasPermission('settings.levels.update'))
                                    <a class="table-action" href="{{ route('settings.class-levels.edit', $classLevel) }}">Ubah</a>
                                @endif
                                @if (auth()->user()->hasPermission('settings.levels.delete'))
                                <form method="POST" action="{{ route('settings.class-levels.destroy', $classLevel) }}" onsubmit="return confirm('Hapus level ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="table-action table-action-danger" type="submit">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="4">Belum ada level kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif

    @if (auth()->user()->hasPermission('settings.frequencies.view'))
    <section class="panel master-data-panel settings-category-panel" aria-label="Frekuensi kelas">
        <div class="panel-heading settings-panel-heading">
            <div>
                <h2>Frekuensi Kelas</h2>
                <p>Pilihan frekuensi saat membuat atau mengubah produk.</p>
            </div>
            @if (auth()->user()->hasPermission('settings.frequencies.create'))
                <a class="primary-action" href="{{ route('settings.class-frequencies.create') }}"><span aria-hidden="true">+</span> Tambah Frekuensi</a>
            @endif
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Nama frekuensi</th>
                        <th>Produk digunakan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classFrequencies as $classFrequency)
                        <tr>
                            <td>{{ $classFrequency->sort_order }}</td>
                            <td><strong>{{ $classFrequency->value }}</strong></td>
                            <td>{{ $classFrequency->frequency_products_count }}</td>
                            <td class="master-data-actions">
                                @if (auth()->user()->hasPermission('settings.frequencies.update'))
                                    <a class="table-action" href="{{ route('settings.class-frequencies.edit', $classFrequency) }}">Ubah</a>
                                @endif
                                @if (auth()->user()->hasPermission('settings.frequencies.delete'))
                                <form method="POST" action="{{ route('settings.class-frequencies.destroy', $classFrequency) }}" onsubmit="return confirm('Hapus frekuensi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="table-action table-action-danger" type="submit">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="4">Belum ada frekuensi kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif

    @if (auth()->user()->hasPermission('settings.lesson_statuses.view'))
    <section class="panel master-data-panel settings-category-panel" aria-label="Status jadwal les">
        <div class="panel-heading settings-panel-heading">
            <div>
                <h2>Status Jadwal Les</h2>
                <p>Status yang tersedia pada setiap record jadwal les.</p>
            </div>
            @if (auth()->user()->hasPermission('settings.lesson_statuses.create'))
                <a class="primary-action" href="{{ route('settings.lesson-statuses.create') }}"><span aria-hidden="true">+</span> Tambah Status</a>
            @endif
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Nama status</th>
                        <th>Warna</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lessonStatuses as $lessonStatus)
                        <tr>
                            <td>{{ $lessonStatus->sort_order }}</td>
                            <td>
                                <span class="lesson-status-badge" style="--lesson-status-color: {{ $lessonStatus->color }}">{{ $lessonStatus->label }}</span>
                                @if ($lessonStatus->code === 0)
                                    <small>Default</small>
                                @endif
                            </td>
                            <td><span class="lesson-status-color-swatch" style="background-color: {{ $lessonStatus->color }}"></span> {{ $lessonStatus->color }}</td>
                            <td class="master-data-actions">
                                @if (auth()->user()->hasPermission('settings.lesson_statuses.update'))
                                    <a class="table-action" href="{{ route('settings.lesson-statuses.edit', $lessonStatus) }}">Ubah</a>
                                @endif
                                @if (auth()->user()->hasPermission('settings.lesson_statuses.delete'))
                                <form method="POST" action="{{ route('settings.lesson-statuses.destroy', $lessonStatus) }}" onsubmit="return confirm('Hapus status ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="table-action table-action-danger" type="submit" @disabled($lessonStatus->code === 0)>Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="4">Belum ada status jadwal les.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif
@endsection
