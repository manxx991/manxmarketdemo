<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Models\Transaction;
use App\Services\VoidService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $paymentMethod = $request->input('payment_method', 'all');
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $query = Transaction::with(['cashier', 'customer', 'voidUser', 'items']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name_manual', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status !== 'all') {
            $query->where('payment_status', $status);
        }

        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total_count' => Transaction::count(),
            'lunas_count' => Transaction::where('payment_status', 'lunas')->count(),
            'belum_lunas_count' => Transaction::where('payment_status', 'belum_lunas')->count(),
            'void_count' => Transaction::where('payment_status', 'void')->count(),
            'total_omzet' => (float)Transaction::where('payment_status', '!=', 'void')->sum('grand_total'),
        ];

        $store = StoreSetting::allSettings();

        return view('transactions.index', compact('transactions', 'stats', 'search', 'status', 'paymentMethod', 'startDate', 'endDate', 'store'));
    }

    public function detail($id)
    {
        $trans = Transaction::with(['cashier', 'customer', 'voidUser', 'items'])->find($id);
        if (!$trans) {
            return response()->json(['success' => false, 'message' => 'Data transaksi tidak ditemukan.'], 404);
        }

        $items = $trans->items->map(function ($it) {
            return [
                'name' => $it->product_name_snapshot,
                'sku' => $it->sku_snapshot,
                'qty' => $it->qty,
                'unit_price' => (float)$it->unit_price,
                'discount_per_item' => (float)$it->discount_per_item,
                'subtotal_item' => (float)$it->subtotal_item,
            ];
        });

        $store = StoreSetting::whereIn('setting_key', [
            'store_name', 'store_address', 'store_phone', 'receipt_footer',
        ])->pluck('setting_value', 'setting_key')->all();

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $trans->id,
                'invoice_number' => $trans->invoice_number,
                'date' => $trans->transaction_date->format('d/m/Y H:i'),
                'cashier_name' => $trans->cashier->name ?? 'Kasir',
                'customer_name' => $trans->customer_name_manual,
                'payment_method' => $trans->payment_method,
                'payment_status' => $trans->payment_status,
                'subtotal' => (float)$trans->subtotal,
                'total_discount' => (float)$trans->total_discount,
                'grand_total' => (float)$trans->grand_total,
                'paid_amount' => (float)$trans->paid_amount,
                'change_amount' => (float)$trans->change_amount,
                'void_by' => $trans->voidUser->name ?? null,
                'void_reason' => $trans->void_reason,
                'void_at' => $trans->void_at ? $trans->void_at->format('d/m/Y H:i') : null,
                'items' => $items,
                'store' => $store,
            ],
            'store' => $store,
        ]);
    }

    public function void(Request $request, VoidService $voidService)
    {
        $user = Auth::user();
        if ($user->role !== 'owner') {
            return response()->json(['success' => false, 'message' => 'Hanya Owner yang memiliki hak akses pembatalan (void).'], 403);
        }

        $transactionId = (int)$request->input('transaction_id');
        $voidReason = $request->input('void_reason', '');

        try {
            $result = $voidService->executeVoid($transactionId, $voidReason, $user);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
