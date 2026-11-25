<?php

namespace App\Services;

use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ReceivableService
{
    public function recordPayment(int $receivableId, float $amountPaid, string $paymentMethod, string $refNote, string $notes, User $receivedBy): array
    {
        if ($amountPaid <= 0) {
            throw new Exception("Nominal pembayaran harus lebih besar dari 0.");
        }

        if (!in_array($paymentMethod, ['tunai', 'qris'], true)) {
            throw new Exception("Metode pembayaran harus Tunai atau QRIS.");
        }

        return DB::transaction(function () use ($receivableId, $amountPaid, $paymentMethod, $refNote, $notes, $receivedBy) {
            $rec = Receivable::where('id', $receivableId)->lockForUpdate()->first();
            if (!$rec) {
                throw new Exception("Data piutang tidak ditemukan.");
            }

            if ($rec->status === 'void') {
                throw new Exception("Tagihan ini telah dibatalkan (VOID).");
            }

            if ($rec->status === 'lunas' || (float)$rec->remaining_debt <= 0) {
                throw new Exception("Tagihan piutang ini sudah berstatus LUNAS.");
            }

            if ($amountPaid > (float)$rec->remaining_debt) {
                $sisaStr = 'Rp ' . number_format((float)$rec->remaining_debt, 0, ',', '.');
                throw new Exception("Nominal pembayaran melebihi sisa tagihan piutang ({$sisaStr}).");
            }

            $datePrefix = date('Ymd');
            $lastPay = ReceivablePayment::where('payment_number', 'like', 'PAY-' . $datePrefix . '-%')
                ->orderBy('id', 'desc')
                ->value('payment_number');

            $seq = 1;
            if ($lastPay && preg_match('/PAY-\d+-(\d+)/', $lastPay, $matches)) {
                $seq = (int)$matches[1] + 1;
            }
            $paymentNumber = 'PAY-' . $datePrefix . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);

            while (ReceivablePayment::where('payment_number', $paymentNumber)->exists()) {
                $seq++;
                $paymentNumber = 'PAY-' . $datePrefix . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
            }

            $payment = ReceivablePayment::create([
                'receivable_id' => $rec->id,
                'payment_number' => $paymentNumber,
                'received_by' => $receivedBy->id,
                'payment_date' => now(),
                'amount_paid' => $amountPaid,
                'payment_method' => $paymentMethod,
                'reference_note' => $refNote,
                'notes' => $notes,
                'created_at' => now(),
            ]);

            $newTotalPaid = (float)$rec->total_paid + $amountPaid;
            $newRemainingDebt = (float)$rec->total_debt - $newTotalPaid;
            $newStatus = ($newRemainingDebt <= 0) ? 'lunas' : 'belum_lunas';

            $rec->update([
                'total_paid' => $newTotalPaid,
                'remaining_debt' => max(0, $newRemainingDebt),
                'status' => $newStatus,
            ]);

            // Sinkronisasi status nota transaksi kasir jika cicilan telah melunasi piutang
            $trans = Transaction::where('id', $rec->transaction_id)->first();
            if ($trans) {
                if ($newStatus === 'lunas') {
                    $trans->update([
                        'payment_status' => 'lunas',
                        'paid_amount' => $trans->grand_total,
                    ]);
                } else {
                    $trans->update([
                        'paid_amount' => min((float)$trans->grand_total, (float)$trans->paid_amount + $amountPaid),
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Pembayaran piutang berhasil dicatat dengan nomor kuitansi ' . $paymentNumber . '.',
                'payment_number' => $paymentNumber,
                'amount_paid' => $amountPaid,
                'remaining_debt' => max(0, $newRemainingDebt),
                'status' => $newStatus,
            ];
        });
    }
}
