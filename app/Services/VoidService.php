<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Receivable;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class VoidService
{
    public function executeVoid(int $transactionId, string $voidReason, User $voidUser): array
    {
        if ($voidUser->role !== 'owner') {
            throw new Exception("Hanya Owner yang berwenang mengeksekusi pembatalan nota (VOID).");
        }

        $voidReason = trim($voidReason);
        if (strlen($voidReason) < 5) {
            throw new Exception("Alasan pembatalan (void) wajib diisi dengan jelas (minimal 5 karakter).");
        }

        return DB::transaction(function () use ($transactionId, $voidReason, $voidUser) {
            $trans = Transaction::where('id', $transactionId)->lockForUpdate()->first();
            if (!$trans) {
                throw new Exception("Data transaksi tidak ditemukan.");
            }

            if ($trans->payment_status === 'void') {
                throw new Exception("Transaksi nota {$trans->invoice_number} sudah berstatus VOID sebelumnya.");
            }

            $rec = Receivable::where('transaction_id', $transactionId)->first();
            if ($rec && (float)$rec->total_paid > 0) {
                $paidStr = 'Rp ' . number_format((float)$rec->total_paid, 0, ',', '.');
                throw new Exception("Transaksi tidak dapat di-VOID karena piutang tempo telah memiliki riwayat pembayaran cicilan sebesar {$paidStr}.");
            }

            $items = $trans->items;
            foreach ($items as $it) {
                $product = Product::where('id', $it->product_id)->lockForUpdate()->first();
                if ($product) {
                    $stockBefore = (int)$product->stock;
                    $stockAfter = $stockBefore + (int)$it->qty;

                    $product->update(['stock' => $stockAfter]);

                    StockMovement::create([
                        'product_id' => $it->product_id,
                        'user_id' => $voidUser->id,
                        'reference_id' => $trans->id,
                        'movement_type' => 'void_return',
                        'qty_change' => (int)$it->qty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'notes' => "Restok Pembatalan Void Nota " . $trans->invoice_number . " (Alasan: " . $voidReason . ")",
                        'created_at' => now(),
                    ]);
                }
            }

            if ($rec) {
                $rec->update(['status' => 'void']);
            }

            $trans->update([
                'payment_status' => 'void',
                'void_by' => $voidUser->id,
                'void_reason' => $voidReason,
                'void_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Transaksi nota {$trans->invoice_number} berhasil dibatalkan (VOID) dan stok dikembalikan ke gudang.",
                'invoice_number' => $trans->invoice_number,
            ];
        });
    }
}
