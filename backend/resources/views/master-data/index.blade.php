@extends('layouts.dashboard')

@section('title', 'Master Data '.$roleLabel)
@section('breadcrumb', 'Master Data / '.$roleLabel)

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN AKUN</p>
            <h1>Data {{ $roleLabel }}</h1>
            <p>Kelola data akun {{ strtolower($roleLabel) }} Effata Fun Learning.</p>
        </div>
        @if (auth()->user()->hasPermission("master_data.{$role}.create"))
        <a class="primary-action" href="{{ route('master-data.create', ['role' => $role]) }}">
            <span aria-hidden="true">+</span> Tambah {{ $roleLabel }}
        </a>
        @endif
    </section>

    @if (session('status'))
        <div class="master-data-alert" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->has('user'))
        <div class="master-data-alert master-data-alert-error" role="alert">{{ $errors->first('user') }}</div>
    @endif

    <section class="panel master-data-panel" aria-label="Daftar {{ strtolower($roleLabel) }}">
        <div class="panel-heading">
            <div>
                <h2>Daftar {{ $roleLabel }}</h2>
                <p>{{ $users->total() }} data terdaftar</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>{{ $role === 'murid' ? 'Nama anak' : 'Nama' }}</th>
                        @if (in_array($role, ['guru', 'admin'], true))
                            <th>Nama panggilan</th>
                            <th>No. telepon</th>
                        @elseif ($role === 'murid')
                            <th>Nama panggilan</th>
                            <th>Orang tua</th>
                            <th>No. telepon orang tua</th>
                        @endif
                        <th>{{ $role === 'murid' ? 'Email orang tua' : 'Email' }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                @if (in_array($role, ['guru', 'admin', 'murid'], true) && auth()->user()->hasPermission("master_data.{$role}.detail"))
                                    <a class="master-data-name-link" href="{{ route('master-data.show', ['role' => $role, 'user' => $user->id]) }}">{{ $user->name }}</a>
                                @else
                                    <strong>{{ $user->name }}</strong>
                                @endif
                            </td>
                            @if (in_array($role, ['guru', 'admin'], true))
                                <td>{{ $user->nickname }}</td>
                                <td>{{ $user->phone }}</td>
                            @elseif ($role === 'murid')
                                <td>{{ $user->nickname }}</td>
                                <td>{{ $user->parent_name }}</td>
                                <td>{{ $user->parent_phone }}</td>
                            @endif
                            <td>{{ $user->email }}</td>
                            <td class="master-data-actions">
                                @if (in_array($role, ['guru', 'admin', 'murid'], true) && auth()->user()->hasPermission("master_data.{$role}.detail"))
                                    <a class="table-action" href="{{ route('master-data.show', ['role' => $role, 'user' => $user->id]) }}">Detail</a>
                                @endif
                                @if (auth()->user()->hasPermission("master_data.{$role}.update"))
                                    <a class="table-action" href="{{ route('master-data.edit', ['role' => $role, 'user' => $user->id]) }}">Ubah</a>
                                @endif
                                @if (auth()->user()->hasPermission("master_data.{$role}.delete"))
                                <form method="POST" action="{{ route('master-data.destroy', ['role' => $role, 'user' => $user->id]) }}" onsubmit="return confirm('Hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="table-action table-action-danger" type="submit">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="{{ in_array($role, ['guru', 'admin'], true) ? 5 : ($role === 'murid' ? 6 : 4) }}">Belum ada data {{ strtolower($roleLabel) }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="master-data-pagination">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
