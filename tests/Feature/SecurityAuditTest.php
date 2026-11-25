<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cashier = User::factory()->create([
            'username' => 'audit_cashier', 'role' => 'kasir',
            'password' => Hash::make('Audit-Fixture-Only!9'),
        ]);
        $this->product = Product::create([
            'sku' => 'AUDIT-01', 'name' => 'Audit Product', 'unit' => 'pcs',
            'cost_price' => 6000, 'retail_price' => 10000,
            'stock' => 5, 'min_stock' => 1, 'is_active' => 1,
        ]);
    }

    /** @dataProvider blankSeedPasswords */
    public function test_seeder_requires_secret_before_creating_any_account($password): void
    {
        config(['auth.seed_default_password' => $password]);
        $before = User::count();
        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Seeder accepted an empty SEED_DEFAULT_PASSWORD.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('SEED_DEFAULT_PASSWORD', $e->getMessage());
        }
        $this->assertSame($before, User::count());
    }

    public function test_seeder_provisions_once_without_rotating_existing_passwords(): void
    {
        config(['auth.seed_default_password' => 'Provision-Fixture-Only!9']);
        $this->seed(DatabaseSeeder::class);
        $accounts = User::whereIn('username', ['owner', 'kasir', 'gudang', 'pembeli'])->get();
        $this->assertCount(4, $accounts);
        foreach ($accounts as $account) {
            $this->assertTrue(Hash::check('Provision-Fixture-Only!9', $account->password));
        }
        $passwords = $accounts->pluck('password', 'username')->all();
        foreach ([null, 'Different-Fixture-Secret!9'] as $secret) {
            config(['auth.seed_default_password' => $secret]);
            $this->seed(DatabaseSeeder::class);
            $this->assertSame($passwords, User::whereIn('username', array_keys($passwords))->pluck('password', 'username')->all());
        }
    }

    public function test_seeder_missing_secret_preserves_existing_account_without_partial_provisioning(): void
    {
        $existing = User::factory()->create(['username' => 'owner', 'role' => 'owner', 'password' => Hash::make('Existing-Fixture!9')]);
        config(['auth.seed_default_password' => null]);
        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Missing provisioning secret was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame(2, User::count());
            $this->assertTrue(Hash::check('Existing-Fixture!9', $existing->fresh()->password));
        }
    }

    public function test_factory_defaults_do_not_provision_privileged_or_known_passwords(): void
    {
        config(['auth.seed_default_password' => 'Known-Seed-Password!9']);
        $first = User::factory()->make();
        $second = User::factory()->make();
        $this->assertSame('pembeli', $first->role);
        $this->assertFalse(Hash::check('Known-Seed-Password!9', $first->password));
        $this->assertFalse(Hash::check('password123', $first->password));
        $this->assertNotSame($first->password, $second->password);
    }

    /** @dataProvider duplicateCartAliases */
    public function test_checkout_rejects_duplicate_canonical_products_before_any_write(string $firstKey, string $secondKey): void
    {
        $before = $this->checkoutState();
        $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [
                [$firstKey => $this->product->id, 'qty' => 3],
                [$secondKey => (string) $this->product->id, 'qty' => 3],
            ],
            'payment_method' => 'tunai', 'paid_amount' => 100000,
        ])->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame($before, $this->checkoutState());
    }

    public static function duplicateCartAliases(): array
    {
        return [['id', 'id'], ['product_id', 'product_id'], ['id', 'product_id']];
    }

    /** @dataProvider invalidCartIntegers */
    public function test_checkout_rejects_invalid_integer_fields_without_silently_skipping($field, $value): void
    {
        $before = $this->checkoutState();
        $invalid = ['product_id' => $this->product->id, 'qty' => 1];
        $invalid[$field] = $value;
        $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [$invalid], 'payment_method' => 'qris',
        ])->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame($before, $this->checkoutState());
    }

    public static function invalidCartIntegers(): array
    {
        $cases = [];
        foreach (['product_id', 'qty'] as $field) {
            foreach ([0, -1, 'bad', '1oops', '1.5', 1.5, true, '9999999999999999999999999', null] as $value) {
                $cases[] = [$field, $value];
            }
        }
        $cases[] = ['qty', 2147483648];
        return $cases;
    }

    public function test_checkout_valid_quantity_decrements_stock_and_records_sale(): void
    {
        $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [['id' => (string) $this->product->id, 'qty' => '3']],
            'payment_method' => 'tunai', 'paid_amount' => 40000,
        ])->assertOk()->assertJsonPath('transaction.change_amount', 10000);
        $this->assertSame(2, $this->product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id, 'movement_type' => 'sale',
            'qty_change' => -3, 'stock_before' => 5, 'stock_after' => 2,
        ]);
    }

    /** @dataProvider invalidServicePayments */
    public function test_service_rejects_invalid_paid_amounts_before_writes($paid): void
    {
        $before = $this->checkoutState();
        try {
            app(\App\Services\CheckoutService::class)->processCheckout([
                'items' => [['id' => $this->product->id, 'qty' => 1]],
                'payment_method' => 'qris', 'paid_amount' => $paid,
            ], $this->cashier);
            $this->fail('Invalid payment was accepted.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('paid_amount', $e->errors());
        }
        $this->assertSame($before, $this->checkoutState());
    }

    /** @dataProvider invalidHttpPayments */
    public function test_http_rejects_invalid_paid_amounts_before_writes($paid): void
    {
        $before = $this->checkoutState();
        $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [['id' => $this->product->id, 'qty' => 1]],
            'payment_method' => 'qris', 'paid_amount' => $paid,
        ])->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame($before, $this->checkoutState());
    }

    public static function invalidHttpPayments(): array
    {
        return [[-1], ['-25000'], ['NaN'], ['INF'], ['1e309'], ['not-money'], ['25oops'], [true], [[]]];
    }

    public static function invalidServicePayments(): array
    {
        return array_merge(self::invalidHttpPayments(), [[NAN], [INF], [-INF]]);
    }

    /** @dataProvider debtDownPayments */
    public function test_debt_payment_preserves_empty_dp_and_remaining_debt($paid, int $remaining): void
    {
        $customer = User::factory()->create(['role' => 'pembeli', 'credit_limit' => 200000]);
        $this->product->update(['retail_price' => 100000]);
        $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [['product_id' => $this->product->id, 'qty' => 1]],
            'customer_id' => $customer->id, 'payment_method' => 'hutang', 'paid_amount' => $paid,
        ])->assertOk()->assertJsonPath('transaction.remaining_debt', $remaining);
        $this->assertDatabaseHas('receivables', ['customer_id' => $customer->id, 'remaining_debt' => $remaining]);
    }

    public static function debtDownPayments(): array
    {
        return [[25000, 75000], ['', 100000], [null, 100000]];
    }

    /** @dataProvider invalidWholesaleWrites */
    public function test_product_rejects_invalid_wholesale_before_mutating_product_rules_or_initial_stock(bool $updating, array $invalid): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        \App\Models\WholesaleRule::create([
            'product_id' => $this->product->id, 'rule_type' => 'per_item_qty', 'min_qty' => 2,
            'discount_type' => 'fixed_amount', 'discount_value' => 1000, 'is_active' => 1,
        ]);
        $before = $this->inventoryState();
        $payload = array_merge([
            'sku' => $updating ? $this->product->sku : 'AUDIT-NEW',
            'name' => 'Invalid Rule Product', 'retail_price' => 10000, 'cost_price' => 6000, 'stock' => 10,
            'has_wholesale' => true, 'ws_min_qty' => 2, 'ws_discount_type' => 'fixed_amount', 'ws_discount_value' => 1000,
        ], $invalid);
        if ($updating) {
            $payload['id'] = $this->product->id;
        }
        $response = $this->actingAs($owner)->postJson('/api/inventory/product', $payload)->assertStatus(422);
        $this->assertStringContainsString('Invalid Rule Product', $response->json('message'));
        $this->assertSame($before, $this->inventoryState());
    }

    public static function invalidWholesaleWrites(): array
    {
        $cases = [];
        foreach ([false, true] as $update) {
            foreach ([
                ['ws_discount_type' => 'unknown'], ['ws_min_qty' => 1], ['ws_min_qty' => '2.5'],
                ['ws_discount_value' => -1], ['ws_discount_value' => 'NaN'], ['ws_discount_value' => '1e309'],
                ['ws_discount_type' => 'percentage', 'ws_discount_value' => 101],
                ['ws_discount_type' => 'fixed_amount', 'ws_discount_value' => 10001],
                ['ws_discount_type' => 'special_price', 'ws_discount_value' => -1],
            ] as $invalid) {
                $cases[] = [$update, $invalid];
            }
        }
        return $cases;
    }

    /** @dataProvider productWriteModes */
    public function test_product_and_wholesale_write_rolls_back_when_rule_persistence_fails(bool $updating): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        \App\Models\WholesaleRule::create([
            'product_id' => $this->product->id, 'rule_type' => 'per_item_qty', 'min_qty' => 2,
            'discount_type' => 'fixed_amount', 'discount_value' => 500, 'is_active' => 1,
        ]);
        $before = $this->inventoryState();
        $dispatcher = \App\Models\WholesaleRule::getEventDispatcher();
        \App\Models\WholesaleRule::setEventDispatcher(clone $dispatcher);
        \App\Models\WholesaleRule::creating(static function () {
            throw new \RuntimeException('Simulated rule persistence failure.');
        });
        try {
            $payload = [
                'sku' => $updating ? $this->product->sku : 'ATOMIC-NEW', 'name' => 'Atomic Product',
                'retail_price' => 10000, 'stock' => 10,
                'has_wholesale' => true, 'ws_min_qty' => 2, 'ws_discount_type' => 'percentage', 'ws_discount_value' => 10,
            ];
            if ($updating) {
                $payload['id'] = $this->product->id;
            }
            $this->actingAs($owner)->postJson('/api/inventory/product', $payload)->assertStatus(500);
        } finally {
            \App\Models\WholesaleRule::setEventDispatcher($dispatcher);
        }
        $this->assertSame($before, $this->inventoryState());
    }

    public static function productWriteModes(): array
    {
        return [[false], [true]];
    }

    /** @dataProvider invalidPersistedDiscounts */
    public function test_checkout_rejects_invalid_effective_persisted_discount_without_writes(string $type, $value, int $minimum): void
    {
        \App\Models\WholesaleRule::create([
            'product_id' => $this->product->id, 'rule_type' => 'per_item_qty', 'min_qty' => $minimum,
            'discount_type' => $type, 'discount_value' => $value, 'is_active' => 1,
        ]);
        $before = $this->checkoutState();
        $response = $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [['product_id' => $this->product->id, 'qty' => 3]], 'payment_method' => 'qris',
        ])->assertStatus(422)->assertJson(['success' => false]);
        $this->assertStringContainsString($this->product->name, $response->json('message'));
        $this->assertSame($before, $this->checkoutState());
    }

    public static function invalidPersistedDiscounts(): array
    {
        return [
            ['fixed_amount', 10001, 2], ['fixed_amount', -1, 2],
            ['percentage', 101, 2], ['percentage', -1, 2], ['special_price', -1, 2],
            ['fixed_amount', 1000, 0],
        ];
    }

    /** @dataProvider validWholesalePrices */
    public function test_valid_wholesale_rules_preserve_checkout_prices_and_server_cost_snapshot(string $type, int $value, int $total): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner)->postJson('/api/inventory/product', [
            'id' => $this->product->id, 'sku' => $this->product->sku, 'name' => $this->product->name,
            'retail_price' => 10000, 'cost_price' => 6000,
            'has_wholesale' => true, 'ws_min_qty' => 2, 'ws_discount_type' => $type, 'ws_discount_value' => $value,
        ])->assertOk();
        $this->assertDatabaseHas('wholesale_rules', ['product_id' => $this->product->id, 'discount_value' => $value]);
        $response = $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [['product_id' => $this->product->id, 'qty' => 3, 'cost_price' => 0, 'unit_price' => 0]],
            'payment_method' => 'qris',
        ])->assertOk()->assertJsonPath('transaction.grand_total', $total)->assertJsonPath('transaction.paid_amount', $total);
        $this->assertDatabaseHas('transaction_items', ['transaction_id' => $response->json('transaction_id'), 'cost_price_snapshot' => 6000]);
    }

    public static function validWholesalePrices(): array
    {
        return [['fixed_amount', 1000, 27000], ['percentage', 10, 27000], ['special_price', 8000, 24000], ['special_price', 0, 0]];
    }

    /** @dataProvider forbiddenManualTypes */
    public function test_manual_mutation_rejects_reserved_and_unknown_types(string $type, string $alias): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $before = $this->checkoutState();
        $this->actingAs($owner)->postJson('/api/inventory/adjust-stock', [
            'product_id' => $this->product->id, 'qty_change' => 2, $alias => $type,
        ])->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame($before, $this->checkoutState());
        try {
            app(\App\Services\StockService::class)->adjustStock($this->product->id, 2, $type, 'Audit', $owner);
            $this->fail('Reserved manual movement type was accepted by service.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('movement_type', $e->errors());
        }
        $this->assertSame($before, $this->checkoutState());
    }

    public static function forbiddenManualTypes(): array
    {
        $cases = [];
        foreach (['sale', 'void_return', 'unknown'] as $type) {
            foreach (['movement_type', 'type'] as $alias) {
                $cases[] = [$type, $alias];
            }
        }
        return $cases;
    }

    /** @dataProvider invalidManualQuantities */
    public function test_manual_mutation_validates_integer_quantity_before_casting($quantity): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $before = $this->checkoutState();
        try {
            app(\App\Services\StockService::class)->adjustStock($this->product->id, $quantity, 'adjustment', 'Audit', $owner);
            $this->fail('Invalid manual quantity was accepted.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('qty_change', $e->errors());
        }
        foreach (['qty', 'qty_change'] as $alias) {
            $this->actingAs($owner)->postJson('/api/inventory/adjust-stock', [
                'product_id' => $this->product->id, $alias => $quantity, 'type' => 'in',
            ])->assertStatus(422)->assertJson(['success' => false]);
        }
        $this->assertSame($before, $this->checkoutState());
    }

    public static function invalidManualQuantities(): array
    {
        return [[1.5], [true], ['2oops'], [0], ['1.2'], [2147483648], [null]];
    }

    /** @dataProvider invalidPhysicalQuantities */
    public function test_physical_stock_validates_nonnegative_integer_before_casting($quantity): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $before = $this->checkoutState();
        try {
            app(\App\Services\StockService::class)->setPhysicalStock($this->product->id, $quantity, 'Audit', $owner);
            $this->fail('Invalid physical stock was accepted.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('new_stock', $e->errors());
        }
        foreach (['new_stock', 'actual_stock'] as $alias) {
            $this->actingAs($owner)->postJson('/api/inventory/set-physical-stock', [
                'product_id' => $this->product->id, $alias => $quantity,
            ])->assertStatus(422)->assertJson(['success' => false]);
        }
        $this->assertSame($before, $this->checkoutState());
    }

    public static function invalidPhysicalQuantities(): array
    {
        return [[1.5], [true], ['2oops'], [-1], ['1.2'], [2147483648], [null]];
    }

    public function test_checkout_response_contains_only_receipt_fields_and_keeps_cost_snapshot_server_side(): void
    {
        foreach ([
            'store_name' => 'Audit Store', 'store_address' => 'Fixture Address', 'store_phone' => '0800000000',
            'receipt_footer' => 'Fixture Footer', 'internal_token' => 'test-only-private-setting',
        ] as $key => $value) {
            \App\Models\StoreSetting::create(['setting_key' => $key, 'setting_value' => $value]);
        }
        $response = $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [['product_id' => $this->product->id, 'qty' => 1]],
            'payment_method' => 'tunai', 'paid_amount' => 20000,
        ])->assertOk()->assertJson(['success' => true]);
        $this->assertNoPrivateReceiptFields($response->json());
        $this->assertEqualsCanonicalizing(
            ['success', 'message', 'transaction_id', 'invoice_number', 'transaction'], array_keys($response->json())
        );
        $this->assertEqualsCanonicalizing(
            ['product_id', 'name', 'sku', 'qty', 'unit_price', 'discount_per_item', 'subtotal_item'],
            array_keys($response->json('transaction.items.0'))
        );
        $this->assertEqualsCanonicalizing(
            ['store_name', 'store_address', 'store_phone', 'receipt_footer'], array_keys($response->json('transaction.store'))
        );
        $this->assertDatabaseHas('transaction_items', ['transaction_id' => $response->json('transaction_id'), 'cost_price_snapshot' => 6000]);
    }

    public function test_reprint_response_contains_only_receipt_store_fields(): void
    {
        foreach ([
            'store_name' => 'Audit Store', 'store_address' => 'Fixture Address', 'store_phone' => '0800000000',
            'receipt_footer' => 'Fixture Footer', 'internal_token' => 'test-only-private-setting',
        ] as $key => $value) {
            \App\Models\StoreSetting::create(['setting_key' => $key, 'setting_value' => $value]);
        }

        $checkout = $this->actingAs($this->cashier)->postJson('/api/pos/checkout', [
            'items' => [['product_id' => $this->product->id, 'qty' => 1]],
            'payment_method' => 'tunai', 'paid_amount' => 20000,
        ])->assertOk();

        $reprint = $this->actingAs($this->cashier)
            ->getJson('/api/transactions/' . $checkout->json('transaction_id'))
            ->assertOk();

        $expectedStoreFields = ['store_name', 'store_address', 'store_phone', 'receipt_footer'];
        $this->assertEqualsCanonicalizing($expectedStoreFields, array_keys($reprint->json('transaction.store')));
        $this->assertEqualsCanonicalizing($expectedStoreFields, array_keys($reprint->json('store')));
        $this->assertNoPrivateReceiptFields($reprint->json());
    }

    private function assertNoPrivateReceiptFields(array $payload): void
    {
        foreach ($payload as $key => $value) {
            $this->assertDoesNotMatchRegularExpression('/cost|stock|product_model|password|secret|internal_token|credit_limit/i', (string) $key);
            if (is_array($value)) {
                $this->assertNoPrivateReceiptFields($value);
            }
        }
    }

    private function inventoryState(): array
    {
        $state = [];
        foreach (['products', 'wholesale_rules', 'stock_movements'] as $table) {
            $state[$table] = \Illuminate\Support\Facades\DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        }
        return $state;
    }

    private function checkoutState(): array
    {
        $state = [];
        foreach (['transactions', 'transaction_items', 'stock_movements', 'receivables', 'receivable_payments'] as $table) {
            $state[$table] = \Illuminate\Support\Facades\DB::table($table)->count();
        }
        $state['stocks'] = Product::orderBy('id')->pluck('stock', 'id')->all();
        return $state;
    }

    public static function blankSeedPasswords(): array
    {
        return ['missing' => [null], 'empty' => [''], 'whitespace' => [" \t\n"]];
    }
}
