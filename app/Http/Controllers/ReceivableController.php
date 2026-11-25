<?php

namespace App\Http\Controllers;

use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\StoreSetting;
use App\Services\QrisService;
use App\Services\ReceivableService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $status = 'belum_lunas';
        $search = trim($request->input('search', ''));

        $query = Receivable::with(['customer', 'transaction', 'payments'])
            ->where('status', 'belum_lunas');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('transaction', function ($tq) use ($search) {
                    $tq->where('invoice_number', 'like', "%{$search}%");
                });
            });
        }

        $receivables = $query->orderBy('due_date', 'asc')->paginate(10)->withQueryString();

        $stats = [
            'total_active_debt' => (float)Receivable::where('status', 'belum_lunas')->sum('remaining_debt'),
            'total_customers_with_debt' => Receivable::where('status', 'belum_lunas')->distinct('customer_id')->count('customer_id'),
            'unpaid_count' => Receivable::where('status', 'belum_lunas')->count(),
            'paid_count' => Receivable::where('status', 'lunas')->count(),
            'total_collected' => (float)ReceivablePayment::sum('amount_paid'),
        ];

        $store = StoreSetting::allSettings();

        return view('receivables.index', compact('receivables', 'stats', 'status', 'search', 'store'));
    }

    public function pay(Request $request, ReceivableService $receivableService)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['owner', 'kasir'], true)) {
            return response()->json(['success' => false, 'message' => 'Hanya Kasir atau Owner yang berwenang mencatat pembayaran piutang.'], 403);
        }

        $receivableId = (int)$request->input('receivable_id');
        $amountPaid = (float)$request->input('amount_paid');
        $paymentMethod = $request->input('payment_method', 'tunai');
        $refNote = trim($request->input('reference_note', ''));
        $notes = trim($request->input('notes', ''));

        try {
            $result = $receivableService->recordPayment($receivableId, $amountPaid, $paymentMethod, $refNote, $notes, $user);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function history($id)
    {
        $rec = Receivable::with(['customer', 'transaction', 'payments.receiver'])->find($id);
        if (!$rec) {
            return response()->json(['success' => false, 'message' => 'Data piutang tidak ditemukan.'], 404);
        }

        $user = Auth::user();
        if ($user->role === 'pembeli' && (int)$rec->customer_id !== (int)$user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda tidak berhak melihat riwayat piutang ini.'], 403);
        }

        $payments = $rec->payments->map(function ($p) {
            return [
                'id' => $p->id,
                'payment_number' => $p->payment_number,
                'date' => $p->payment_date->format('d/m/Y H:i'),
                'payment_date' => $p->payment_date->format('d/m/Y H:i'),
                'amount_paid' => (float)$p->amount_paid,
                'payment_method' => $p->payment_method,
                'reference_note' => $p->reference_note,
                'notes' => $p->notes,
                'receiver_name' => $p->receiver->name ?? 'Kasir',
                'received_by_name' => $p->receiver->name ?? 'Kasir',
            ];
        });

        $paymentsSum = (float)$rec->payments->sum('amount_paid');
        $downPayment = max(0, (float)$rec->total_paid - $paymentsSum);

        return response()->json([
            'success' => true,
            'receivable' => [
                'id' => $rec->id,
                'invoice_number' => $rec->transaction->invoice_number ?? '-',
                'transaction_date' => $rec->transaction && $rec->transaction->transaction_date ? $rec->transaction->transaction_date->format('d/m/Y H:i') : '-',
                'customer_name' => $rec->customer->name ?? '-',
                'phone' => $rec->customer->phone ?? '-',
                'total_debt' => (float)$rec->total_debt,
                'down_payment' => $downPayment,
                'total_paid' => (float)$rec->total_paid,
                'remaining_debt' => (float)$rec->remaining_debt,
                'due_date' => $rec->due_date ? $rec->due_date->format('d/m/Y') : '-',
                'status' => $rec->status,
            ],
            'payments' => $payments,
        ]);
    }
}
