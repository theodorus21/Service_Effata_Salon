<?php

namespace App\Http\Controllers;

use App\Models\LookupValue;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'level_id' => ['nullable', 'integer', $this->lookupRule('class_level')],
            'program_id' => ['nullable', 'integer', $this->lookupRule('program')],
            'category_id' => ['nullable', 'integer', $this->lookupRule('class_category')],
            'frequency_id' => ['nullable', 'integer', $this->lookupRule('class_frequency')],
            'status' => ['nullable', Rule::in(Product::STATUSES)],
        ]);

        $products = Product::query()
            ->with(['level', 'program', 'category', 'frequency'])
            ->when($filters['level_id'] ?? null, fn ($query, $id) => $query->where('level_id', $id))
            ->when($filters['program_id'] ?? null, fn ($query, $id) => $query->where('program_id', $id))
            ->when($filters['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($filters['frequency_id'] ?? null, fn ($query, $id) => $query->where('frequency_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            ...$this->options(),
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['level', 'program', 'category', 'frequency', 'creator', 'updater']);

        return view('products.show', ['product' => $product]);
    }

    public function create(): View
    {
        return view('products.form', ['product' => new Product, ...$this->options()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data, $request): void {
            Product::create([
                ...$data,
                'code' => $this->generateCode($data),
                'status' => 'active',
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', ['product' => $product, ...$this->options()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update([
            ...$this->validatedData($request, $product),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        $values = fn (string $category) => LookupValue::query()
            ->where('category', $category)
            ->orderBy('sort_order')
            ->orderBy('value')
            ->get();

        return [
            'levels' => $values('class_level'),
            'programs' => $values('program'),
            'categories' => $values('class_category'),
            'frequencies' => $values('class_frequency'),
        ];
    }

    /**
     * Format: PROGRAM-KATEGORI-FREKUENSI-LEVEL-NNN, e.g. HP-R-2S-B-001.
     *
     * @param  array<string, mixed>  $data
     */
    private function generateCode(array $data): string
    {
        $initials = fn (int $id): string => collect(preg_split('/\s+/', trim(LookupValue::findOrFail($id)->value)))
            ->map(fn (string $word) => preg_match('/^\d+/', $word, $m) ? $m[0] : mb_substr($word, 0, 1))
            ->implode('');

        $prefix = mb_strtoupper(implode('-', array_map($initials, [
            $data['program_id'],
            $data['category_id'],
            $data['frequency_id'],
            $data['level_id'],
        ])));

        $last = Product::query()
            ->where('code', 'like', $prefix.'-%')
            ->lockForUpdate()
            ->pluck('code')
            ->map(fn (string $code) => (int) substr($code, strlen($prefix) + 1))
            ->max() ?? 0;

        return sprintf('%s-%03d', $prefix, $last + 1);
    }

    private function lookupRule(string $category): Exists
    {
        return Rule::exists('lookup_values', 'id')->where('category', $category);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level_id' => ['required', 'integer', $this->lookupRule('class_level')],
            'program_id' => ['required', 'integer', $this->lookupRule('program')],
            'category_id' => ['required', 'integer', $this->lookupRule('class_category')],
            'frequency_id' => ['required', 'integer', $this->lookupRule('class_frequency')],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:65535'],
            'price' => ['required', 'integer', 'min:0', 'max:9999999999'],
            'description' => ['nullable', 'string', 'max:5000'],
            ...($product ? ['status' => ['required', Rule::in(Product::STATUSES)]] : []),
        ]);
    }
}
