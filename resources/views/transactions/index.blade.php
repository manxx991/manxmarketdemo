@extends('layouts.app')

@section('title', 'Riwayat Transaksi & Void - MANXMARKET')
@section('page_title', 'Riwayat Transaksi Penjualan & Void')

@section('content')
<div class="flex flex-col gap-space-lg">
    <!-- Header Title -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-space-md border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <div class="flex items-center gap-1.5 text-text-muted text-xs">
            <span class="uppercase tracking-wider font-semibold">Kasir &amp; Penjualan</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-primary dark:text-blue-400 font-bold uppercase tracking-wider">Audit Nota &amp; Void</span>
        </div>
        <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight mt-1">Riwayat Transaksi Penjualan &amp; Void</h1>
        <p class="text-xs text-text-muted mt-0.5">Daftar seluruh nota transaksi kasir, cetak ulang struk thermal, dan audit pembatalan nota (void).</p>
    </div>

    <!-- Filter Bar -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-space-md border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="form-label" for="filterSearch">Cari Nota / Pelanggan</label>
                <input type="text" id="filterSearch" name="search" class="form-control font-mono" placeholder="No nota / nama pembeli..." value="{{ $search }}">
            </div>

            <div class="w-36">
                <label class="form-label" for="filterStartDate">Dari Tanggal</label>
                <input type="date" id="filterStartDate" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>

            <div class="w-36">
                <label class="form-label" for="filterEndDate">Sampai Tanggal</label>
                <input type="date" id="filterEndDate" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>

            <div class="w-44">
                <label class="form-label" for="filterStatus">Status</label>
                <div class="relative">
                    <select id="filterStatus" name="status" class="w-full pl-3 pr-8 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs">
                        <option value="all">Semua Status</option>
                        <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="belum_lunas" {{ $status === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas (Tempo)</option>
                        <option value="void" {{ $status === 'void' ? 'selected' : '' }}>VOID (Batal)</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-600 dark:text-slate-300 pointer-events-none text-[18px]" aria-hidden="true">unfold_more</span>
                </div>
            </div>

            <div class="w-44">
                <label class="form-label" for="filterMethod">Metode Bayar</label>
                <div class="relative">
                    <select id="filterMethod" name="payment_method" class="w-full pl-3 pr-8 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs">
                        <option value="all">Semua Metode</option>
                        <option value="tunai" {{ $paymentMethod === 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="qris" {{ $paymentMethod === 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="hutang" {{ $paymentMethod === 'hutang' ? 'selected' : '' }}>Hutang/Tempo</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-600 dark:text-slate-300 pointer-events-none text-[18px]" aria-hidden="true">unfold_more</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">Filter</button>
                <a href="{{ route('transactions.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-100 font-bold text-xs shadow-xs transition-colors cursor-pointer">Reset</a>
            </div>
        </form>
    </div>

    <!-- Stats Quick Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="p-4 rounded-2xl bg-surface-white dark:bg-slate-900 border border-border-subtle dark:border-slate-800 shadow-2xs transition-colors">
            <span class="text-xs text-text-muted font-medium">Total Transaksi</span>
            <div class="text-xl font-bold font-mono text-text-main dark:text-white mt-1">{{ number_format($stats['total_count']) }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-surface-white dark:bg-slate-900 border border-border-subtle dark:border-slate-800 shadow-2xs transition-colors">
            <span class="text-xs text-text-muted font-medium">Transaksi Lunas</span>
            <div class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['lunas_count']) }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-surface-white dark:bg-slate-900 border border-border-subtle dark:border-slate-800 shadow-2xs transition-colors">
            <span class="text-xs text-text-muted font-medium">Tempo / Kasbon</span>
            <div class="text-xl font-bold font-mono text-amber-600 dark:text-amber-400 mt-1">{{ number_format($stats['belum_lunas_count']) }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-surface-white dark:bg-slate-900 border border-border-subtle dark:border-slate-800 shadow-2xs transition-colors">
            <span class="text-xs text-text-muted font-medium">Transaksi VOID</span>
            <div class="text-xl font-bold font-mono text-red-600 dark:text-red-400 mt-1">{{ number_format($stats['void_count']) }}</div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface dark:bg-slate-800/80 border-b border-border-subtle dark:border-slate-800 text-text-muted dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="p-3.5 whitespace-nowrap">No. Nota</th>
                        <th class="p-3.5 whitespace-nowrap">Tanggal &amp; Waktu</th>
                        <th class="p-3.5 whitespace-nowrap">Kasir</th>
                        <th class="p-3.5">Pelanggan</th>
                        <th class="p-3.5 whitespace-nowrap">Metode Bayar</th>
                        <th class="p-3.5 whitespace-nowrap text-right">Total Belanja</th>
                        <th class="p-3.5 whitespace-nowrap">Status Nota</th>
                        <th class="p-3.5 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse ($transactions as $t)
                        <tr class="hover:bg-background-canvas dark:hover:bg-slate-800/40 transition-colors {{ $t->payment_status === 'void' ? 'bg-red-50/40 dark:bg-red-950/20' : '' }}">
                            <td class="p-3.5 font-mono font-bold text-text-main dark:text-white whitespace-nowrap">
                                {{ $t->invoice_number }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap font-mono text-text-muted dark:text-slate-400">
                                {{ $t->transaction_date->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-text-main dark:text-slate-200 font-medium">
                                {{ $t->cashier->name ?? 'Kasir' }}
                            </td>
                            <td class="p-3.5 text-text-main dark:text-white font-semibold">
                                {{ $t->customer_name_manual ?: ($t->customer->name ?? 'Pelanggan Umum') }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
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
                            <td class="p-3.5 whitespace-nowrap font-mono font-bold text-right text-text-main dark:text-white">
                                {{ formatRupiah($t->grand_total) }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                @if($t->payment_status === 'lunas')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-bold">LUNAS</span>
                                @elseif($t->payment_status === 'belum_lunas')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-bold">BELUM LUNAS</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 text-[10px] font-bold">BATAL / VOID</span>
                                @endif
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" class="px-2 py-1 rounded-lg border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 hover:bg-background-canvas dark:hover:bg-slate-700 text-xs font-semibold text-text-main dark:text-white flex items-center gap-1 shadow-2xs cursor-pointer" onclick="openReprintModal({{ $t->id }})" title="Cetak Ulang Struk">
                                        <span class="material-symbols-outlined text-[15px]">print</span>
                                        Struk
                                    </button>
                                    @if(Auth::user()->role === 'owner' && $t->payment_status !== 'void')
                                        <button type="button" class="px-2 py-1 rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-xs font-bold text-red-700 dark:text-red-400 flex items-center gap-1 cursor-pointer" onclick="openVoidDialog({{ $t->id }}, '{{ $t->invoice_number }}')" title="Batalkan Nota (Void)">
                                            <span class="material-symbols-outlined text-[15px]">cancel</span>
                                            Void
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-text-muted">
                                Tidak ada data transaksi yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-border-subtle dark:border-slate-800">
            {{ $transactions->links() }}
        </div>
    </div>
</div>

@if(Auth::user()->role === 'owner')
<!-- Modal Void Transaksi -->
<div id="voidModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="voidModalTitle">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-red-600 text-[24px]">warning</span>
                <h2 class="modal-title" id="voidModalTitle">Konfirmasi Pembatalan (VOID)</h2>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('voidModal')" aria-label="Tutup">&times;</button>
        </div>
        <form id="voidForm" onsubmit="handleVoidSubmit(event)">
            <input type="hidden" id="voidTransactionId" name="transaction_id">
            <div class="modal-body flex flex-col gap-3">
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-900 leading-relaxed">
                    Perhatian: Tindakan pembatalan (VOID) akan <strong>mengembalikan seluruh kuantitas stok barang</strong> ke gudang dan membatalkan piutang tempo terkait. Data tidak dapat dipulihkan kembali.
                </div>
                <div class="form-group">
                    <label class="form-label" for="voidInvoiceDisplay">Nomor Nota Transaksi</label>
                    <input type="text" id="voidInvoiceDisplay" class="form-control font-mono font-bold bg-background-canvas" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label" for="voidReasonInput">Alasan Pembatalan (Wajib Minimal 5 Karakter) *</label>
                    <textarea id="voidReasonInput" name="void_reason" class="form-control" rows="3" required placeholder="Contoh: Salah ketik item barang kasir / Pembeli membatalkan pesanan"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('voidModal')">Tutup</button>
                <button type="submit" id="voidSubmitBtn" class="btn btn-danger">Eksekusi VOID &amp; Restok</button>
            </div>
        </form>
    </div>
</div>
@endif

@include('components.thermal-receipt')

@endsection

@push('scripts')
<script>
async function openReprintModal(id) {
    try {
        const resp = await fetch(`{{ url('api/transactions') }}/${id}`);
        const data = await resp.json();
        if (data.success && data.transaction) {
            const tx = data.transaction;
            if (!tx.store && data.store) tx.store = data.store;
            showReceiptModal(tx);
        } else {
            showToast(data.message || 'Gagal memuat detail transaksi.', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
}

@if(Auth::user()->role === 'owner')
function openVoidDialog(id, invoiceNumber) {
    document.getElementById('voidTransactionId').value = id;
    document.getElementById('voidInvoiceDisplay').value = invoiceNumber;
    document.getElementById('voidReasonInput').value = '';
    openModal('voidModal');
}

async function handleVoidSubmit(e) {
    e.preventDefault();
    const transId = document.getElementById('voidTransactionId').value;
    const reason = document.getElementById('voidReasonInput').value.trim();

    if (reason.length < 5) {
        showToast('Alasan pembatalan minimal 5 karakter.', 'warning');
        return;
    }

    const btn = document.getElementById('voidSubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Memproses Void...';

    try {
        const resp = await fetch('{{ route("transactions.void") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({ transaction_id: transId, void_reason: reason })
        });
        const res = await resp.json();
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('voidModal');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(res.message || 'Gagal melakukan void.', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Eksekusi VOID & Restok';
    }
}
@endif
</script>
@endpush
