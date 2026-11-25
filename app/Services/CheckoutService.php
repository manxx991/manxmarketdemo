<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Receivable;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\WholesaleRule;
use Exception;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function processCheckout(array $data, User $cashier): array
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw new Exception("Keranjang belanja kosong atau data tidak valid.");
        }

        \Illuminate\Support\Facades\Validator::make(['items' => $items], [
            'items' => 'required|array|min:1',
            'items.*' => 'required|array',
        ])->validate();

        $seenProducts = [];
        foreach ($items as &$item) {
            foreach (['product_id' => $item['product_id'] ?? $item['id'] ?? null, 'qty' => $item['qty'] ?? null] as $field => $value) {
                $max = $field === 'qty' ? 2147483647 : PHP_INT_MAX;
                if ((!is_int($value) && !is_string($value))
                    || filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $max]]) === false) {
                    throw \Illuminate\Validation\ValidationException::withMessages(["items.$field" => "$field wajib berupa bilangan bulat positif yang valid."]);
                }
                $item[$field] = (int) $value;
            }
            $productId = $item['product_id'];
            if (isset($seenProducts[$productId])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['items' => 'Produk yang sama tidak boleh muncul lebih dari sekali.']);
            }
            $seenProducts[$productId] = true;
        }
        unset($item);

        $paymentMethod = $data['payment_method'] ?? 'tunai';
        $customerId = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
        $customerNameManual = trim($data['customer_name_manual'] ?? '');
        $paidAmount = $data['paid_amount'] ?? 0;
        if ($paymentMethod === 'hutang' && is_string($paidAmount) && trim($paidAmount) === '') {
            $paidAmount = 0;
        }
        if (!is_numeric($paidAmount) || !is_finite((float) $paidAmount) || (float) $paidAmount < 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['paid_amount' => 'Nominal pembayaran wajib berupa angka nonnegatif yang valid.']);
        }
        $paidAmount = (float) $paidAmount;
        $dueDate = !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d', strtotime('+14 days'));

        if (!in_array($paymentMethod, ['tunai', 'qris', 'hutang'], true)) {
            throw new Exception("Metode pembayaran tidak valid.");
        }

        if ($paymentMethod === 'hutang' && !$customerId) {
            throw new Exception("Transaksi tempo/hutang wajib memilih Pembeli Terdaftar.");
        }

        return DB::transaction(function () use ($items, $paymentMethod, $customerId, $customerNameManual, $paidAmount, $dueDate, $cashier) {
            $customer = null;
            if ($customerId) {
                $customer = User::where('id', $customerId)
                    ->where('role', 'pembeli')
                    ->where('is_active', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$customer) {
                    throw new Exception("Data pelanggan tidak ditemukan atau berstatus nonaktif.");
                }
            }

            $processedItems = [];
            $costPriceSnapshots = [];
            $subtotal = 0.00;
            $totalDiscount = 0.00;

            foreach ($items as $it) {
                $pId = $it['product_id'];
                $qty = $it['qty'];

                $product = Product::where('id', $pId)->lockForUpdate()->first();
                if (!$product) {
                    throw new Exception("Produk dengan ID {$pId} tidak ditemukan di sistem.");
                }

                if ($product->stock < $qty) {
                    throw new Exception("Stok produk '{$product->name}' tidak mencukupi. Sisa stok: {$product->stock} {$product->unit}, diminta: {$qty}.");
                }

                $unitPrice = (float)$product->retail_price;
                $costPrice = (float)$product->cost_price;
                $costPriceSnapshots[$product->id] = $costPrice;
                $discountPerItem = 0.00;

                $rules = WholesaleRule::where('product_id', $pId)
                    ->where('is_active', 1)
                    ->orderBy('min_qty', 'desc')
                    ->get();

                foreach ($rules as $r) {
                    if ($r->rule_type === 'per_item_qty' && $qty >= (int)$r->min_qty) {
                        $discountValue = $r->getRawOriginal('discount_value');
                        if (filter_var($r->getRawOriginal('min_qty'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 2]]) === false
                            || !in_array($r->discount_type, ['fixed_amount', 'percentage', 'special_price'], true)
                            || !is_numeric($discountValue) || !is_finite((float) $discountValue) || (float) $discountValue < 0
                            || ($r->discount_type === 'fixed_amount' && (float) $discountValue > $unitPrice)
                            || ($r->discount_type === 'percentage' && (float) $discountValue > 100)) {
                            throw \Illuminate\Validation\ValidationException::withMessages(['items' => "Aturan grosir produk '{$product->name}' tidak valid."]);
                        }
                        $discountValue = (float) $discountValue;
                        if ($r->discount_type === 'fixed_amount') {
                            $discountPerItem = $discountValue;
                        } elseif ($r->discount_type === 'percentage') {
                            $discountPerItem = ($unitPrice * $discountValue) / 100.0;
                        } elseif ($r->discount_type === 'special_price') {
                            $discountPerItem = max(0, $unitPrice - $discountValue);
                        }
                        break;
                    }
                }

                $itemSubtotal = ($unitPrice - $discountPerItem) * $qty;
                $subtotal += ($unitPrice * $qty);
                $totalDiscount += ($discountPerItem * $qty);

                $processedItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $unitPrice,
                    'discount_per_item' => $discountPerItem,
                    'qty' => $qty,
                    'subtotal_item' => $itemSubtotal,
                ];
            }

            if (empty($processedItems)) {
                throw new Exception("Tidak ada item yang valid untuk diproses.");
            }

            $grandTotal = $subtotal - $totalDiscount;

            $changeAmount = 0.00;
            $paymentStatus = 'lunas';
            $receivableDebt = 0.00;

            if ($paymentMethod === 'tunai') {
                if ($paidAmount < $grandTotal) {
                    throw new Exception("Uang tunai diterima (Rp " . number_format($paidAmount, 0, ',', '.') . ") kurang dari total belanja (Rp " . number_format($grandTotal, 0, ',', '.') . ").");
                }
                $changeAmount = $paidAmount - $grandTotal;
                $paymentStatus = 'lunas';
            } elseif ($paymentMethod === 'qris') {
                $paidAmount = $grandTotal;
                $changeAmount = 0.00;
                $paymentStatus = 'lunas';
            } elseif ($paymentMethod === 'hutang') {
                if ($paidAmount >= $grandTotal) {
                    $paymentStatus = 'lunas';
                    $changeAmount = $paidAmount - $grandTotal;
                } else {
                    $paymentStatus = 'belum_lunas';
                    $receivableDebt = $grandTotal - $paidAmount;

                    $creditLimit = (float)$customer->credit_limit;
                    if ($creditLimit <= 0) {
                        throw new Exception("Pelanggan '{$customer->name}' tidak memiliki limit plafon kredit kasbon.");
                    }

                    $currentDebt = (float)Receivable::where('customer_id', $customerId)
                        ->where('status', 'belum_lunas')
                        ->sum('remaining_debt');

                    if (($currentDebt + $receivableDebt) > $creditLimit) {
                        $plafonStr = 'Rp ' . number_format($creditLimit, 0, ',', '.');
                        $currDebtStr = 'Rp ' . number_format($currentDebt, 0, ',', '.');
                        $reqStr = 'Rp ' . number_format($receivableDebt, 0, ',', '.');
                        throw new Exception("Batas kredit pelanggan terlampaui! Plafon: {$plafonStr}, Hutang Aktif: {$currDebtStr}, Tambahan Hutang: {$reqStr}.");
                    }
                }
            }

            $datePrefix = date('Ymd');
            $lastInv = Transaction::where('invoice_number', 'like', 'INV-' . $datePrefix . '-%')
                ->orderBy('id', 'desc')
                ->value('invoice_number');

            $seq = 1;
            if ($lastInv && preg_match('/INV-\d+-(\d+)/', $lastInv, $matches)) {
                $seq = (int)$matches[1] + 1;
            }
            $invoiceNumber = 'INV-' . $datePrefix . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);

            while (Transaction::where('invoice_number', $invoiceNumber)->exists()) {
                $seq++;
                $invoiceNumber = 'INV-' . $datePrefix . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
            }

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'cashier_id' => $cashier->id,
                'customer_id' => $customerId,
                'customer_name_manual' => $customerNameManual ?: ($customer->name ?? 'Pelanggan Umum'),
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'total_discount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'created_at' => now(),
            ]);

            foreach ($processedItems as $pItem) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $pItem['product_id'],
                    'product_name_snapshot' => $pItem['name'],
                    'sku_snapshot' => $pItem['sku'],
                    'qty' => $pItem['qty'],
                    'cost_price_snapshot' => $costPriceSnapshots[$pItem['product_id']],
                    'unit_price' => $pItem['unit_price'],
                    'discount_per_item' => $pItem['discount_per_item'],
                    'subtotal_item' => $pItem['subtotal_item'],
                ]);

                $product = Product::where('id', $pItem['product_id'])->lockForUpdate()->firstOrFail();
                $stockBefore = (int) $product->stock;
                $stockAfter = $stockBefore - $pItem['qty'];

                $product->update(['stock' => $stockAfter]);

                StockMovement::create([
                    'product_id' => $pItem['product_id'],
                    'user_id' => $cashier->id,
                    'reference_id' => $transaction->id,
                    'movement_type' => 'sale',
                    'qty_change' => -$pItem['qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => "Penjualan Kasir Nota " . $invoiceNumber,
                    'created_at' => now(),
                ]);
            }

            if ($paymentMethod === 'hutang' && $receivableDebt > 0) {
                Receivable::create([
                    'transaction_id' => $transaction->id,
                    'customer_id' => $customerId,
                    'total_debt' => $grandTotal,
                    'total_paid' => $paidAmount,
                    'remaining_debt' => $receivableDebt,
                    'due_date' => $dueDate,
                    'status' => 'belum_lunas',
                    'created_at' => now(),
                ]);
            }

            $store = \App\Models\StoreSetting::whereIn('setting_key', [
                'store_name', 'store_address', 'store_phone', 'receipt_footer',
            ])->pluck('setting_value', 'setting_key')->all();

            $transPayload = [
                'id' => $transaction->id,
                'invoice_number' => $invoiceNumber,
                'date' => $transaction->transaction_date->format('d/m/Y H:i'),
                'cashier' => $cashier->name,
                'customer' => $transaction->customer_name_manual,
                'payment_method' => strtoupper($paymentMethod),
                'payment_status' => strtoupper($paymentStatus),
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'due_date' => ($paymentMethod === 'hutang' && $receivableDebt > 0) ? date('d/m/Y', strtotime($dueDate)) : null,
                'remaining_debt' => $receivableDebt,
                'items' => $processedItems,
                'store' => $store,
            ];

            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'invoice_number' => $invoiceNumber,
                'transaction' => $transPayload,
            ];
        });
    }
}
