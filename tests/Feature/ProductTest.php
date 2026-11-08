<?php

namespace Tests\Feature;

use App\Models\LookupValue;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use AuthenticatesAsMasterAdmin;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    private function payload(array $overrides = []): array
    {
        $level = LookupValue::firstOrCreate(['category' => 'class_level', 'value' => 'Beginner'], ['sort_order' => 1]);
        $category = LookupValue::firstOrCreate(['category' => 'class_category', 'value' => 'Reguler'], ['sort_order' => 1]);
        $frequency = LookupValue::firstOrCreate(['category' => 'class_frequency', 'value' => '2x seminggu'], ['sort_order' => 1]);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();

        return [
            'name' => 'Calistung Dasar',
            'level_id' => $level->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
            'frequency_id' => $frequency->id,
            'duration_minutes' => 60,
            'price' => 150000,
            'description' => 'Belajar membaca.',
            ...$overrides,
        ];
    }

    public function test_product_list_and_create_form_are_available(): void
    {
        $this->get(route('products.index'))->assertOk()->assertSee('Tambah Produk');
        $this->get(route('products.create'))->assertOk()->assertSee('Level kelas');
    }

    public function test_product_is_created_and_updated_with_audit_fields(): void
    {
        $this->post(route('products.store'), $this->payload())->assertRedirect(route('products.index'));

        $product = Product::firstOrFail();
        $this->assertSame(auth()->id(), $product->created_by);
        $this->assertSame(auth()->id(), $product->updated_by);

        $this->put(route('products.update', $product), [...$this->payload(['name' => 'Calistung Lanjut']), 'level_id' => $product->level_id, 'category_id' => $product->category_id, 'frequency_id' => $product->frequency_id, 'status' => 'inactive'])
            ->assertRedirect(route('products.index'));
        $this->assertSame('Calistung Lanjut', $product->fresh()->name);
        $this->assertSame('active', $product->status);
        $this->assertSame('inactive', $product->fresh()->status);
        $this->assertSame($product->code, $product->fresh()->code);
    }

    public function test_product_code_is_generated_from_initials_with_sequence(): void
    {
        $payload = $this->payload();
        $this->post(route('products.store'), $payload);
        $this->post(route('products.store'), $payload);

        $codes = Product::orderBy('id')->pluck('code')->all();
        $this->assertSame(['CI-R-2S-B-001', 'CI-R-2S-B-002'], $codes);
    }

    public function test_product_list_shows_only_summary_columns_and_detail_page_shows_the_rest(): void
    {
        $this->post(route('products.store'), $this->payload());
        $product = Product::firstOrFail();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Nama kelas')
            ->assertSee('Frekuensi')
            ->assertSee(route('products.show', $product))
            ->assertDontSee($product->code)
            ->assertDontSee('Rp 150.000')
            ->assertDontSee('60 mnt');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->code)
            ->assertSee('Rp 150.000')
            ->assertSee('60 menit')
            ->assertSee('Belajar membaca.');
    }

    public function test_product_list_filters_by_level_status_and_frequency(): void
    {
        $this->post(route('products.store'), $this->payload());
        $advanced = LookupValue::firstOrCreate(['category' => 'class_level', 'value' => 'Advanced'], ['sort_order' => 2]);
        $this->post(route('products.store'), $this->payload(['name' => 'Calistung Lanjut', 'level_id' => $advanced->id]));
        Product::where('name', 'Calistung Lanjut')->update(['status' => 'inactive']);
        $otherFrequency = LookupValue::firstOrCreate(['category' => 'class_frequency', 'value' => '1x seminggu'], ['sort_order' => 2]);
        $this->post(route('products.store'), $this->payload(['name' => 'Kelas Mingguan', 'frequency_id' => $otherFrequency->id]));

        $this->get(route('products.index', ['level_id' => $advanced->id]))
            ->assertSee('Calistung Lanjut')
            ->assertDontSee('Calistung Dasar');

        $this->get(route('products.index', ['status' => 'inactive']))
            ->assertSee('Calistung Lanjut')
            ->assertDontSee('Calistung Dasar');

        $this->get(route('products.index', ['frequency_id' => $otherFrequency->id]))
            ->assertSee('Kelas Mingguan')
            ->assertDontSee('Calistung Dasar');

        $this->get(route('products.index', ['status' => 'unknown']))
            ->assertSessionHasErrors('status');
    }

    public function test_level_settings_crud(): void
    {
        $this->post(route('settings.class-levels.store'), ['value' => 'Advanced', 'sort_order' => 2])
            ->assertRedirect(route('settings.index'));
        $this->get(route('settings.index'))->assertOk()->assertSee('Advanced');
    }
}
