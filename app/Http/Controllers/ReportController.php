<?php

namespace App\Http\Controllers;

use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        switch ($period) {
            case 'today':
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d');
                break;
            case 'this_week':
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate = date('Y-m-d', strtotime('sunday this week'));
                break;
            case 'this_month':
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                break;
            case 'custom':
                $startDate = $startDate ?: date('Y-m-01');
                $endDate = $endDate ?: date('Y-m-d');
                break;
            default:
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                break;
        }

        $baseTransQuery = Transaction::where('payment_status', '!=', 'void')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        $grossSubtotal = (float)(clone $baseTransQuery)->sum('subtotal');
        $totalDiscount = (float)(clone $baseTransQuery)->sum('total_discount');
        $netOmzet = (float)(clone $baseTransQuery)->sum('grand_total');
        $transCount = (clone $baseTransQuery)->count();

        $validTransIds = (clone $baseTransQuery)->pluck('id');
        $totalHpp = (float)TransactionItem::whereIn('transaction_id', $validTransIds)
            ->selectRaw('SUM(cost_price_snapshot * qty) as total_hpp')
            ->value('total_hpp');

        $grossProfit = $netOmzet - $totalHpp;
        $profitMargin = $netOmzet > 0 ? ($grossProfit / $netOmzet) * 100 : 0.0;

        $pmStats = (clone $baseTransQuery)
            ->select('payment_method', DB::raw('SUM(grand_total) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $tunaiTotal = (float)($pmStats['tunai']->total_amount ?? 0);
        $qrisTotal = (float)($pmStats['qris']->total_amount ?? 0);
        $hutangTotal = (float)($pmStats['hutang']->total_amount ?? 0);

        $collectedReceivables = (float)ReceivablePayment::whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->sum('amount_paid');

        $topProducts = TransactionItem::whereIn('transaction_id', $validTransIds)
            ->select('product_name_snapshot', 'sku_snapshot', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal_item) as total_sales'))
            ->groupBy('product_name_snapshot', 'sku_snapshot')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();

        $recentTransactions = (clone $baseTransQuery)
            ->with(['cashier', 'customer'])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'gross_subtotal' => $grossSubtotal,
            'total_discount' => $totalDiscount,
            'net_omzet' => $netOmzet,
            'total_hpp' => $totalHpp,
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'trans_count' => $transCount,
            'tunai_total' => $tunaiTotal,
            'qris_total' => $qrisTotal,
            'hutang_total' => $hutangTotal,
            'collected_receivables' => $collectedReceivables,
        ];

        return view('reports.index', compact('stats', 'topProducts', 'recentTransactions', 'period', 'startDate', 'endDate'));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $transactions = Transaction::where('payment_status', '!=', 'void')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->with(['cashier', 'customer', 'items'])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"laporan_penjualan_{$startDate}_{$endDate}.csv\"",
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No. Faktur', 'Tanggal', 'Kasir', 'Pelanggan', 'Metode Bayar', 'Status', 'Subtotal', 'Diskon', 'Grand Total']);

            $sanitize = function ($val) {
                $str = (string)$val;
                if (isset($str[0]) && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                    return "'" . $str;
                }
                return $str;
            };

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $sanitize($t->invoice_number),
                    $t->transaction_date->format('Y-m-d H:i:s'),
                    $sanitize($t->cashier->name ?? 'Kasir'),
                    $sanitize($t->customer_name_manual),
                    strtoupper($t->payment_method),
                    strtoupper($t->payment_status),
                    $t->subtotal,
                    $t->total_discount,
                    $t->grand_total,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
