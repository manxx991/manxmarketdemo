<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\WholesaleRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WholesalePricingFormTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner', 'is_active' => 1]);
        $this->actingAs($this->owner);
        $this->product = Product::create([
            'sku' => 'WHOLESALE-01', 'name' => 'Produk Grosir Uji', 'unit' => 'pcs',
            'cost_price' => 5000, 'retail_price' => 10000,
            'stock' => 20, 'min_stock' => 2, 'is_active' => 1,
        ]);
    }

    public function test_inventory_form_exposes_and_prefills_per_unit_wholesale_discount(): void
    {
        WholesaleRule::create([
            'product_id' => $this->product->id,
            'rule_type' => 'per_item_qty',
            'min_qty' => 5,
            'discount_type' => 'fixed_amount',
            'discount_value' => 1500,
            'is_active' => 1,
        ]);

        $this->get('/inventory')->assertOk()
            ->assertSee('id="hasWholesale"', false)
            ->assertSee('name="ws_min_qty"', false)
            ->assertSee('name="ws_discount_value"', false)
            ->assertSee('data-wholesale_min_qty="5"', false)
            ->assertSee('data-wholesale_discount_value="1500.00"', false)
            ->assertSee('data-wholesale_discount_type="fixed_amount"', false)
            ->assertSee('data-wholesale_enabled="1"', false);
    }

    public function test_wholesale_discount_is_saved_per_unit_and_can_be_disabled(): void
    {
        $payload = [
            'id' => $this->product->id,
            'sku' => $this->product->sku,
            'name' => $this->product->name,
            'retail_price' => 10000,
            'has_wholesale' => '1',
            'ws_min_qty' => 5,
            'ws_discount_type' => 'fixed_amount',
            'ws_discount_value' => 1500,
        ];

        $this->postJson('/api/inventory/product', $payload)->assertOk();
        $this->assertDatabaseHas('wholesale_rules', [
            'product_id' => $this->product->id,
            'min_qty' => 5,
            'discount_type' => 'fixed_amount',
            'discount_value' => 1500,
            'is_active' => 1,
        ]);

        $this->get('/pos')->assertOk()->assertSee('Rp 8.500', false);
        $cashier = User::factory()->create(['role' => 'kasir', 'is_active' => 1]);
        $this->actingAs($cashier)->postJson('/api/pos/checkout', [
            'items' => [['product_id' => $this->product->id, 'qty' => 5]],
            'payment_method' => 'qris',
        ])->assertOk()->assertJsonPath('transaction.grand_total', 42500);

        $this->actingAs($this->owner)->postJson('/api/inventory/product', array_merge($payload, ['has_wholesale' => '0']))->assertOk();
        $this->assertDatabaseMissing('wholesale_rules', ['product_id' => $this->product->id]);
    }

    public function test_pos_wholesale_badge_shows_correct_final_price_for_percentage_discount(): void
    {
        WholesaleRule::create([
            'product_id' => $this->product->id,
            'rule_type' => 'per_item_qty',
            'min_qty' => 5,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => 1,
        ]);

        $this->get('/pos')->assertOk()
            ->assertSee('Rp 9.000', false)
            ->assertSee('"wholesale_final_price":9000', false);
    }

    public function test_pos_wholesale_badge_shows_correct_final_price_for_special_price_rule(): void
    {
        WholesaleRule::create([
            'product_id' => $this->product->id,
            'rule_type' => 'per_item_qty',
            'min_qty' => 5,
            'discount_type' => 'special_price',
            'discount_value' => 8000,
            'is_active' => 1,
        ]);

        $this->get('/pos')->assertOk()
            ->assertSee('Rp 8.000', false)
            ->assertSee('"wholesale_final_price":8000', false);
    }
}
