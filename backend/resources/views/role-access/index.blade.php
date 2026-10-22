@extends('layouts.dashboard')

@section('title', 'Role Akses')
@section('breadcrumb', 'Role Akses')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN AKSES</p>
            <h1>Role Akses</h1>
            <p>Kelola role yang tersedia di aplikasi.</p>
        </div>
        @if (auth()->user()->hasPermission('role_access.create'))
            <a class="primary-action" href="{{ route('role-access.create') }}"><span aria-hidden="true">+</span> Tambah Role</a>
        @endif
    </section>

    @if (session('status'))
        <div class="master-data-alert" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->has('role'))
        <div class="master-data-alert master-data-alert-error" role="alert">{{ $errors->first('role') }}</div>
    @endif

    <section class="panel master-data-panel" aria-label="Daftar role akses">
        <div class="panel-heading">
            <div>
                <h2>Daftar Role</h2>
                <p>{{ $roles->count() }} role terdaftar</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Nama role</th>
                        <th>Kode</th>
                        <th>Pengguna</th>
                        <th>Tipe</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td><strong>{{ $role->name }}</strong></td>
                            <td>{{ $role->code }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $role->is_system ? 'Sistem' : 'Kustom' }}</td>
                            <td class="master-data-actions">
                                @if (auth()->user()->hasPermission('role_access.permissions'))
                                    <a class="table-action" href="{{ route('role-access.permissions.edit', $role) }}">Akses</a>
                                @endif
                                @if (auth()->user()->hasPermission('role_access.update'))
                                    <a class="table-action" href="{{ route('role-access.edit', $role) }}">Ubah</a>
                                @endif
                                @if (auth()->user()->hasPermission('role_access.delete'))
                                    <form method="POST" action="{{ route('role-access.destroy', $role) }}" onsubmit="return confirm('Hapus role ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="table-action table-action-danger" type="submit" @disabled($role->is_system || $role->users_count > 0)>Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="5">Belum ada role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
