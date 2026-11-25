@extends('layouts.app')

@section('title', 'Portal Pembeli - MANXMARKET')
@section('page_title', 'Portal Pembeli')

@section('content')
<div class="flex flex-col gap-6 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-5 border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight">{{ $customer->name ?? 'Portal Pembeli' }}</h1>
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-200 dark:border-blue-900/40 font-mono font-semibold">
                    {{ '@' . ($customer->username ?? 'user') }}
                </span>
            </div>
            <p class="text-xs text-text-muted mt-1">Informasi akun pembeli, saldo plafon kredit, riwayat faktur kasbon, dan katalog harga produk.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(Auth::user()->role === 'owner' && count($allCustomers) > 0)
                <div class="relative min-w-[220px]">
                    <label for="portalCustomerSelect" class="sr-only">Pilih Profil Akun Pembeli</label>
                    <select id="portalCustomerSelect" aria-label="Pilih Profil Akun Pembeli" onchange="window.location.href='{{ route('portal.index') }}?customer_id=' + this.value" class="w-full pl-3 pr-8 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 transition-all shadow-xs">
                        @foreach($allCustomers as $ac)
                            <option value="{{ $ac->id }}" {{ $customer->id === $ac->id ? 'selected' : '' }}>
                                {{ $ac->name }} (@ {{ $ac->username }})
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                </div>
            @endif
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                Pelanggan Terdaftar
            </span>
        </div>
    </div>

    @if(Auth::user()->role === 'owner' && count($allCustomers) > 0)
        <!-- Box Data Seluruh Pembeli -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
            <div class="p-4 border-b border-border-subtle dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-primary dark:text-blue-400">group</span>
                    <h2 class="font-bold text-sm text-text-main dark:text-white">Daftar Semua Pembeli Toko ({{ count($allCustomers) }})</h2>
                </div>
                <span class="text-xs text-text-muted">Gunakan dropdown pemilih akun di atas untuk mengganti profil aktif</span>
            </div>
            <div class="overflow-x-auto focus-visible:outline-2 focus-visible:outline-blue-600 dark:focus-visible:outline-blue-400" tabindex="0" role="region" aria-label="Tabel daftar seluruh pembeli">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                            <th class="p-3">Nama Pembeli</th>
                            <th class="p-3 font-mono">Username / Kontak</th>
                            <th class="p-3 text-center">Total Transaksi</th>
                            <th class="p-3 text-right">Plafon Kredit</th>
                            <th class="p-3 text-right">Kasbon Aktif</th>
                            <th class="p-3 text-center">Status Akun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                        @foreach($allCustomers as $c)
                            @php
                                $isSelected = $customer->id === $c->id;
                            @endphp
                            <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors {{ $isSelected ? 'bg-blue-50/60 dark:bg-blue-950/30' : '' }}">
                                <td class="p-3">
                                    <div class="font-semibold text-text-main dark:text-white flex items-center gap-1.5">
                                        @if($isSelected)
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        @endif
                                        <span>{{ $c->name }}</span>
                                    </div>
                                </td>
                                <td class="p-3 font-mono">
                                    <div class="text-text-main dark:text-slate-300">@ {{ $c->username }}</div>
                                    <div class="text-[11px] text-text-muted">{{ $c->phone ?: '-' }}</div>
                                </td>
                                <td class="p-3 text-center font-mono font-semibold text-text-main dark:text-white">
                                    {{ number_format($c->customer_transactions_count ?? 0) }} kali
                                </td>
                                <td class="p-3 text-right font-mono font-semibold text-text-main dark:text-white">
                                    {{ formatRupiah($c->credit_limit) }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold {{ ($c->total_active_debt ?? 0) > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                    {{ formatRupiah($c->total_active_debt ?? 0) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $c->is_active ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40' : 'bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-900/40' }}">
                                        {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- 3 Bento KPI Cards: Plafon & Debt -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Plafon Kredit Belanja</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">credit_card</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">{{ formatRupiah($creditLimit) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Batas maksimal hutang tempo toko</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Total Kasbon Belum Lunas</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold font-mono {{ $totalUnpaid > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400' }}">{{ formatRupiah($totalUnpaid) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">{{ $totalUnpaid > 0 ? 'Tagihan aktif perlu pelunasan' : 'Tidak ada tagihan tertunggak' }}</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Sisa Saldo Plafon</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-emerald-700 dark:text-emerald-400 font-mono">{{ formatRupiah($remainingCredit) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Dapat digunakan untuk belanja tempo</div>
            </div>
        </div>
    </div>

    <!-- Active Receivables / Debt Invoices -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-4 border-b border-border-subtle dark:border-slate-800 flex items-center justify-between">
            <h2 class="font-bold text-sm text-text-main dark:text-white">Daftar Tagihan Kasbon &amp; Jatuh Tempo Akun Ini</h2>
            <span class="text-xs text-text-muted">{{ count($myReceivables) }} faktur tercatat</span>
        </div>
        <div class="overflow-x-auto focus-visible:outline-2 focus-visible:outline-blue-600 dark:focus-visible:outline-blue-400" tabindex="0" role="region" aria-label="Tabel tagihan kasbon akun ini">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3">No. Nota Faktur</th>
                        <th class="p-3 font-mono">Batas Jatuh Tempo</th>
                        <th class="p-3 text-right">Nilai Belanja</th>
                        <th class="p-3 text-right">Telah Dicicil</th>
                        <th class="p-3 text-right">Sisa Tagihan</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Cicilan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($myReceivables as $r)
                        @php
                            $isOverdue = $r->status === 'belum_lunas' && $r->due_date && \Carbon\Carbon::parse($r->due_date)->isPast();
                            $payCount = $r->payments ? $r->payments->count() : 0;
                        @endphp
                        <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors">
                            <td class="p-3 font-mono font-bold text-text-main dark:text-white whitespace-nowrap">{{ $r->transaction->invoice_number ?? '-' }}</td>
                            <td class="p-3 font-mono whitespace-nowrap {{ $isOverdue ? 'text-red-700 dark:text-red-400 font-bold' : 'text-text-muted dark:text-slate-400' }}">
                                {{ $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('d/m/Y') : '-' }}
                                @if($isOverdue)
                                    <span class="block text-[10px] text-red-600 dark:text-red-400 font-semibold">Melewati batas!</span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-mono text-text-main dark:text-white whitespace-nowrap">{{ formatRupiah($r->total_debt) }}</td>
                            <td class="p-3 text-right font-mono text-emerald-700 dark:text-emerald-400 whitespace-nowrap">{{ formatRupiah($r->total_paid) }}</td>
                            <td class="p-3 text-right font-mono font-bold whitespace-nowrap {{ $r->remaining_debt > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-text-muted dark:text-slate-400' }}">{{ formatRupiah($r->remaining_debt) }}</td>
                            <td class="p-3 text-center whitespace-nowrap">
                                @if($r->status === 'lunas')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40">LUNAS</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">BELUM LUNAS</span>
                                @endif
                            </td>
                            <td class="p-3 text-center whitespace-nowrap">
                                <button type="button" onclick="openPaymentHistory({{ $r->id }}, '{{ $r->transaction->invoice_number ?? '-' }}')" class="px-2.5 py-1 rounded-lg border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 hover:bg-background-canvas dark:hover:bg-slate-700 text-xs font-semibold text-text-main dark:text-white inline-flex items-center gap-1 shadow-2xs transition-colors cursor-pointer" title="Lihat Riwayat Cicilan">
                                    <span class="material-symbols-outlined text-[15px] text-blue-600 dark:text-blue-400">receipt_long</span>
                                    <span>{{ $payCount }}x</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-text-muted">
                                Tidak ada catatan tagihan kasbon pada akun pembeli ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Transaksi Belanja -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-4 border-b border-border-subtle dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-primary dark:text-blue-400">shopping_cart_checkout</span>
                <h2 class="font-bold text-sm text-text-main dark:text-white">Riwayat Belanja</h2>
            </div>
            <span class="text-xs text-text-muted">{{ count($myTransactions) }} transaksi belanja terakhir akun ini</span>
        </div>
        <div class="overflow-x-auto focus-visible:outline-2 focus-visible:outline-blue-600 dark:focus-visible:outline-blue-400" tabindex="0" role="region" aria-label="Tabel riwayat belanja">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3">No. Nota</th>
                        <th class="p-3 font-mono">Waktu Transaksi</th>
                        <th class="p-3">Kasir</th>
                        <th class="p-3 text-center">Banyak Item</th>
                        <th class="p-3">Metode Bayar</th>
                        <th class="p-3 text-right">Total Belanja</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($myTransactions as $t)
                        @php
                            $totalQty = $t->items ? (int)$t->items->sum('qty') : 0;
                            $itemsCount = $t->items ? $t->items->count() : 0;
                            $isLunas = ($t->payment_status === 'lunas') || ($t->receivable && $t->receivable->status === 'lunas');
                        @endphp
                        <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors {{ $t->payment_status === 'void' ? 'bg-red-50/30 dark:bg-red-950/20' : '' }}">
                            <td class="p-3 font-mono font-bold text-text-main dark:text-white whitespace-nowrap">
                                {{ $t->invoice_number }}
                            </td>
                            <td class="p-3 font-mono text-text-muted dark:text-slate-400 whitespace-nowrap">
                                {{ $t->transaction_date ? $t->transaction_date->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="p-3 text-text-main dark:text-slate-200">
                                {{ $t->cashier->name ?? 'Kasir' }}
                            </td>
                            <td class="p-3 text-center font-mono font-semibold text-text-main dark:text-white whitespace-nowrap">
                                @if($totalQty > 0)
                                    <span>{{ number_format($totalQty) }} pcs</span>
                                    @if($itemsCount > 1)
                                        <span class="block text-[10px] text-text-muted dark:text-slate-400 font-normal">({{ $itemsCount }} SKU)</span>
                                    @endif
                                @else
                                    <span class="text-text-muted">-</span>
                                @endif
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                @if($t->payment_method === 'tunai')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-[10px] font-bold">
                                        <span class="material-symbols-outlined text-[13px] text-emerald-600 dark:text-emerald-400">payments</span>
                                        Tunai
                                    </span>
                                @elseif($t->payment_method === 'qris')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 text-[10px] font-bold">
                                        <span class="material-symbols-outlined text-[13px] text-blue-600 dark:text-blue-400">qr_code_2</span>
                                        QRIS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 text-[10px] font-bold">
                                        <span class="material-symbols-outlined text-[13px] text-amber-600 dark:text-amber-400">event_note</span>
                                        Tempo
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-mono font-bold text-text-main dark:text-white whitespace-nowrap">
                                {{ formatRupiah($t->grand_total) }}
                            </td>
                            <td class="p-3 text-center whitespace-nowrap">
                                @if($t->payment_status === 'void')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-900/40">BATAL / VOID</span>
                                @elseif($isLunas)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40">LUNAS</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">BELUM LUNAS</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-text-muted">
                                Belum ada riwayat transaksi belanja pada akun pembeli ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Katalog Harga Produk & Grosir -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-4 border-b border-border-subtle dark:border-slate-800 flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-primary dark:text-blue-400">storefront</span>
                    <div>
                        <h2 class="font-bold text-sm text-text-main dark:text-white">Katalog Harga Produk &amp; Grosir (<span id="catalogVisibleCount">{{ count($catalog) }}</span>/{{ count($catalog) }} SKU)</h2>
                        <p class="text-[11px] text-text-muted">Daftar harga eceran dan ketentuan diskon grosir aktif berdasarkan kategori</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1 sm:w-56">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                        <input type="text" id="catalogSearchInput" placeholder="Cari produk atau SKU..." oninput="filterCatalog()" class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-xs text-text-main dark:text-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15">
                    </div>
                    <div class="inline-flex rounded-xl bg-background-canvas dark:bg-slate-800 p-0.5 border border-border-subtle dark:border-slate-700 shrink-0" role="group" aria-label="Mode Tampilan Katalog">
                        <button type="button" id="btnViewTable" onclick="setCatalogView('table')" class="px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1 bg-surface-white dark:bg-slate-700 text-text-main dark:text-white shadow-2xs cursor-pointer transition-colors" title="Tampilan Tabel">
                            <span class="material-symbols-outlined text-[15px]">table_rows</span>
                            <span class="hidden sm:inline">Tabel</span>
                        </button>
                        <button type="button" id="btnViewGrid" onclick="setCatalogView('grid')" class="px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1 text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer transition-colors" title="Tampilan Kartu">
                            <span class="material-symbols-outlined text-[15px]">grid_view</span>
                            <span class="hidden sm:inline">Kartu</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Kategori -->
            <div class="flex flex-wrap items-center gap-1.5" id="catalogCategoryTabs">
                <button type="button" data-cat="all" onclick="setCatalogCategory('all', this)" class="cat-pill active px-3 py-1.5 rounded-xl text-[11px] font-bold bg-primary dark:bg-blue-600 text-white shadow-2xs cursor-pointer transition-colors">
                    Semua Kategori ({{ count($catalog) }})
                </button>
                @foreach($categories as $cat)
                    @if(($cat->products_count ?? 0) > 0)
                        <button type="button" data-cat="{{ $cat->slug }}" onclick="setCatalogCategory('{{ $cat->slug }}', this)" class="cat-pill px-3 py-1.5 rounded-xl text-[11px] font-semibold bg-background-canvas dark:bg-slate-800 text-text-muted dark:text-slate-300 hover:text-text-main dark:hover:text-white border border-border-subtle dark:border-slate-700 cursor-pointer transition-colors">
                            {{ $cat->name }} ({{ $cat->products_count }})
                        </button>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Mode Tabel Katalog -->
        <div id="catalogTableWrapper" class="overflow-x-auto focus-visible:outline-2 focus-visible:outline-blue-600 dark:focus-visible:outline-blue-400" tabindex="0" role="region" aria-label="Tabel katalog harga produk">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3">SKU</th>
                        <th class="p-3">Produk</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3 text-center">Satuan</th>
                        <th class="p-3 text-right">Harga Eceran</th>
                        <th class="p-3">Diskon Grosir</th>
                        <th class="p-3 text-right">Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800" id="catalogTableBody">
                    @forelse($catalog as $cp)
                        @php
                            $catSlug = $cp->category->slug ?? 'umum';
                            $catName = $cp->category->name ?? 'Umum';
                            $searchStr = strtolower($cp->name . ' ' . $cp->sku . ' ' . $catName);
                        @endphp
                        <tr class="catalog-item hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors" data-cat="{{ $catSlug }}" data-search="{{ $searchStr }}">
                            <td class="p-3 font-mono text-[11px] font-semibold text-text-muted dark:text-slate-400 whitespace-nowrap">
                                {{ $cp->sku }}
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-lg bg-white p-1 border border-border-subtle dark:border-slate-700 flex items-center justify-center shrink-0 overflow-hidden">
                                        <img src="{{ $cp->image_url }}" alt="{{ $cp->name }}" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                        <span class="material-symbols-outlined text-[18px] text-slate-400 hidden">inventory_2</span>
                                    </div>
                                    <span class="font-bold text-text-main dark:text-white leading-snug">{{ $cp->name }}</span>
                                </div>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-[10px] font-semibold">
                                    {{ $catName }}
                                </span>
                            </td>
                            <td class="p-3 text-center whitespace-nowrap">
                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-900/40">
                                    {{ $cp->unit }}
                                </span>
                            </td>
                            <td class="p-3 text-right font-mono font-bold text-slate-900 dark:text-emerald-400 whitespace-nowrap">
                                {{ formatRupiah($cp->retail_price) }}
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                @if($cp->activeWholesaleRule)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-wholesale-badge-bg dark:bg-red-950/40 border border-red-200/80 dark:border-red-900/50 text-[10px] text-wholesale-badge-text dark:text-red-300 font-bold">
                                        <span>&ge; {{ $cp->activeWholesaleRule->min_qty }} {{ $cp->unit }}</span>
                                        <span class="font-mono">-{{ formatRupiah($cp->activeWholesaleRule->discount_value) }}/{{ $cp->unit }}</span>
                                    </span>
                                @else
                                    <span class="text-[11px] text-text-muted">Harga Reguler</span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-mono font-semibold whitespace-nowrap {{ $cp->stock > 0 ? 'text-text-main dark:text-slate-200' : 'text-red-600 dark:text-red-400' }}">
                                {{ $cp->stock > 0 ? number_format($cp->stock) . ' ' . $cp->unit : 'Habis' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-text-muted">Katalog produk belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mode Kartu Katalog -->
        <div id="catalogGridWrapper" class="hidden p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($catalog as $cp)
                @php
                    $catSlug = $cp->category->slug ?? 'umum';
                    $catName = $cp->category->name ?? 'Umum';
                    $searchStr = strtolower($cp->name . ' ' . $cp->sku . ' ' . $catName);
                @endphp
                <div class="catalog-item bg-background-canvas dark:bg-slate-800/50 rounded-2xl p-3.5 border border-border-subtle dark:border-slate-700/60 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-600 transition-colors" data-cat="{{ $catSlug }}" data-search="{{ $searchStr }}">
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-slate-200/80 dark:border-slate-700/60">
                                {{ $cp->unit }}
                            </span>
                            <span class="text-[10px] font-mono text-text-muted dark:text-slate-400 font-semibold">
                                {{ $cp->sku }}
                            </span>
                        </div>
                        <div class="w-full h-28 rounded-xl bg-white p-2 border border-border-subtle dark:border-slate-700/60 flex items-center justify-center mb-2.5 overflow-hidden">
                            <img src="{{ $cp->image_url }}" alt="{{ $cp->name }}" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            <span class="material-symbols-outlined text-[32px] text-slate-400 hidden">inventory_2</span>
                        </div>
                        <h3 class="font-bold text-xs text-text-main dark:text-white line-clamp-2 leading-snug">{{ $cp->name }}</h3>
                        <div class="mt-1.5">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-[10px] font-semibold">
                                {{ $catName }}
                            </span>
                        </div>

                        @if($cp->activeWholesaleRule)
                            <div class="bg-wholesale-badge-bg dark:bg-red-950/40 border border-red-200/80 dark:border-red-900/50 px-2 py-1 rounded-xl mt-2 flex items-center justify-between text-[10px] text-wholesale-badge-text dark:text-red-300 font-bold">
                                <span>&ge; {{ $cp->activeWholesaleRule->min_qty }} {{ $cp->unit }}</span>
                                <span class="font-mono">-{{ formatRupiah($cp->activeWholesaleRule->discount_value) }}/{{ $cp->unit }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-border-subtle dark:border-slate-700/60 flex items-baseline justify-between">
                        <span class="text-[10px] uppercase font-bold text-text-muted">Harga Ecer</span>
                        <span class="font-mono font-extrabold text-sm text-slate-900 dark:text-emerald-400">{{ formatRupiah($cp->retail_price) }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-text-muted">Katalog produk belum tersedia.</div>
            @endforelse
        </div>

        <div id="catalogEmptyFiltered" class="hidden p-8 text-center text-xs text-text-muted">
            Tidak ada produk yang sesuai dengan filter kategori atau kata kunci pencarian.
        </div>
    </div>
</div>

<!-- Drawer: Riwayat Cicilan (Slide-over Sheet) -->
<div id="historyModal" class="modal-backdrop hidden fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="histModalTitle">
    <div class="fixed inset-0 bg-slate-900/25 dark:bg-black/50 transition-opacity cursor-pointer" onclick="closeModal('historyModal')"></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-4 sm:pl-10 pointer-events-none">
        <div class="w-screen max-w-md lg:max-w-lg bg-surface-white dark:bg-slate-900 shadow-2xl border-l border-border-subtle dark:border-slate-800 flex flex-col pointer-events-auto transform transition-all">
            <div class="px-5 py-4 border-b border-border-subtle dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="min-w-0 pr-3">
                    <h2 class="font-bold text-sm text-text-main dark:text-white truncate font-mono" id="histModalTitle">Riwayat Cicilan</h2>
                    <p class="text-[11px] text-text-muted dark:text-slate-400 mt-0.5 truncate" id="histModalSubtitle">Detail angsuran pembayaran piutang kasbon</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="hidden sm:inline-block text-[10px] text-text-muted font-mono px-1.5 py-0.5 rounded border border-border-subtle dark:border-slate-800 select-none">ESC</span>
                    <button type="button" onclick="closeModal('historyModal')" aria-label="Tutup Panel" class="w-8 h-8 rounded-lg flex items-center justify-center text-text-muted hover:text-text-main dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            </div>

            <div id="histModalContent" class="p-5 overflow-y-auto flex-1 text-xs text-text-main dark:text-slate-200 flex flex-col gap-4">
                Memuat riwayat...
            </div>

            <div class="px-5 py-3 border-t border-border-subtle dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 flex items-center justify-between shrink-0">
                <span class="text-[11px] font-mono text-text-muted dark:text-slate-400" id="histModalFooterCount"></span>
                <button type="button" onclick="closeModal('historyModal')" class="px-3.5 py-1.5 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-xs font-semibold text-text-main dark:text-white hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Tutup Panel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let activeCatalogCategory = 'all';

function setCatalogCategory(cat, btn) {
    activeCatalogCategory = cat;
    document.querySelectorAll('#catalogCategoryTabs .cat-pill').forEach(el => {
        el.className = 'cat-pill px-3 py-1.5 rounded-xl text-[11px] font-semibold bg-background-canvas dark:bg-slate-800 text-text-muted dark:text-slate-300 hover:text-text-main dark:hover:text-white border border-border-subtle dark:border-slate-700 cursor-pointer transition-colors';
    });
    if (btn) {
        btn.className = 'cat-pill active px-3 py-1.5 rounded-xl text-[11px] font-bold bg-primary dark:bg-blue-600 text-white shadow-2xs cursor-pointer transition-colors';
    }
    filterCatalog();
}

function setCatalogView(mode) {
    const tableWrap = document.getElementById('catalogTableWrapper');
    const gridWrap = document.getElementById('catalogGridWrapper');
    const btnTable = document.getElementById('btnViewTable');
    const btnGrid = document.getElementById('btnViewGrid');

    if (mode === 'grid') {
        tableWrap.classList.add('hidden');
        gridWrap.classList.remove('hidden');
        btnGrid.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1 bg-surface-white dark:bg-slate-700 text-text-main dark:text-white shadow-2xs cursor-pointer transition-colors';
        btnTable.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1 text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer transition-colors';
    } else {
        gridWrap.classList.add('hidden');
        tableWrap.classList.remove('hidden');
        btnTable.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1 bg-surface-white dark:bg-slate-700 text-text-main dark:text-white shadow-2xs cursor-pointer transition-colors';
        btnGrid.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1 text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer transition-colors';
    }
}

function filterCatalog() {
    const query = (document.getElementById('catalogSearchInput')?.value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('#catalogTableBody .catalog-item');
    const cards = document.querySelectorAll('#catalogGridWrapper .catalog-item');
    let visibleCount = 0;

    rows.forEach((el, idx) => {
        const matchCat = activeCatalogCategory === 'all' || el.dataset.cat === activeCatalogCategory;
        const matchQuery = !query || (el.dataset.search || '').includes(query);
        const show = matchCat && matchQuery;
        el.classList.toggle('hidden', !show);
        if (cards[idx]) cards[idx].classList.toggle('hidden', !show);
        if (show) visibleCount++;
    });

    const countEl = document.getElementById('catalogVisibleCount');
    if (countEl) countEl.textContent = visibleCount;

    const emptyEl = document.getElementById('catalogEmptyFiltered');
    if (emptyEl) emptyEl.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
}

function escHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}

async function openPaymentHistory(id, invoice) {
    document.getElementById('histModalTitle').textContent = 'Riwayat Cicilan · ' + (invoice || '-');
    const subEl = document.getElementById('histModalSubtitle');
    if (subEl) subEl.textContent = 'Memuat detail pembayaran...';
    document.getElementById('histModalContent').innerHTML = '<div class="text-center py-12 text-text-muted">Memuat riwayat cicilan...</div>';
    const footerCount = document.getElementById('histModalFooterCount');
    if (footerCount) footerCount.textContent = '';
    openModal('historyModal');

    try {
        const resp = await fetch('{{ url("api/receivables") }}/' + id + '/history');
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);

        const rec = res.receivable || {};
        const inv = rec.invoice_number || invoice || '-';
        document.getElementById('histModalTitle').textContent = 'Riwayat Cicilan · ' + inv;
        if (subEl) {
            subEl.textContent = `${rec.customer_name || 'Pelanggan'} · Jatuh Tempo ${rec.due_date || '-'}`;
        }

        const rem = Number(rec.remaining_debt || 0);
        const totalDebt = Number(rec.total_debt || 0);
        const totalPaid = Number(rec.total_paid || 0);
        const downPayment = Number(rec.down_payment || 0);
        const isLunas = rec.status === 'lunas' || rem <= 0;

        const statusLabel = isLunas
            ? '<span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Lunas</span>'
            : '<span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Belum Lunas</span>';

        const summaryHtml = `
            <div class="pb-4 border-b border-border-subtle dark:border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-text-muted dark:text-slate-400">Ringkasan Tagihan</span>
                    ${statusLabel}
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <span class="text-[11px] text-text-muted dark:text-slate-400 block">Total Faktur</span>
                        <span class="font-mono tabular-nums font-bold text-text-main dark:text-white text-sm block mt-0.5">Rp ${totalDebt.toLocaleString('id-ID')}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-text-muted dark:text-slate-400 block">Telah Dibayar</span>
                        <span class="font-mono tabular-nums font-bold text-text-main dark:text-white text-sm block mt-0.5">Rp ${totalPaid.toLocaleString('id-ID')}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-text-muted dark:text-slate-400 block">Sisa Piutang</span>
                        <span class="font-mono tabular-nums font-bold ${isLunas ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'} text-sm block mt-0.5">Rp ${rem.toLocaleString('id-ID')}</span>
                    </div>
                </div>
            </div>
        `;

        let dpHtml = '';
        if (downPayment > 0) {
            dpHtml = `
                <div class="flex items-center justify-between py-2.5 border-b border-border-subtle dark:border-slate-800 text-xs">
                    <div>
                        <span class="font-semibold text-text-main dark:text-white block">Uang Muka (DP Kasir)</span>
                        <span class="text-[10px] text-text-muted dark:text-slate-400 font-mono">${escHtml(rec.transaction_date || '-')}</span>
                    </div>
                    <span class="font-mono tabular-nums font-semibold text-text-main dark:text-white">Rp ${downPayment.toLocaleString('id-ID')}</span>
                </div>
            `;
        }

        let paymentsHtml = '';
        let totalInstallments = 0;

        if (res.payments && res.payments.length > 0) {
            let rowsHtml = '';
            res.payments.forEach(p => {
                totalInstallments += Number(p.amount_paid || 0);
                const methodText = (p.payment_method || 'tunai').toUpperCase();

                rowsHtml += `
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-2.5 pr-2 font-mono text-text-muted dark:text-slate-400 whitespace-nowrap">
                            <div class="font-semibold text-text-main dark:text-white text-[11px]">${escHtml(p.payment_number || '-')}</div>
                            <div class="text-[10px] text-text-muted dark:text-slate-400">${escHtml(p.payment_date)}</div>
                        </td>
                        <td class="py-2.5 px-2 text-center whitespace-nowrap">
                            <span class="font-mono text-[10px] font-semibold uppercase tracking-wide ${p.payment_method === 'qris' ? 'text-blue-700 dark:text-blue-400' : 'text-text-muted dark:text-slate-300'}">${methodText}</span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono tabular-nums font-bold text-text-main dark:text-white whitespace-nowrap">
                            Rp ${Number(p.amount_paid).toLocaleString('id-ID')}
                        </td>
                        <td class="py-2.5 pl-3 text-left text-text-muted dark:text-slate-300">
                            <div class="font-medium text-text-main dark:text-slate-200 whitespace-nowrap">${escHtml((p.received_by_name || 'Kasir').replace(/\s*\([^)]*\)\s*$/, ''))}</div>
                            ${p.notes ? `<div class="text-[10px] text-text-muted dark:text-slate-400 truncate max-w-[150px]" title="${escHtml(p.notes)}">${escHtml(p.notes)}</div>` : ''}
                        </td>
                    </tr>
                `;
            });

            paymentsHtml = `
                <div class="flex flex-col">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-text-muted dark:text-slate-400">Daftar Angsuran</span>
                        <span class="text-[10px] font-mono text-text-muted dark:text-slate-400">${res.payments.length} transaksi</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-text-muted dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-y border-border-subtle dark:border-slate-800">
                                    <th class="py-2 pr-2">Kuitansi</th>
                                    <th class="py-2 px-2 text-center">Metode</th>
                                    <th class="py-2 px-3 text-right">Nominal</th>
                                    <th class="py-2 pl-3 text-left">Penerima</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                                ${rowsHtml}
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-border-subtle dark:border-slate-800 font-bold">
                                    <td colspan="2" class="py-2.5 pr-2 text-left text-[11px] text-text-muted dark:text-slate-400">Total Angsuran (${res.payments.length}x)</td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums font-extrabold text-text-main dark:text-white whitespace-nowrap">Rp ${totalInstallments.toLocaleString('id-ID')}</td>
                                    <td class="py-2.5 pl-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            `;
            if (footerCount) footerCount.textContent = `${res.payments.length} angsuran tercatat`;
        } else {
            paymentsHtml = `
                <div class="py-8 text-center text-text-muted border-b border-dashed border-border-subtle dark:border-slate-800">
                    <span class="text-xs">Belum ada angsuran cicilan tercatat untuk nota ini.</span>
                </div>
            `;
            if (footerCount) footerCount.textContent = '0 angsuran';
        }

        document.getElementById('histModalContent').innerHTML = summaryHtml + dpHtml + paymentsHtml;
    } catch (e) {
        document.getElementById('histModalContent').innerHTML = '<div class="text-red-600 dark:text-red-400 text-center py-6">Gagal memuat: ' + escHtml(e.message) + '</div>';
    }
}
</script>
@endpush
