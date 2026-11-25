<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WholesaleRule;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function adjustStock(int $productId, mixed $qtyChange, string $movementType, string $notes, User $user): array
    {
        if (!in_array($movementType, ['in', 'adjustment'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['movement_type' => 'Jenis mutasi manual tidak valid.']);
        }
        if ((!is_int($qtyChange) && !is_string($qtyChange))
            || filter_var($qtyChange, FILTER_VALIDATE_INT, ['options' => ['min_range' => -2147483647, 'max_range' => 2147483647]]) === false
            || (int) $qtyChange === 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['qty_change' => 'Jumlah perubahan stok wajib berupa bilangan bulat bukan nol.']);
        }
        $qtyChange = (int) $qtyChange;

        return DB::transaction(function () use ($productId, $qtyChange, $movementType, $notes, $user) {
            $product = Product::where('id', $productId)->lockForUpdate()->first();
            if (!$product) {
                throw new Exception("Produk tidak ditemukan.");
            }

            $stockBefore = (int)$product->stock;
            $stockAfter = $stockBefore + $qtyChange;

            if ($stockAfter < 0) {
                throw new Exception("Koreksi stok gagal. Stok akhir tidak boleh kurang dari 0 (Sisa saat ini: {$stockBefore}).");
            }

            $product->update(['stock' => $stockAfter]);

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'reference_id' => null,
                'movement_type' => $movementType,
                'qty_change' => $qtyChange,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes ?: 'Koreksi Stok Manual',
                'created_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Stok {$product->name} berhasil diperbarui menjadi {$stockAfter}.",
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ];
        });
    }

    public function setPhysicalStock(int $productId, mixed $newStock, string $notes, User $user): array
    {
        if ((!is_int($newStock) && !is_string($newStock))
            || filter_var($newStock, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]) === false) {
            throw \Illuminate\Validation\ValidationException::withMessages(['new_stock' => 'Stok fisik wajib berupa bilangan bulat nonnegatif yang valid.']);
        }
        $newStock = (int) $newStock;

        return DB::transaction(function () use ($productId, $newStock, $notes, $user) {
            $product = Product::where('id', $productId)->lockForUpdate()->first();
            if (!$product) {
                throw new Exception("Produk tidak ditemukan.");
            }

            $stockBefore = (int)$product->stock;
            $qtyChange = $newStock - $stockBefore;

            $product->update(['stock' => $newStock]);

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'reference_id' => null,
                'movement_type' => 'adjustment',
                'qty_change' => $qtyChange,
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'notes' => $notes ?: 'Opname Fisik Langsung',
                'created_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Sisa stok {$product->name} berhasil disesuaikan menjadi {$newStock}.",
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'delta' => $qtyChange,
            ];
        });
    }
}
