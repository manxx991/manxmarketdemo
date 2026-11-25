@extends('layouts.app')

@section('title', 'Laporan Penjualan & Keuangan - MANXMARKET')
@section('page_title', 'Laporan Penjualan & Analitik Keuangan')

@section('content')
<div class="flex flex-col gap-6 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-5 border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight">Laporan Penjualan &amp; Analitik Keuangan</h1>
        <p class="text-xs text-text-muted mt-1">Ringkasan omzet bersih, HPP, estimasi laba kotor, dan analisis metode pembayaran kasir.</p>
    </div>

    <!-- Filter & Action Toolbar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="flex items-center bg-background-canvas dark:bg-slate-800 p-1 rounded-xl border border-border-subtle dark:border-slate-700">
                <a href="{{ route('reports.index', ['period' => 'today']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $period === 'today' ? 'bg-primary dark:bg-blue-600 text-white shadow-xs' : 'text-text-muted hover:text-text-main dark:hover:text-white' }}">Harian</a>
                <a href="{{ route('reports.index', ['period' => 'this_week']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $period === 'this_week' ? 'bg-primary dark:bg-blue-600 text-white shadow-xs' : 'text-text-muted hover:text-text-main dark:hover:text-white' }}">Mingguan</a>
                <a href="{{ route('reports.index', ['period' => 'this_month']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $period === 'this_month' ? 'bg-primary dark:bg-blue-600 text-white shadow-xs' : 'text-text-muted hover:text-text-main dark:hover:text-white' }}">Bulanan</a>
            </div>

            <div class="flex items-center gap-2 text-xs text-text-muted">
                <label for="reportStartDate" class="sr-only">Dari tanggal</label>
                <input type="date" id="reportStartDate" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 text-text-main dark:text-white text-xs font-mono outline-none focus:border-blue-500">
                <span>s/d</span>
                <label for="reportEndDate" class="sr-only">Sampai tanggal</label>
                <input type="date" id="reportEndDate" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 text-text-main dark:text-white text-xs font-mono outline-none focus:border-blue-500">
                <input type="hidden" name="period" value="custom">
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer">
                    Filter
                </button>
            </div>
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.exportCsv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 hover:bg-background-subtle dark:hover:bg-slate-700 border border-border-subtle dark:border-slate-700 text-xs font-semibold text-text-main dark:text-white transition-colors shadow-xs cursor-pointer">
                <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-emerald-400">table_chart</span>
                <span>Unduh CSV</span>
            </a>
            <button type="button" onclick="window.print()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-surface-white dark:bg-slate-800 hover:bg-background-subtle dark:hover:bg-slate-700 border border-border-subtle dark:border-slate-700 text-xs font-semibold text-text-main dark:text-white transition-colors shadow-xs cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>Cetak Nota / PDF</span>
            </button>
        </div>
    </div>

    <!-- Executive KPI Row: 5 Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Omzet Bersih -->
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Omzet Bersih</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">{{ formatRupiah($stats['net_omzet']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">{{ number_format($stats['trans_count'] ?? 0) }} transaksi berhasil</div>
            </div>
        </div>

        <!-- 2. Diskon Grosir -->
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-amber-700 dark:text-amber-400 tracking-wider">Diskon Grosir</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">sell</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-amber-700 dark:text-amber-400 font-mono">-{{ formatRupiah($stats['total_discount']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Potongan otomatis grosir</div>
            </div>
        </div>

        <!-- 3. Total HPP (Modal) -->
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Total Beban HPP</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-slate-700 dark:text-slate-300 font-mono">{{ formatRupiah($stats['total_hpp']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Modal pokok barang terjual</div>
            </div>
        </div>

        <!-- 4. Estimasi Laba Kotor -->
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-emerald-700 dark:text-emerald-400 tracking-wider">Laba Kotor Toko</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">trending_up</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-emerald-700 dark:text-emerald-400 font-mono">{{ formatRupiah($stats['gross_profit']) }}</div>
                <div class="text-[11px] text-emerald-800 dark:text-emerald-300 font-bold mt-0.5">Margin: {{ number_format((float)($stats['profit_margin'] ?? 0), 1) }}%</div>
            </div>
        </div>

        <!-- 5. Piutang Tertagih -->
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Cicilan Piutang</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-amber-700 dark:text-amber-400 font-mono">{{ formatRupiah($stats['collected_receivables']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Kas masuk cicilan piutang</div>
            </div>
        </div>
    </div>

    <!-- Analytics Double Column -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top 5 Best-Selling Products -->
        <div class="lg:col-span-2 bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden flex flex-col transition-colors">
            <div class="p-4 border-b border-border-subtle dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-bold text-sm text-text-main dark:text-white">5 Produk Terlaris Periode Ini</h2>
                <span class="text-xs text-text-muted font-mono">Berdasarkan Volume Qty</span>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                            <th class="p-3">SKU</th>
                            <th class="p-3">Nama Produk</th>
                            <th class="p-3 text-right">Volume Terjual</th>
                            <th class="p-3 text-right">Nilai Omzet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                        @forelse($topProducts as $tp)
                            <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 font-mono text-text-muted">{{ $tp->sku_snapshot }}</td>
                                <td class="p-3 font-semibold text-text-main dark:text-white">{{ $tp->product_name_snapshot }}</td>
                                <td class="p-3 text-right font-mono font-bold text-secondary dark:text-blue-400">{{ number_format($tp->total_qty) }}</td>
                                <td class="p-3 text-right font-mono font-semibold text-text-main dark:text-white">{{ formatRupiah($tp->total_sales) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-text-muted">
                                    <div class="flex flex-col items-center justify-center gap-1">
                                        <span class="material-symbols-outlined text-[32px] text-slate-300 dark:text-slate-600">receipt_long</span>
                                        <span class="font-medium">Belum ada data transaksi pada rentang periode ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden flex flex-col transition-colors">
            <div class="p-4 border-b border-border-subtle dark:border-slate-800">
                <h2 class="font-bold text-sm text-text-main dark:text-white">Distribusi Metode Pembayaran</h2>
            </div>
            <div class="p-4 flex flex-col divide-y divide-border-subtle dark:divide-slate-800">
                <!-- Tunai -->
                <div class="py-3 first:pt-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-semibold text-xs uppercase text-text-main dark:text-white">Tunai (Cash)</span>
                        </div>
                        <span class="font-mono font-bold text-xs text-text-main dark:text-white">{{ formatRupiah($stats['tunai_total']) }}</span>
                    </div>
                </div>

                <!-- QRIS -->
                <div class="py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="font-semibold text-xs uppercase text-text-main dark:text-white">QRIS Dinamis</span>
                        </div>
                        <span class="font-mono font-bold text-xs text-text-main dark:text-white">{{ formatRupiah($stats['qris_total']) }}</span>
                    </div>
                </div>

                <!-- Tempo / Hutang -->
                <div class="py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-semibold text-xs uppercase text-text-main dark:text-white">Kasbon / Tempo</span>
                        </div>
                        <span class="font-mono font-bold text-xs text-text-main dark:text-white">{{ formatRupiah($stats['hutang_total']) }}</span>
                    </div>
                </div>

                <!-- Piutang Terkumpul -->
                <div class="py-3 last:pb-0 bg-emerald-50/50 dark:bg-emerald-950/30 -mx-4 px-4 rounded-b-xl">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-xs uppercase text-emerald-800 dark:text-emerald-300">Cicilan Kasbon Diterima</span>
                        <span class="font-mono font-bold text-xs text-emerald-700 dark:text-emerald-400">{{ formatRupiah($stats['collected_receivables']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Audit Table -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-border-subtle dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-sm sm:text-base text-text-main dark:text-white">Audit Transaksi Terakhir</h2>
                <p class="text-[11px] text-text-muted mt-0.5">Daftar transaksi penjualan terkini pada sistem</p>
            </div>
        </div>
        <div class="overflow-x-auto focus-visible:outline-2 focus-visible:outline-blue-600 dark:focus-visible:outline-blue-400" tabindex="0" role="region" aria-label="Tabel audit transaksi terbaru">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/60 text-text-muted uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3.5">No. Faktur</th>
                        <th class="p-3.5">Waktu</th>
                        <th class="p-3.5">Kasir</th>
                        <th class="p-3.5">Pelanggan</th>
                        <th class="p-3.5">Metode Bayar</th>
                        <th class="p-3.5 text-right">Grand Total</th>
                        <th class="p-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($recentTransactions as $t)
                        <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors">
                            <td class="p-3.5 font-mono font-bold text-text-main dark:text-white">{{ $t->invoice_number }}</td>
                            <td class="p-3.5 text-text-muted dark:text-slate-400 font-mono">{{ $t->transaction_date ? \Carbon\Carbon::parse($t->transaction_date)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="p-3.5 text-text-main dark:text-slate-200">{{ $t->cashier->name ?? 'Kasir' }}</td>
                            <td class="p-3.5 text-text-main dark:text-slate-200">{{ $t->customer_name_manual ?: ($t->customer->name ?? 'Pelanggan Umum') }}</td>
                            <td class="p-3.5 uppercase font-semibold text-[11px]">{{ $t->payment_method }}</td>
                            <td class="p-3.5 text-right font-mono font-bold text-text-main dark:text-white">{{ formatRupiah($t->grand_total) }}</td>
                            <td class="p-3.5 text-center">
                                @if($t->payment_status === 'lunas')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">Lunas</span>
                                @elseif($t->payment_status === 'belum_lunas')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">Tempo</span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300">Void</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-text-muted">
                                Tidak ada transaksi terbaru pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3.5 sm:p-4 bg-background-canvas dark:bg-slate-800/40 border-t border-border-subtle dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs text-text-muted">Menampilkan {{ count($recentTransactions) }} transaksi terbaru</span>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 hover:bg-background-subtle dark:hover:bg-slate-700 text-xs font-semibold text-text-main dark:text-white shadow-2xs transition-colors cursor-pointer">
                <span>Buka Riwayat Lengkap</span>
                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
            </a>
        </div>
    </div>
</div>
@endsection
