@extends('layouts.app')

@section('title', 'Buku Kasbon & Piutang Pelanggan - MANXMARKET')
@section('page_title', 'Buku Hutang Piutang & Tempo')

@section('content')
<div class="flex flex-col gap-6 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-5 border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight">Buku Hutang Piutang &amp; Tempo</h1>
        <p class="text-xs text-text-muted mt-1">Audit transaksi tempo pelanggan grosir, jatuh tempo, dan pencatatan cicilan kasbon.</p>
    </div>

    <!-- 4 Bento KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Total Piutang Beredar</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-amber-700 dark:text-amber-400 font-mono">{{ formatRupiah($stats['total_active_debt']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">{{ number_format($stats['unpaid_count']) }} tagihan belum lunas</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Pelanggan Memiliki Kasbon</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">{{ number_format($stats['total_customers_with_debt']) }} <span class="text-xs text-text-muted font-sans font-normal">Toko</span></div>
                <div class="text-[11px] text-text-muted mt-0.5">Memiliki saldo piutang aktif</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-emerald-700 dark:text-emerald-400 tracking-wider">Total Cicilan Terkumpul</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-emerald-700 dark:text-emerald-400 font-mono">{{ formatRupiah($stats['total_collected']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Kas masuk dari pelunasan kasbon</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Faktur Telah Lunas</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">{{ number_format($stats['paid_count']) }} <span class="text-xs text-text-muted font-sans font-normal">Nota</span></div>
                <div class="text-[11px] text-text-muted mt-0.5">Selesai terbayar penuh</div>
            </div>
        </div>
    </div>

    <!-- Search Toolbar -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-4 border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <form method="GET" action="{{ route('receivables.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs font-mono outline-none focus:border-blue-500" placeholder="Cari nomor nota atau nama pelanggan grosir..." value="{{ $search }}">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">search</span>
                <span>Cari</span>
            </button>
        </form>
    </div>

    <!-- Receivables Table -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto focus-visible:outline-2 focus-visible:outline-blue-600 dark:focus-visible:outline-blue-400" tabindex="0" role="region" aria-label="Tabel tagihan piutang">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3">No. Nota</th>
                        <th class="p-3">Pelanggan Grosir</th>
                        <th class="p-3 font-mono">Jatuh Tempo</th>
                        <th class="p-3 text-right">Total Hutang</th>
                        <th class="p-3 text-right">Sudah Dibayar</th>
                        <th class="p-3 text-right">Sisa Tagihan</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($receivables as $r)
                        @php
                            $isOverdue = $r->status === 'belum_lunas' && $r->due_date && \Carbon\Carbon::parse($r->due_date)->isPast();
                        @endphp
                        <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors">
                            <td class="p-3 font-mono font-bold text-text-main dark:text-white whitespace-nowrap">
                                {{ $r->transaction->invoice_number ?? '-' }}
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-text-main dark:text-white">{{ $r->customer->name ?? 'Pelanggan' }}</div>
                                <div class="text-[11px] text-text-muted font-mono">{{ $r->customer->phone ?? '-' }}</div>
                            </td>
                            <td class="p-3 font-mono {{ $isOverdue ? 'text-red-700 dark:text-red-400 font-bold' : 'text-text-muted' }}">
                                {{ $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('d/m/Y') : '-' }}
                                @if($isOverdue)
                                    <span class="block text-[10px] text-red-600 dark:text-red-400">Terlambat!</span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-mono tabular-nums font-semibold text-text-main dark:text-white whitespace-nowrap">{{ formatRupiah($r->total_debt) }}</td>
                            <td class="p-3 text-right font-mono tabular-nums text-emerald-700 dark:text-emerald-400 whitespace-nowrap">{{ formatRupiah($r->total_paid) }}</td>
                            <td class="p-3 text-right font-mono tabular-nums font-bold whitespace-nowrap {{ $r->remaining_debt > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-text-muted' }}">{{ formatRupiah($r->remaining_debt) }}</td>
                            <td class="p-3 text-center">
                                @if($r->status === 'lunas')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40">LUNAS</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">BELUM LUNAS</span>
                                @endif
                            </td>
                            <td class="p-3 text-right whitespace-nowrap">
                                @if($r->status !== 'lunas')
                                    <button type="button" onclick="openPaymentModal({{ $r->id }}, '{{ addslashes($r->transaction->invoice_number ?? '') }}', '{{ addslashes($r->customer->name ?? '') }}', {{ $r->remaining_debt }})" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition-colors mr-1 cursor-pointer">
                                        Bayar Cicilan
                                    </button>
                                @endif
                                <button type="button" onclick="openPaymentHistory({{ $r->id }}, '{{ addslashes($r->transaction->invoice_number ?? '') }}')" class="px-2.5 py-1 rounded-lg bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-text-main dark:text-white text-xs transition-colors cursor-pointer">
                                    Riwayat
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-text-muted">
                                Tidak ada data catatan piutang kasbon aktif saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-border-subtle dark:border-slate-800">
            {{ $receivables->links() }}
        </div>
    </div>
</div>

<!-- Modal: Bayar Cicilan -->
<div id="paymentModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-border-subtle dark:border-slate-800 my-auto max-h-[calc(100vh-2rem)] flex flex-col">
        <!-- Modal Header (Fixed) -->
        <div class="flex items-center justify-between pb-3 border-b border-border-subtle dark:border-slate-800 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">payments</span>
                </div>
                <div>
                    <h2 class="font-bold text-base text-text-main dark:text-white leading-tight">Input Pembayaran Cicilan</h2>
                    <p class="text-[11px] text-text-muted mt-0.5">Catat penerimaan cicilan kasbon piutang</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('paymentModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Tutup">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Scrollable Modal Body -->
        <form onsubmit="submitPayment(event)" class="flex flex-col flex-1 overflow-y-auto pt-3">
            @csrf
            <input type="hidden" id="payRecId">
            <div class="flex flex-col gap-3 text-xs overflow-y-auto pr-1">
                <div class="p-3 bg-background-canvas dark:bg-slate-800/60 rounded-xl border border-border-subtle dark:border-slate-700 text-xs flex flex-col gap-1.5" id="payModalInfo"></div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold block text-text-main dark:text-white" for="payAmount">Nominal Pembayaran (Rp) *</label>
                        <button type="button" onclick="setFullPayAmount()" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer">Lunasi Sisa</button>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-mono font-bold text-sm text-text-muted">Rp</span>
                        <input type="number" id="payAmount" step="1" min="1" class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono text-base font-bold outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" oninput="handlePayAmountChange()" required>
                    </div>
                    <span class="text-[10px] text-text-muted dark:text-slate-400 mt-1 block">Mendukung pecahan satuan rupiah bebas (contoh: 100, 250, 500, dsb.)</span>
                </div>

                <div>
                    <label class="font-bold block text-text-main dark:text-white mb-1.5">Metode Penerimaan *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" id="payTabTunai" onclick="setPayMethod('tunai')" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/40 text-emerald-950 dark:text-emerald-100 font-bold text-xs shadow-xs cursor-pointer select-none transition-all">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600 dark:text-emerald-400">payments</span>
                            <span>Kas Tunai</span>
                        </button>
                        <button type="button" id="payTabQris" onclick="setPayMethod('qris')" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-blue-400 font-semibold text-xs cursor-pointer select-none transition-all">
                            <span class="material-symbols-outlined text-[18px] text-blue-600 dark:text-blue-400">qr_code_2</span>
                            <span>QRIS Dinamis</span>
                        </button>
                    </div>
                    <input type="hidden" id="payMethod" value="tunai">
                </div>

                <!-- Dynamic QRIS Display Container -->
                <div id="recQrisSection" class="hidden p-3 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/60 flex flex-col items-center gap-2 text-center transition-all">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-100/90 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 text-[10px] font-bold border border-blue-200 dark:border-blue-800/60">
                        <span class="material-symbols-outlined text-[14px]">qr_code_2</span>
                        <span>QRIS Dinamis Otomatis</span>
                    </div>
                    <div class="p-2 bg-white rounded-xl shadow-xs border border-slate-200 flex justify-center items-center my-0.5" id="recQrisCanvasWrapper">
                        <div id="recQrisCanvas" class="flex items-center justify-center"></div>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[10px] text-text-muted dark:text-slate-400">Total Nominal Pembayaran:</span>
                        <span id="recQrisAmountDisplay" class="font-mono font-extrabold text-lg text-blue-950 dark:text-blue-100">Rp 0</span>
                        <span class="text-[10px] text-text-muted mt-0.5" id="recQrisMerchantNameDisplay"></span>
                    </div>
                    <div class="text-[10px] text-emerald-800 dark:text-emerald-300 font-bold bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[12px]">lock</span>
                        <span>Nominal cicilan otomatis terkunci pada QRIS</span>
                    </div>
                </div>

                <div id="refNumberGroup" class="hidden">
                    <label class="font-bold block text-text-main dark:text-white mb-1" for="payReferenceNote">Nomor Referensi Transaksi (Opsional)</label>
                    <input type="text" id="payReferenceNote" placeholder="Contoh: No. Ref QRIS / Transfer Bank" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono text-xs outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="font-bold block text-text-main dark:text-white mb-1" for="payNotes">Catatan / Keterangan (Opsional)</label>
                    <input type="text" id="payNotes" placeholder="Misal: Cicilan ke-2 transfer BCA / titip kasir" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Modal Action Buttons (Fixed Bottom) -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-border-subtle dark:border-slate-800 mt-3 shrink-0">
                <button type="button" onclick="closeModal('paymentModal')" class="px-4 py-2.5 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main font-semibold cursor-pointer">Batal</button>
                <button type="submit" id="paySubmitBtn" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs transition-all cursor-pointer flex items-center gap-1.5 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]" id="paySubmitBtnIcon">payments</span>
                    <span id="paySubmitBtnText">Konfirmasi Bayar Tunai</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Drawer: Riwayat Cicilan (Slide-over Sheet) -->
<div id="historyModal" class="modal-backdrop hidden fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="histModalTitle">
    <!-- Non-intrusive backdrop scrim -->
    <div class="fixed inset-0 bg-slate-900/25 dark:bg-black/50 transition-opacity cursor-pointer" onclick="closeModal('historyModal')"></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-4 sm:pl-10 pointer-events-none">
        <div class="w-screen max-w-md lg:max-w-lg bg-surface-white dark:bg-slate-900 shadow-2xl border-l border-border-subtle dark:border-slate-800 flex flex-col pointer-events-auto transform transition-all">
            <!-- Header -->
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

            <!-- Content Area (Scrollable) -->
            <div id="histModalContent" class="p-5 overflow-y-auto flex-1 text-xs text-text-main dark:text-slate-200 flex flex-col gap-4">
                Memuat riwayat...
            </div>

            <!-- Footer -->
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
const QRIS_STATIC_STRING = @json($store['qris_static_string'] ?? '');
const QRIS_MERCHANT_NAME = @json($store['qris_merchant_name'] ?? 'MANXMARKET POS');

let currentRemainingDebt = 0;

function openPaymentModal(id, invoice, customer, remaining) {
    currentRemainingDebt = Number(remaining);
    document.getElementById('payRecId').value = id;
    document.getElementById('payModalInfo').innerHTML = `
        <div class="flex items-center justify-between font-bold text-text-main dark:text-white">
            <span class="truncate">${customer}</span>
            <span class="font-mono text-xs text-secondary dark:text-blue-400 shrink-0 font-bold">${invoice}</span>
        </div>
        <div class="flex items-center justify-between text-[11px] text-text-muted dark:text-slate-400 pt-1 border-t border-border-subtle dark:border-slate-700/50">
            <span>Sisa Tagihan Piutang:</span>
            <span class="font-mono font-extrabold text-amber-700 dark:text-amber-400 text-xs">Rp ${Number(remaining).toLocaleString('id-ID')}</span>
        </div>
    `;
    const payInput = document.getElementById('payAmount');
    payInput.value = remaining;
    payInput.max = remaining;
    document.getElementById('payNotes').value = '';
    const refInput = document.getElementById('payReferenceNote');
    if (refInput) refInput.value = '';

    setPayMethod('tunai');
    openModal('paymentModal');
}

function setFullPayAmount() {
    if (currentRemainingDebt > 0) {
        const input = document.getElementById('payAmount');
        input.value = currentRemainingDebt;
        handlePayAmountChange();
    }
}

function setPayMethod(method) {
    document.getElementById('payMethod').value = method;
    const tabTunai = document.getElementById('payTabTunai');
    const tabQris = document.getElementById('payTabQris');
    const qrisSection = document.getElementById('recQrisSection');
    const refGroup = document.getElementById('refNumberGroup');
    const submitBtn = document.getElementById('paySubmitBtn');
    const submitIcon = document.getElementById('paySubmitBtnIcon');
    const submitText = document.getElementById('paySubmitBtnText');

    if (method === 'qris') {
        tabQris.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-blue-500 bg-blue-50/90 dark:bg-blue-950/40 text-blue-950 dark:text-blue-100 font-bold text-xs shadow-xs cursor-pointer select-none transition-all';
        tabTunai.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-emerald-400 font-semibold text-xs cursor-pointer select-none transition-all';

        if (qrisSection) qrisSection.classList.remove('hidden');
        if (refGroup) refGroup.classList.remove('hidden');

        if (submitBtn) {
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-xs transition-all cursor-pointer flex items-center gap-1.5 active:scale-95';
            if (submitIcon) submitIcon.textContent = 'qr_code_scanner';
            if (submitText) submitText.textContent = 'Konfirmasi Bayar QRIS';
        }

        renderRecQris();
    } else {
        tabTunai.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/40 text-emerald-950 dark:text-emerald-100 font-bold text-xs shadow-xs cursor-pointer select-none transition-all';
        tabQris.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-blue-400 font-semibold text-xs cursor-pointer select-none transition-all';

        if (qrisSection) qrisSection.classList.add('hidden');
        if (refGroup) refGroup.classList.add('hidden');

        if (submitBtn) {
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs transition-all cursor-pointer flex items-center gap-1.5 active:scale-95';
            if (submitIcon) submitIcon.textContent = 'payments';
            if (submitText) submitText.textContent = 'Konfirmasi Bayar Tunai';
        }
    }
}

function handlePayAmountChange() {
    const payInput = document.getElementById('payAmount');
    let amount = Number(payInput.value || 0);

    if (currentRemainingDebt > 0 && amount > currentRemainingDebt) {
        amount = currentRemainingDebt;
        payInput.value = currentRemainingDebt;
        if (typeof showToast === 'function') {
            showToast('Nominal disesuaikan ke sisa hutang: Rp ' + currentRemainingDebt.toLocaleString('id-ID'), 'info');
        }
    }

    const currentMethod = document.getElementById('payMethod').value;
    if (currentMethod === 'qris') {
        renderRecQris();
    }
}

function renderRecQris() {
    const amount = Number(document.getElementById('payAmount')?.value || 0);
    const canvas = document.getElementById('recQrisCanvas');
    const amtDisplay = document.getElementById('recQrisAmountDisplay');
    const merchDisplay = document.getElementById('recQrisMerchantNameDisplay');

    if (amtDisplay) {
        amtDisplay.textContent = 'Rp ' + amount.toLocaleString('id-ID');
    }
    if (merchDisplay) {
        merchDisplay.textContent = QRIS_MERCHANT_NAME;
    }
    if (!canvas) return;

    canvas.innerHTML = '';
    if (amount <= 0 || !QRIS_STATIC_STRING) {
        canvas.innerHTML = '<div class="text-[11px] text-text-muted py-6">Masukkan nominal pembayaran untuk memunculkan QRIS.</div>';
        return;
    }

    try {
        const dynamicPayload = (typeof makeDynamicQris === 'function') 
            ? makeDynamicQris(QRIS_STATIC_STRING, amount) 
            : QRIS_STATIC_STRING;

        new QRCode(canvas, {
            text: dynamicPayload,
            width: 135,
            height: 135,
            colorDark: "#0b1c30",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    } catch (e) {
        canvas.innerHTML = '<div class="text-xs text-red-600 py-4">Gagal menghasilkan QRIS: ' + e.message + '</div>';
    }
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

async function submitPayment(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const amount = Number(document.getElementById('payAmount').value || 0);

    if (amount <= 0) {
        if (typeof showToast === 'function') {
            showToast('Nominal pembayaran harus lebih besar dari Rp 0.', 'warning');
        }
        return;
    }

    if (currentRemainingDebt > 0 && amount > currentRemainingDebt) {
        if (typeof showToast === 'function') {
            showToast('Nominal melebihi sisa tagihan piutang (Rp ' + currentRemainingDebt.toLocaleString('id-ID') + ').', 'warning');
        }
        return;
    }

    const payload = {
        receivable_id: document.getElementById('payRecId').value,
        amount_paid: amount,
        payment_method: document.getElementById('payMethod').value,
        reference_note: document.getElementById('payReferenceNote')?.value || '',
        notes: document.getElementById('payNotes')?.value || ''
    };

    const btn = document.getElementById('paySubmitBtn');
    btn.disabled = true;
    const oldBtnText = document.getElementById('paySubmitBtnText').textContent;
    document.getElementById('paySubmitBtnText').textContent = 'Memproses...';

    try {
        const resp = await fetch('{{ route("receivables.pay") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);
        closeModal('paymentModal');
        if (typeof showToast === 'function') {
            showToast(res.message, 'success');
        }
        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        if (typeof showToast === 'function') {
            showToast('Error: ' + err.message, 'error');
        }
    } finally {
        btn.disabled = false;
        document.getElementById('paySubmitBtnText').textContent = oldBtnText;
    }
}
</script>
@endpush
