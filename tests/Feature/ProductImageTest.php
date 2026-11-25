<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\WholesaleRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('product_assets');
        $this->actingAs(User::factory()->create(['role' => 'owner', 'is_active' => 1]));
    }

    /** @dataProvider acceptedFormats */
    public function test_product_upload_stores_random_path_and_returns_public_url(string $format, int $size): void
    {
        $file = $this->image($format)->size($size);
        $response = $this->saveProduct(['image' => $file])->assertOk()->assertJson(['success' => true]);
        $path = $response->json('product.image_path');
        $this->assertIsString($path);
        $this->assertStringStartsWith('assets/produk/', $path);
        Storage::disk('product_assets')->assertExists($path);
        $this->assertSame($file->get(), Storage::disk('product_assets')->get($path));
        $this->assertDatabaseHas('products', ['sku' => 'PHOTO-01', 'image_path' => $path]);
        $this->assertSame(asset($path), $response->json('product.image_url'));
    }


    public static function acceptedFormats(): array
    {
        return [['png', 2048], ['jpg', 1], ['jpeg', 1], ['webp', 1]];
    }

    public function test_rejects_unsupported_formats_including_non_image_with_png_name(): void
    {
        $gif = $this->image('gif');
        $text = UploadedFile::fake()->createWithContent('notes.txt', 'Bukan gambar.');
        $svg = UploadedFile::fake()->createWithContent('product.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');
        foreach ([$gif, $svg, new UploadedFile($text->getPathname(), 'product.png', 'image/png', UPLOAD_ERR_OK, true)] as $file) {
            $this->saveProduct(['image' => $file])->assertUnprocessable()->assertJsonValidationErrors('image');
        }
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('product_assets')->allFiles());
    }

    public function test_rejects_upload_larger_than_two_megabytes(): void
    {
        $this->saveProduct(['image' => $this->image()->size(2049)])
            ->assertUnprocessable()->assertJsonPath('errors.image.0', 'Ukuran gambar maksimal 2 MB.');
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('product_assets')->allFiles());
    }

    public function test_product_without_image_stores_null_and_ignores_client_path(): void
    {
        $this->saveProduct(['image_path' => 'products/not-uploaded.png'])->assertOk();
        $this->assertDatabaseHas('products', ['sku' => 'PHOTO-01', 'image_path' => null]);
        $this->assertSame([], Storage::disk('product_assets')->allFiles());
    }

    public function test_uploaded_image_url_uses_current_host_instead_of_configured_localhost(): void
    {
        $path = 'products/test-image.webp';
        Storage::disk('public')->put($path, 'test image');
        $product = new Product(['sku' => 'PHOTO-URL', 'image_path' => $path]);
        URL::forceRootUrl('http://manxmarket.test');

        try {
            $this->assertSame('http://manxmarket.test/storage/'.$path, $product->image_url);
        } finally {
            URL::forceRootUrl(null);
        }
    }

    public function test_legacy_image_remains_fallback_when_upload_is_missing(): void
    {
        $asset = 'assets/produk/brs-pdn-5k.webp';
        $product = Product::create(['sku' => 'BRS-PDN-5K', 'name' => 'Beras Uji']);
        $this->assertFileExists(public_path($asset));
        $this->assertSame(asset($asset), $product->image_url);
        $product->image_path = 'products/missing.png';
        $this->assertSame(asset($asset), $product->image_url);
    }

    public function test_inventory_pos_and_buyers_see_uploaded_image(): void
    {
        $response = $this->saveProduct(['image' => $this->image(), 'stock' => 3])->assertOk();
        $url = $response->json('product.image_url');

        $this->get('/inventory')->assertOk()->assertSee('src="'.$url.'"', false);
        $cashier = User::factory()->create(['role' => 'kasir', 'is_active' => 1]);
        $pos = $this->actingAs($cashier)->get('/pos')->assertOk()->assertSee('src="'.$url.'"', false);
        $pos->assertSee('"image_url":'.json_encode($url), false);
        $buyer = User::factory()->create(['role' => 'pembeli', 'is_active' => 1]);
        $this->actingAs($buyer)->get('/portal')->assertOk()->assertSee('src="'.$url.'"', false);
    }

    public function test_replacement_removes_previous_upload_without_touching_legacy_asset(): void
    {
        $sku = 'BRS-PDN-5K';
        $legacyFile = public_path('assets/produk/brs-pdn-5k.webp');
        $legacyHash = hash_file('sha256', $legacyFile);
        $product = $this->saveProduct(['sku' => $sku, 'image' => $this->image()])->assertOk()->json('product');
        $replacement = $this->saveProduct([
            'id' => $product['id'], 'sku' => $sku, 'image' => $this->image('webp'),
        ])->assertOk()->json('product.image_path');

        $this->assertNotSame($product['image_path'], $replacement);
        $this->assertDatabaseHas('products', ['id' => $product['id'], 'image_path' => $replacement]);
        Storage::disk('product_assets')->assertExists($replacement);
        Storage::disk('product_assets')->assertMissing($product['image_path']);
        $this->assertSame($legacyHash, hash_file('sha256', $legacyFile));
    }

    public function test_edit_without_upload_keeps_image_stock_and_existing_wholesale_rule(): void
    {
        $product = $this->saveProduct([
            'image' => $this->image(), 'stock' => 3,
            'has_wholesale' => '1', 'ws_min_qty' => 2,
            'ws_discount_type' => 'fixed_amount', 'ws_discount_value' => 1000,
        ])->assertOk()->json('product');
        $this->saveProduct(['id' => $product['id'], 'name' => 'Nama Baru', 'stock' => 999])->assertOk();

        $this->assertDatabaseHas('products', [
            'id' => $product['id'], 'name' => 'Nama Baru', 'stock' => 3, 'image_path' => $product['image_path'],
        ]);
        $this->assertDatabaseHas('wholesale_rules', ['product_id' => $product['id'], 'discount_value' => 1000]);
        Storage::disk('product_assets')->assertExists($product['image_path']);
    }

    public function test_invalid_wholesale_rule_creates_no_image_or_product(): void
    {
        $this->saveProduct([
            'image' => $this->image(), 'has_wholesale' => '1',
            'ws_min_qty' => 2, 'ws_discount_type' => 'percentage', 'ws_discount_value' => 101,
        ])->assertUnprocessable()->assertJsonValidationErrors('ws_discount_value');
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('product_assets')->allFiles());
    }

    public function test_rejected_replacement_keeps_old_file_and_product(): void
    {
        $product = $this->saveProduct(['image' => $this->image()])->assertOk()->json('product');
        $this->saveProduct(['id' => $product['id'], 'image' => $this->image('gif')])
            ->assertUnprocessable()->assertJsonValidationErrors('image');
        $this->assertDatabaseHas('products', ['id' => $product['id'], 'image_path' => $product['image_path']]);
        $this->assertSame([$product['image_path']], Storage::disk('product_assets')->allFiles('assets/produk'));
    }

    public function test_only_inventory_roles_can_upload_images(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir', 'is_active' => 1]));
        $this->saveProduct(['image' => $this->image()])->assertForbidden();
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('product_assets')->allFiles());
        $this->actingAs(User::factory()->create(['role' => 'gudang', 'is_active' => 1]));
        $this->saveProduct(['image' => $this->image()])->assertOk();
    }

    /** @dataProvider productWriteModes */
    public function test_database_failure_cleans_new_image_and_keeps_previous_upload(bool $updating): void
    {
        $product = $this->saveProduct(['image' => $this->image()])->assertOk()->json('product');
        $dispatcher = WholesaleRule::getEventDispatcher();
        WholesaleRule::setEventDispatcher(clone $dispatcher);
        WholesaleRule::creating(static function () {
            throw new \RuntimeException('Simulated rule persistence failure.');
        });
        try {
            $payload = [
                'sku' => 'PHOTO-02', 'name' => 'Nama Baru', 'stock' => 2, 'image' => $this->image('webp'),
                'has_wholesale' => '1', 'ws_min_qty' => 2,
                'ws_discount_type' => 'percentage', 'ws_discount_value' => 10,
            ];
            if ($updating) $payload['id'] = $product['id'];
            $this->saveProduct($payload)->assertStatus(500);
        } finally {
            WholesaleRule::setEventDispatcher($dispatcher);
        }

        $this->assertDatabaseHas('products', [
            'id' => $product['id'], 'name' => $product['name'], 'image_path' => $product['image_path'],
        ]);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertSame([$product['image_path']], Storage::disk('product_assets')->allFiles('assets/produk'));
    }

    public static function productWriteModes(): array
    {
        return [[false], [true]];
    }

    private function saveProduct(array $attributes = []): \Illuminate\Testing\TestResponse
    {
        return $this->post('/api/inventory/product', $attributes + [
            'sku' => 'PHOTO-01', 'name' => 'Produk Uji Gambar', 'retail_price' => 10000,
        ], ['Accept' => 'application/json']);
    }

    private function image(string $format = 'png'): UploadedFile
    {
        $base64 = match ($format) {
            'png' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAADElEQVQImWNgYGAAAAAEAAGjChXjAAAAAElFTkSuQmCC',
            'gif' => 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'webp' => 'UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJaQAA3AA/v02aAA=',
            'jpg', 'jpeg' => '/9j/4AAQSkZJRgABAQEAYABgAAD//gA+Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2ODApLCBkZWZhdWx0IHF1YWxpdHkK/9sAQwAIBgYHBgUIBwcHCQkICgwUDQwLCwwZEhMPFB0aHx4dGhwcICQuJyAiLCMcHCg3KSwwMTQ0NB8nOT04MjwuMzQy/9sAQwEJCQkMCwwYDQ0YMiEcITIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy/8AAEQgAAQABAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A+f6KKKAP/9k=',
        };

        return UploadedFile::fake()->createWithContent('product.'.$format, base64_decode($base64));
    }
}
