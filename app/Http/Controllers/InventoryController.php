<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\WholesaleRule;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'products');
        $search = trim($request->input('search', ''));
        $categorySlug = $request->input('category', 'all');

        $query = Product::with(['category', 'activeWholesaleRule']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categorySlug !== 'all') {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $products = $query->orderBy('name', 'asc')->paginate(10, ['*'], 'p_page')->withQueryString();

        $moveSearch = trim($request->input('move_search', ''));
        $moveType = $request->input('move_type', 'all');
        $moveStart = $request->input('move_start_date', '');
        $moveEnd = $request->input('move_end_date', '');

        $moveQuery = StockMovement::with(['product', 'user']);

        if ($moveSearch) {
            $moveQuery->where(function ($q) use ($moveSearch) {
                $q->where('notes', 'like', "%{$moveSearch}%")
                  ->orWhereHas('product', function ($pq) use ($moveSearch) {
                      $pq->where('name', 'like', "%{$moveSearch}%")
                         ->orWhere('sku', 'like', "%{$moveSearch}%")
                         ->orWhere('barcode', 'like', "%{$moveSearch}%");
                  });
            });
        }

        if ($moveType !== 'all') {
            $moveQuery->where('movement_type', $moveType);
        }

        if ($moveStart) {
            $moveQuery->whereDate('created_at', '>=', $moveStart);
        }
        if ($moveEnd) {
            $moveQuery->whereDate('created_at', '<=', $moveEnd);
        }

        $movements = $moveQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'm_page')->withQueryString();

        $categories = Category::withCount('products')->orderBy('name', 'asc')->get();
        $allProducts = Product::where('is_active', 1)->orderBy('name', 'asc')->get(['id', 'name', 'sku', 'stock', 'unit']);

        $stats = [
            'total_sku' => Product::where('is_active', 1)->count(),
            'total_stock_physical' => (int)Product::where('is_active', 1)->sum('stock'),
            'low_stock_count' => Product::where('is_active', 1)->whereRaw('stock <= min_stock')->count(),
            'total_in_qty' => (int)StockMovement::whereIn('movement_type', ['in', 'void_return'])->sum('qty_change'),
            'total_out_qty' => abs((int)StockMovement::where('movement_type', 'sale')->sum('qty_change')),
        ];

        return view('inventory.index', compact('products', 'allProducts', 'movements', 'categories', 'stats', 'tab', 'search', 'categorySlug', 'moveSearch', 'moveType', 'moveStart', 'moveEnd'));
    }

    public function storeProduct(Request $request)
    {
        $id = $request->input('id');
        $validated = $request->validate([
            'sku' => 'required|string|max:60',
            'name' => 'required|string|max:200',
            'retail_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:30',
            'category_id' => 'nullable|exists:categories,id',
            'barcode' => 'nullable|string|max:60',
            'image' => 'bail|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'image.image' => 'File harus berupa gambar JPG/JPEG, PNG, atau WebP.',
            'image.mimes' => 'Format gambar harus JPG/JPEG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'image.uploaded' => 'Gambar gagal diunggah. Pilih JPG/JPEG, PNG, atau WebP maksimal 2 MB.',
        ]);

        $sku = strtoupper(trim($validated['sku']));
        $unit = trim($validated['unit'] ?? 'pcs') ?: 'pcs';
        $minStock = (int)($validated['min_stock'] ?? 5);
        $costPrice = (float)($validated['cost_price'] ?? 0);
        $retailPrice = (float)$validated['retail_price'];
        $categoryId = !empty($validated['category_id']) ? (int)$validated['category_id'] : null;
        $barcode = trim($validated['barcode'] ?? '');
        $writeWholesale = $request->boolean('wholesale_configured') || $request->has('has_wholesale');
        $hasWholesale = $request->boolean('has_wholesale');
        $wholesale = [];
        if ($hasWholesale) {
            $ruleMessage = "Aturan grosir produk '{$validated['name']}' tidak valid (:attribute).";
            $wholesale = \Illuminate\Support\Facades\Validator::make($request->only([
                'ws_min_qty', 'ws_discount_type', 'ws_discount_value',
            ]), [
                'ws_min_qty' => 'required|integer|min:2|max:2147483647',
                'ws_discount_type' => 'required|in:fixed_amount,percentage,special_price',
                'ws_discount_value' => [
                    'required', 'numeric', 'min:0',
                    function ($attribute, $value, $fail) use ($request, $retailPrice, $ruleMessage) {
                        if (!is_numeric($value) || !is_finite((float) $value)
                            || ($request->input('ws_discount_type') === 'percentage' && (float) $value > 100)
                            || ($request->input('ws_discount_type') === 'fixed_amount' && (float) $value > $retailPrice)) {
                            $fail($ruleMessage);
                        }
                    },
                ],
            ], [
                'ws_min_qty.*' => $ruleMessage,
                'ws_discount_type.*' => $ruleMessage,
                'ws_discount_value.*' => $ruleMessage,
            ])->validate();
        }

        $attributes = [
            'category_id' => $categoryId,
            'sku' => $sku,
            'barcode' => $barcode,
            'name' => trim($validated['name']),
            'unit' => $unit,
            'cost_price' => $costPrice,
            'retail_price' => $retailPrice,
            'min_stock' => $minStock,
        ];
        $newImagePath = null;
        $oldImagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = $image->hashName();
            $newImagePath = 'assets/produk/'.$fileName;
            try {
                $stored = $image->storeAs('assets/produk', $fileName, 'product_assets');
            } catch (Throwable $e) {
                report($e);
                $stored = false;
            }
            if ($stored === false) {
                $this->deleteProductImage($newImagePath);
                throw ValidationException::withMessages(['image' => 'Gambar gagal disimpan. Silakan coba lagi.']);
            }
            $attributes['image_path'] = $newImagePath;
        }

        $stock = (int) ($validated['stock'] ?? 0);
        try {
            $product = DB::transaction(function () use ($id, $attributes, $stock, $writeWholesale, $hasWholesale, $wholesale, &$oldImagePath) {
                if ($id) {
                    $product = Product::where('id', $id)->lockForUpdate()->firstOrFail();
                    $oldImagePath = $product->image_path;
                    $product->update($attributes);
                } else {
                    $product = Product::create(array_merge($attributes, [
                        'stock' => $stock, 'is_active' => 1, 'created_at' => now(),
                    ]));

                    if ($stock > 0) {
                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => Auth::id(),
                            'reference_id' => null,
                            'movement_type' => 'in',
                            'qty_change' => $stock,
                            'stock_before' => 0,
                            'stock_after' => $stock,
                            'notes' => 'Stok Awal Input Produk Baru',
                            'created_at' => now(),
                        ]);
                    }
                }

                if ($writeWholesale) {
                    WholesaleRule::where('product_id', $product->id)->delete();
                }
                if ($hasWholesale) {
                    WholesaleRule::create([
                        'product_id' => $product->id,
                        'rule_type' => 'per_item_qty',
                        'min_qty' => (int) $wholesale['ws_min_qty'],
                        'min_amount' => 0.00,
                        'discount_type' => $wholesale['ws_discount_type'],
                        'discount_value' => (float) $wholesale['ws_discount_value'],
                        'is_active' => 1,
                        'created_at' => now(),
                    ]);
                }
                return $product;
            });
        } catch (Throwable $e) {
            if ($newImagePath !== null) {
                $this->deleteProductImage($newImagePath);
            }
            throw $e;
        }

        if ($newImagePath !== null && $oldImagePath && $oldImagePath !== $newImagePath) {
            DB::afterCommit(fn () => $this->deleteProductImage($oldImagePath));
        }

        return response()->json(['success' => true, 'message' => 'Data produk berhasil disimpan.', 'product' => $product]);
    }

    private function deleteProductImage(string $path): void
    {
        $prefix = Str::startsWith($path, 'assets/produk/') ? 'assets/produk/' : 'products/';
        if (!Str::startsWith($path, $prefix)
            || basename($path) !== substr($path, strlen($prefix))
            || !preg_match('/\A[A-Za-z0-9]{40}\.(?:jpe?g|png|webp)\z/i', basename($path))
            || !in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return;
        }
        $disk = $prefix === 'assets/produk/' ? 'product_assets' : 'public';
        try {
            if (!Storage::disk($disk)->delete($path)) {
                report(new \RuntimeException('Gambar produk gagal dihapus: '.$path));
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);

        $baseSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $cat = Category::create([
            'name' => $name,
            'slug' => $slug,
            'description' => trim($validated['description'] ?? ''),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan.', 'category' => $cat]);
    }

    public function adjustStock(Request $request, StockService $stockService)
    {
        $productId = (int)$request->input('product_id');
        $qtyChange = $request->input('qty_change', $request->input('qty'));
        if ((!is_int($qtyChange) && !is_string($qtyChange))
            || filter_var($qtyChange, FILTER_VALIDATE_INT, ['options' => ['min_range' => -2147483647, 'max_range' => 2147483647]]) === false
            || (int) $qtyChange === 0) {
            return response()->json(['success' => false, 'message' => 'Jumlah perubahan stok wajib berupa bilangan bulat bukan nol.'], 422);
        }
        $qtyChange = (int) $qtyChange;
        $movementType = $request->input('movement_type', $request->input('type', 'adjustment'));
        $notes = trim($request->input('notes', ''));

        if (!in_array($movementType, ['in', 'out', 'adjustment'], true)) {
            return response()->json(['success' => false, 'message' => 'Jenis mutasi manual tidak valid.'], 422);
        }
        if ($movementType === 'in') {
            $qtyChange = abs($qtyChange);
        } elseif ($movementType === 'out') {
            $qtyChange = -abs($qtyChange);
            $movementType = 'adjustment';
        }

        try {
            $result = $stockService->adjustStock($productId, $qtyChange, $movementType, $notes, Auth::user());
            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function setPhysicalStock(Request $request, StockService $stockService)
    {
        $productId = (int)$request->input('product_id');
        $newStock = $request->input('new_stock', $request->input('actual_stock'));
        $notes = trim($request->input('notes', ''));

        try {
            $result = $stockService->setPhysicalStock($productId, $newStock, $notes, Auth::user());
            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function stockHistory($id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        $movements = StockMovement::with('user')
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $totalIn = (int)StockMovement::where('product_id', $id)->whereIn('movement_type', ['in', 'void_return'])->sum('qty_change');
        $totalOut = abs((int)StockMovement::where('product_id', $id)->where('movement_type', 'sale')->sum('qty_change'));

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'unit' => $product->unit,
                'stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
            ],
            'movements' => $movements->map(function ($m) {
                return [
                    'id' => $m->id,
                    'created_at' => $m->created_at->format('d/m/Y H:i'),
                    'type' => $m->movement_type,
                    'qty_change' => $m->qty_change,
                    'stock_before' => $m->stock_before,
                    'stock_after' => $m->stock_after,
                    'notes' => $m->notes,
                    'user_name' => $m->user->name ?? 'System',
                ];
            }),
        ]);
    }
}
