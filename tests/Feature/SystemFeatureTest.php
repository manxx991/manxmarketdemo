<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\StoreSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SystemFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $kasir;
    protected User $gudang;
    protected User $pembeli;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['owner', 'kasir', 'gudang', 'pembeli'] as $role) {
            $this->{$role} = User::factory()->create([
                'name' => 'Fixture ' . $role,
                'username' => 'fixture_' . $role,
                'password' => Hash::make('Test-Fixture-Password!9'),
                'role' => $role,
                'is_active' => 1,
                'credit_limit' => $role === 'pembeli' ? 1000000 : 0,
            ]);
        }

        Product::create([
            'sku' => 'FIXTURE-01', 'name' => 'Fixture Product', 'unit' => 'pcs',
            'cost_price' => 6000, 'retail_price' => 10000, 'stock' => 50,
            'min_stock' => 5, 'is_active' => 1,
        ]);
    }

    public function test_auth_login_and_logout(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $loginResponse = $this->post('/login', [
            'username' => $this->owner->username,
            'password' => 'Test-Fixture-Password!9',
        ]);
        $loginResponse->assertRedirect(route('reports.index'));
        $this->assertAuthenticatedAs($this->owner);

        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_auth_switch_role(): void
    {
        $this->actingAs($this->owner);

        $switchResponse = $this->post('/switch-role', [
            'role' => 'kasir',
        ]);
        $switchResponse->assertRedirect(route('pos.index'));
        $this->assertEquals('kasir', Auth::user()->role);
    }

    public function test_pos_index_page_and_checkout_tunai(): void
    {
        $this->actingAs($this->kasir);

        $res = $this->get('/pos');
        $res->assertStatus(200);

        $product = Product::where('is_active', 1)->where('stock', '>=', 5)->first();
        $this->assertNotNull($product);

        $initialStock = $product->stock;

        $checkoutRes = $this->postJson('/api/pos/checkout', [
            'items' => [
                ['id' => $product->id, 'qty' => 2],
            ],
            'payment_method' => 'tunai',
            'paid_amount' => (float)$product->retail_price * 2,
        ]);

        $checkoutRes->assertStatus(200);
        $checkoutRes->assertJson(['success' => true]);

        $product->refresh();
        $this->assertEquals($initialStock - 2, $product->stock);
    }

    public function test_pos_checkout_hutang_with_customer(): void
    {
        $this->actingAs($this->kasir);

        $product = Product::where('is_active', 1)->where('stock', '>=', 2)->first();
        $this->assertNotNull($product);

        $checkoutRes = $this->postJson('/api/pos/checkout', [
            'items' => [
                ['id' => $product->id, 'qty' => 1],
            ],
            'payment_method' => 'hutang',
            'customer_id' => $this->pembeli->id,
            'paid_amount' => 0,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ]);

        $checkoutRes->assertStatus(200);
        $checkoutRes->assertJson(['success' => true]);

        $txId = $checkoutRes->json('transaction_id');
        $this->assertDatabaseHas('receivables', [
            'transaction_id' => $txId,
            'customer_id' => $this->pembeli->id,
            'status' => 'belum_lunas',
        ]);
    }

    public function test_transaction_index_detail_and_void(): void
    {
        $this->actingAs($this->owner);

        $indexRes = $this->get('/transactions');
        $indexRes->assertStatus(200);

        $trans = Transaction::with('items')->where('payment_status', 'lunas')->first();
        if ($trans) {
            $detailRes = $this->getJson("/api/transactions/{$trans->id}");
            $detailRes->assertStatus(200);
            $detailRes->assertJson(['success' => true]);

            $firstItem = $trans->items->first();
            if ($firstItem && $firstItem->product_id) {
                $prod = Product::find($firstItem->product_id);
                $oldStock = $prod ? $prod->stock : 0;

                $voidRes = $this->postJson('/api/transactions/void', [
                    'transaction_id' => $trans->id,
                    'void_reason' => 'Testing Void Pembatalan Nota Otomatis',
                ]);

                $voidRes->assertStatus(200);
                $voidRes->assertJson(['success' => true]);

                if ($prod) {
                    $prod->refresh();
                    $this->assertEquals($oldStock + $firstItem->qty, $prod->stock);
                }
            }
        }
    }

    public function test_receivables_index_and_payment(): void
    {
        $this->actingAs($this->kasir);

        $res = $this->get('/receivables');
        $res->assertStatus(200);

        $rec = Receivable::where('status', 'belum_lunas')->first();
        if ($rec) {
            $historyRes = $this->getJson("/api/receivables/{$rec->id}/history");
            $historyRes->assertStatus(200);
            $historyRes->assertJson(['success' => true]);

            $payRes = $this->postJson('/api/receivables/pay', [
                'receivable_id' => $rec->id,
                'amount_paid' => min(10000, $rec->remaining_debt),
                'payment_method' => 'tunai',
                'notes' => 'Testing cicilan piutang',
            ]);

            $payRes->assertStatus(200);
            $payRes->assertJson(['success' => true]);
        }
    }

    public function test_inventory_features(): void
    {
        $this->actingAs($this->gudang);

        $res = $this->get('/inventory');
        $res->assertStatus(200);

        $catRes = $this->postJson('/api/inventory/category', [
            'name' => 'Kategori Test ' . rand(100, 999),
            'description' => 'Deskripsi kategori test',
        ]);
        $catRes->assertStatus(200);
        $catRes->assertJson(['success' => true]);
        $catId = $catRes->json('category.id');

        $prodRes = $this->postJson('/api/inventory/product', [
            'category_id' => $catId,
            'sku' => 'TEST-' . rand(1000, 9999),
            'barcode' => '899' . rand(100000, 999999),
            'name' => 'Produk Test Otomatis',
            'unit' => 'Pcs',
            'cost_price' => 10000,
            'retail_price' => 15000,
            'stock' => 50,
            'min_stock' => 5,
        ]);
        $prodRes->assertStatus(200);
        $prodRes->assertJson(['success' => true]);
        $newProdId = $prodRes->json('product.id');

        $adjustRes = $this->postJson('/api/inventory/adjust-stock', [
            'product_id' => $newProdId,
            'type' => 'in',
            'qty' => 10,
            'notes' => 'Uji tambah stok',
        ]);
        $adjustRes->assertStatus(200);
        $adjustRes->assertJson(['success' => true]);

        $histRes = $this->getJson("/api/inventory/{$newProdId}/history");
        $histRes->assertStatus(200);
        $histRes->assertJson(['success' => true]);
    }

    public function test_roles_management(): void
    {
        $this->actingAs($this->owner);

        $res = $this->get('/roles');
        $res->assertStatus(200);
        $this->assertSame(['owner', 'kasir', 'gudang', 'pembeli'], $res->viewData('users')->getCollection()->pluck('role')->all());

        $username = 'testkasir' . rand(100, 999);
        $createRes = $this->postJson('/api/roles', [
            'name' => 'Kasir Baru Testing',
            'username' => $username,
            'password' => 'password123',
            'role' => 'kasir',
            'phone' => '081299990000',
        ]);
        $createRes->assertStatus(200);
        $createRes->assertJson(['success' => true]);
        $newUserId = $createRes->json('user.id');

        $toggleRes = $this->postJson("/api/roles/{$newUserId}/toggle-active");
        $toggleRes->assertStatus(200);
        $toggleRes->assertJson(['success' => true, 'is_active' => 0]);
    }

    public function test_reports_and_csv_export(): void
    {
        $this->actingAs($this->owner);

        $repRes = $this->get('/reports');
        $repRes->assertStatus(200);

        $csvRes = $this->get('/reports/export-csv');
        $csvRes->assertStatus(200);
        $this->assertTrue(str_contains($csvRes->headers->get('content-type') ?? '', 'text/csv'));
    }

    public function test_settings_update(): void
    {
        $this->actingAs($this->owner);

        $setRes = $this->get('/settings');
        $setRes->assertStatus(200);

        $postSet = $this->post('/settings', [
            'store_name' => 'MANXMARKET POS',
            'store_address' => 'Pasar Induk',
            'store_phone' => '081234567890',
            'receipt_footer' => 'Terima kasih atas belanjaan Anda',
            'default_due_days' => 14,
            'enable_stock_warning' => 1,
        ]);
        $postSet->assertRedirect(route('settings.index'));
    }

    public function test_customer_portal(): void
    {
        $this->actingAs($this->pembeli);

        $portRes = $this->get('/portal');
        $portRes->assertStatus(200);
    }

    public function test_role_based_access_control(): void
    {
        // Pembeli cannot access POS, inventory, reports, settings
        $this->actingAs($this->pembeli);

        $this->get('/pos')->assertStatus(403);
        $this->get('/inventory')->assertStatus(403);
        $this->get('/reports')->assertStatus(403);
        $this->get('/settings')->assertStatus(403);

        // Gudang cannot access POS, reports, settings
        $this->actingAs($this->gudang);
        $this->get('/pos')->assertStatus(403);
        $this->get('/reports')->assertStatus(403);
        $this->get('/settings')->assertStatus(403);

        // Kasir cannot access reports, settings, roles, or void
        $this->actingAs($this->kasir);
        $this->get('/reports')->assertStatus(403);
        $this->get('/settings')->assertStatus(403);
        $this->get('/roles')->assertStatus(403);
        $this->postJson('/api/roles', ['name' => 'Bad User'])->assertStatus(403);
        $this->postJson('/api/transactions/void', ['transaction_id' => 1, 'void_reason' => 'Coba void kasir'])->assertStatus(403);
    }

    public function test_cashier_customer_registration_enforces_pembeli_and_zero_credit(): void
    {
        $this->actingAs($this->kasir);

        $username = 'cust' . rand(10000, 99999);
        // Kasir attempts to inject role 'owner' and credit_limit 999999999
        $res = $this->postJson('/api/customers/register', [
            'name' => 'Pelanggan Uji Kasir',
            'username' => $username,
            'password' => 'secret123',
            'phone' => '08123456789',
            'role' => 'owner',
            'credit_limit' => 999999999,
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $user = User::where('username', $username)->first();
        $this->assertNotNull($user);
        // Assert role is strictly 'pembeli' and credit_limit is strictly 0.00
        $this->assertEquals('pembeli', $user->role);
        $this->assertEquals(0.00, (float)$user->credit_limit);
    }

    public function test_owner_exclusive_void_authorization(): void
    {
        $product = Product::where('is_active', 1)->where('stock', '>=', 2)->first();
        $this->actingAs($this->owner);

        // Buat transaksi nota
        $transRes = $this->postJson('/api/pos/checkout', [
            'items' => [['id' => $product->id, 'qty' => 1]],
            'payment_method' => 'tunai',
            'paid_amount' => (float)$product->retail_price,
        ]);
        $transRes->assertStatus(200);
        $txId = $transRes->json('transaction_id');

        // Kasir dilarang mengeksekusi void
        $this->actingAs($this->kasir);
        $voidKasir = $this->postJson('/api/transactions/void', [
            'transaction_id' => $txId,
            'void_reason' => 'Kasir coba void nota',
        ]);
        $voidKasir->assertStatus(403);

        // Owner diizinkan mengeksekusi void
        $this->actingAs($this->owner);
        $voidOwner = $this->postJson('/api/transactions/void', [
            'transaction_id' => $txId,
            'void_reason' => 'Pembeli membatalkan pesanan di kasir',
        ]);
        $voidOwner->assertStatus(200);
        $voidOwner->assertJson(['success' => true]);
    }

    public function test_stock_opname_physical_stock(): void
    {
        $this->actingAs($this->gudang);

        $product = Product::where('is_active', 1)->first();
        $this->assertNotNull($product);

        $newActualStock = $product->stock + 5;

        $res = $this->postJson('/api/inventory/set-physical-stock', [
            'product_id' => $product->id,
            'new_stock' => $newActualStock,
            'notes' => 'Stock opname audit fisik',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $product->refresh();
        $this->assertEquals($newActualStock, $product->stock);
    }

    public function test_security_non_owner_cannot_switch_role(): void
    {
        $this->actingAs($this->kasir);
        $res = $this->post('/switch-role', ['role' => 'owner']);
        $res->assertStatus(403);
    }

    public function test_security_deactivated_user_is_logged_out(): void
    {
        $deactivated = User::factory()->create(['role' => 'kasir', 'is_active' => 0]);
        $this->actingAs($deactivated);

        $res = $this->get('/pos');
        $res->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_security_zero_credit_limit_cannot_take_debt(): void
    {
        $this->actingAs($this->kasir);
        $product = Product::where('is_active', 1)->where('stock', '>=', 1)->first();

        $zeroCreditBuyer = User::factory()->create([
            'role' => 'pembeli',
            'is_active' => 1,
            'credit_limit' => 0,
        ]);

        $res = $this->postJson('/api/pos/checkout', [
            'items' => [['id' => $product->id, 'qty' => 1]],
            'payment_method' => 'hutang',
            'customer_id' => $zeroCreditBuyer->id,
            'paid_amount' => 0,
        ]);

        $res->assertStatus(400);
        $res->assertJson(['success' => false]);
    }

    public function test_security_csv_export_formula_sanitization(): void
    {
        $this->actingAs($this->owner);

        // Create transaction with dangerous formula name
        Transaction::create([
            'invoice_number' => 'INV-TEST-CSV-01',
            'cashier_id' => $this->owner->id,
            'customer_name_manual' => '=cmd|"/c calc"!A0',
            'transaction_date' => now(),
            'subtotal' => 10000,
            'total_discount' => 0,
            'grand_total' => 10000,
            'paid_amount' => 10000,
            'change_amount' => 0,
            'payment_method' => 'tunai',
            'payment_status' => 'lunas',
            'created_at' => now(),
        ]);

        $res = $this->get('/reports/export-csv');
        $res->assertStatus(200);

        ob_start();
        $res->sendContent();
        $csvContent = ob_get_clean();

        // Must be prefixed with single quote
        $this->assertStringContainsString("'=cmd|", $csvContent);
    }
}
