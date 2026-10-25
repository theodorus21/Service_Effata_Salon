@extends('layouts.dashboard')

@section('title', 'Produk')
@section('breadcrumb', 'Produk')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN</p>
            <h1>Daftar Produk</h1>
            <p>Kelola produk kelas beserta level, program, kategori, dan frekuensi.</p>
        </div>
        <div class="learning-class-heading-actions">
            @if (auth()->user()->hasPermission('products.create'))
            <a class="primary-action" href="{{ route('products.create') }}"><span aria-hidden="true">+</span> Tambah Produk</a>
            @endif
        </div>
    </section>

    @if (session('status'))
        <div class="master-data-alert" role="status">{{ session('status') }}</div>
    @endif

    <section class="panel schedule-filter-panel product-filter-panel" aria-label="Filter produk">
        <form class="schedule-filter-form product-filter-form" method="GET" action="{{ route('products.index') }}">
            @foreach ([
                'level_id' => ['Level', $levels, 'Semua level'],
                'program_id' => ['Program', $programs, 'Semua program'],
                'category_id' => ['Kategori', $categories, 'Semua kategori'],
                'frequency_id' => ['Frekuensi', $frequencies, 'Semua frekuensi'],
            ] as $field => [$label, $options, $placeholder])
                <div class="master-data-field">
                    <label for="filter-{{ $field }}">{{ $label }}</label>
                    <select id="filter-{{ $field }}" name="{{ $field }}">
                        <option value="">{{ $placeholder }}</option>
                        @foreach ($options as $option)
                            <option value="{{ $option->id }}" @selected((string) ($filters[$field] ?? '') === (string) $option->id)>{{ $option->value }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="master-data-field">
                <label for="filter-status">Status</label>
                <select id="filter-status" name="status">
                    <option value="">Semua status</option>
                    @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="schedule-filter-actions">
                <button class="primary-action" type="submit">Terapkan filter</button>
                <a class="secondary-action" href="{{ route('products.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel master-data-panel" aria-label="Daftar produk">
        <div class="panel-heading">
            <div>
                <h2>Daftar Produk</h2>
                <p>{{ $products->total() }} produk terdaftar</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Nama kelas</th>
                        <th>Level</th>
                        <th>Program</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Frekuensi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->level->value }}</td>
                            <td>{{ $product->program->value }}</td>
                            <td>{{ $product->category->value }}</td>
                            <td>{{ ucfirst($product->status) }}</td>
                            <td>{{ $product->frequency?->value ?? '-' }}</td>
                            <td class="master-data-actions">
                                <a class="table-action" href="{{ route('products.show', $product) }}">Detail</a>
                                @if (auth()->user()->hasPermission('products.update'))
                                    <a class="table-action" href="{{ route('products.edit', $product) }}">Ubah</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="7">Tidak ada produk yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="master-data-pagination">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
