@extends('layouts.app')

@section('title', 'Kasir (POS) & Penjualan - MANXMARKET')
@section('page_title', 'Katalog Penjualan Kasir')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-[1fr_390px] xl:grid-cols-[1fr_420px] gap-4 lg:gap-6 w-full">
    <!-- LEFT / CENTER AREA: Products Catalog & Quick Operations -->
    <div class="flex flex-col gap-space-md min-w-0 order-2 lg:order-1">
        <!-- Top Action Bar & Category Strip -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-space-md shadow-sm border border-border-subtle dark:border-slate-800 flex flex-col gap-space-md transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-sm">
                    <span class="w-11 h-11 rounded-2xl bg-primary-fixed dark:bg-blue-950/60 text-primary dark:text-blue-400 border border-transparent dark:border-blue-900/40 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px]">point_of_sale</span>
                    </span>
                    <div>
                        <h1 class="font-bold text-lg text-text-main dark:text-white leading-tight">Katalog Penjualan Kasir</h1>
                        <p class="text-xs text-text-muted mt-0.5">Mode Grosir Dinamis &amp; Ritel Cepat Aktif</p>
                    </div>
                </div>

                <div class="flex items-center gap-space-xs bg-background-canvas dark:bg-slate-800 p-1 rounded-xl border border-border-subtle dark:border-slate-700">
                    <button type="button" onclick="posApp && posApp.scrollToCart()" class="lg:hidden flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-xs text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-900/50 shadow-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors cursor-pointer" aria-label="Ke Keranjang Kasir">
                        <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
                        <span>Keranjang</span>
                        <span id="catalogCartCount" class="font-mono tabular-nums font-bold text-[10px] px-1.5 py-0.5 rounded-full bg-blue-600 text-white">0</span>
                    </button>
                    <button type="button" onclick="document.getElementById('searchInput').focus()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-white dark:bg-slate-900 text-xs text-text-main dark:text-white shadow-xs font-semibold hover:text-primary dark:hover:text-blue-400 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[16px] text-primary dark:text-blue-400">barcode_scanner</span>
                        Auto-Scanner F2
                    </button>
                </div>
            </div>

            <!-- Search Bar Input -->
            <div class="relative">
                <label for="searchInput" class="sr-only">Ketik nama produk, SKU, atau scan barcode</label>
                <span class="material-symbols-outlined absolute left-3.5 top-3 text-text-muted text-[20px]" aria-hidden="true">search</span>
                <input type="text" id="searchInput" autofocus class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs sm:text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 transition-all font-mono" placeholder="Ketik nama produk, SKU, atau scan barcode... (Tekan F2)">
            </div>

            <!-- Horizontal Filter Badges -->
            <div class="flex flex-wrap items-center gap-2 category-tabs">
                <button type="button" class="category-pill active px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap bg-primary dark:bg-blue-600 text-white shadow-xs cursor-pointer transition-colors" data-cat="all" onclick="posApp.filterCategory('all', this)">
                    Semua Produk ({{ count($products) }})
                </button>
                @foreach ($categories as $cat)
                    <button type="button" class="category-pill px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap bg-background-canvas dark:bg-slate-800 text-blue-700 dark:text-blue-300 hover:bg-background-subtle dark:hover:bg-slate-700 hover:text-text-main dark:hover:text-white border border-border-subtle dark:border-slate-700 cursor-pointer transition-colors" data-cat="{{ $cat->slug }}" onclick="posApp.filterCategory('{{ $cat->slug }}', this)">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Card Grid (SSR Pre-rendered + Dynamic Filter via PosEngine) -->
        <h2 class="sr-only" id="catalogHeading">Daftar Produk Kasir</h2>
        <div id="catalogGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-space-md min-h-[500px] pb-28 lg:pb-0" aria-labelledby="catalogHeading" data-ssr="1">
            @forelse ($products as $p)
                @php
                    $isOutOfStock = $p->stock <= 0;
                    $stockBadge = 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
                    if ($p->stock <= 0) $stockBadge = 'bg-red-50 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800/60';
                    elseif ($p->stock <= $p->min_stock) $stockBadge = 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60';
                    $wsRule = $p->activeWholesaleRule;
                    $wholesaleDiscount = 0;
                    if ($wsRule && $wsRule->rule_type === 'per_item_qty') {
                        $wholesaleDiscount = match ($wsRule->discount_type) {
                            'percentage' => (float) $p->retail_price * (float) $wsRule->discount_value / 100,
                            'special_price' => max(0, (float) $p->retail_price - (float) $wsRule->discount_value),
                            default => (float) $wsRule->discount_value,
                        };
                    }
                    $wholesalePrice = max(0, (float) $p->retail_price - $wholesaleDiscount);
                @endphp
                <article class="group relative bg-surface-white dark:bg-slate-900 rounded-2xl p-3.5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between border border-border-subtle dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 {{ $isOutOfStock ? 'opacity-60 pointer-events-none' : '' }}" data-sku="{{ $p->sku }}" data-price="{{ (float)$p->retail_price }}">
                    <button type="button" onclick="posApp && posApp.addToCart({{ $p->id }})" class="absolute inset-0 w-full h-full rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 cursor-pointer z-10" aria-label="Tambah {{ $p->name }} harga {{ formatRupiah($p->retail_price) }} ke keranjang" @if($isOutOfStock) disabled @endif></button>
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-blue-700 dark:text-blue-300 border border-slate-200/80 dark:border-slate-700/60 tracking-wide select-none whitespace-nowrap shrink-0">
                                {{ $p->unit }}
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg font-mono {{ $stockBadge }} select-none whitespace-nowrap shrink-0">
                                {{ $p->stock <= 0 ? 'Habis' : 'Stok: ' . $p->stock }}
                            </span>
                        </div>
                        <div class="w-full h-28 rounded-xl bg-white p-2 border border-slate-100 dark:border-slate-800 flex items-center justify-center mb-2.5 overflow-hidden group-hover:scale-[1.02] transition-transform">
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" width="180" height="180" @if($loop->index < 4) fetchpriority="high" @endif class="w-full h-full object-contain aspect-square" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            <div class="hidden w-full h-full rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center" aria-hidden="true">
                                <span class="material-symbols-outlined text-[32px] opacity-85">inventory_2</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono text-slate-700 dark:text-slate-300 font-bold tracking-wider uppercase block select-none truncate">SKU: {{ $p->sku }}</span>
                        <h3 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-slate-100 group-hover:text-primary dark:group-hover:text-blue-400 transition-colors line-clamp-2 min-h-[2.5rem] mt-0.5 leading-snug">{{ $p->name }}</h3>
                        @if($wsRule && $wsRule->min_qty)
                            <div class="bg-wholesale-badge-bg dark:bg-red-950/40 border border-red-200/80 dark:border-red-900/50 px-2.5 py-1 rounded-xl mt-2 flex items-center justify-between gap-2 text-[11px] text-wholesale-badge-text dark:text-red-300 font-bold">
                                <span class="flex items-center gap-1 min-w-0 truncate">
                                    <span class="material-symbols-outlined text-[13px] shrink-0" aria-hidden="true">sell</span>
                                    <span class="truncate">&ge; {{ $wsRule->min_qty }} {{ $p->unit }}</span>
                                </span>
                                <span class="font-mono whitespace-nowrap shrink-0">@ {{ formatRupiah($wholesalePrice) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-auto pt-3 border-t border-border-subtle dark:border-slate-800 flex items-center justify-between">
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-700 dark:text-slate-300 select-none">Harga Ecer</span>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 font-mono select-none">Rp</span>
                                <span class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white font-mono tracking-tight leading-none">{{ number_format((float)$p->retail_price, 0, ',', '.') }}</span>
                                <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 font-sans ml-0.5">/{{ $p->unit }}</span>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-surface-white dark:bg-slate-900 rounded-2xl p-8 border border-border-subtle dark:border-slate-800 text-center">
                    <div class="font-bold text-sm text-text-main dark:text-white">Belum ada produk aktif</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- RIGHT DEDICATED CART & ORDER PANEL (Top on Mobile, Right Sticky on Desktop) -->
    <aside id="posCartPanel" class="w-full relative lg:h-full order-1 lg:order-2 scroll-mt-20" aria-label="Panel Keranjang Kasir">
        <div class="cart-sticky-panel bg-surface-white dark:bg-slate-900 rounded-2xl lg:rounded-3xl p-3.5 sm:p-5 lg:p-space-lg shadow-sm border border-border-subtle dark:border-slate-800 flex flex-col gap-3 lg:gap-space-md lg:sticky lg:top-[70px] lg:max-h-[calc(100vh-85px)] lg:overflow-y-auto">
            <!-- Order Header -->
            <div class="flex items-center justify-between pb-2 border-b border-border-subtle dark:border-slate-800">
                <div>
                    <h2 class="font-bold text-base text-text-main dark:text-white">Keranjang Kasir</h2>
                    <span class="text-xs text-text-muted dark:text-slate-300">Item Terpilih: <strong id="cartCountBadge" class="text-primary dark:text-blue-400 font-extrabold font-mono text-sm px-1.5 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-950/70 border border-blue-200/80 dark:border-blue-800/60">0</strong> <span class="font-semibold text-text-muted dark:text-slate-300">pcs</span></span>
                </div>
                <button type="button" class="flex items-center gap-1 text-xs text-text-muted dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 px-2.5 py-1 rounded-lg bg-background-canvas dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-950/40 border border-border-subtle dark:border-slate-700 transition-colors cursor-pointer" onclick="posApp.clearCart()" title="Reset Keranjang" aria-label="Reset Keranjang">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">delete_sweep</span>
                    <span>Reset</span>
                </button>
            </div>

            <!-- Customer Type / Account Selector -->
            <div class="bg-slate-50 dark:bg-slate-900/60 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col gap-2 shadow-2xs">
                <div class="flex items-center justify-between">
                    <label for="customerSelect" class="flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px] text-slate-600 dark:text-slate-400 select-none" aria-hidden="true">person</span>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-700 dark:text-slate-300 select-none">Identitas Pembeli</span>
                    </label>
                    <button type="button" onclick="openAddRoleModalQuick()" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-0.5 cursor-pointer transition-colors" title="Daftarkan Pelanggan Baru">
                        <span class="material-symbols-outlined text-[15px]">person_add</span>
                        <span>+ Pelanggan</span>
                    </button>
                </div>
                <div class="relative">
                    <select id="customerSelect" aria-label="Identitas Pembeli" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl py-2 pl-3 pr-8 text-xs font-semibold text-slate-800 dark:text-slate-100 appearance-none cursor-pointer focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs">
                        <option value="">Pelanggan Umum (Walk-in / Tunai &amp; QRIS)</option>
                        @foreach ($customers as $cust)
                            <option value="{{ $cust->id }}">
                                {{ $cust->name }} ({{ $cust->phone ?: 'No Telp -' }})
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                </div>

                <!-- Customer Debt & Plafon Warning -->
                <div id="customerDebtWarning" class="p-2.5 bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700/80 text-xs shadow-2xs" style="display: none;"></div>
            </div>

            <!-- Cart Items List (Rendered dynamically by PosEngine) -->
            <div id="cartItemsList" class="flex flex-col gap-2.5 max-h-[220px] lg:max-h-[340px] min-h-[84px] lg:min-h-[120px] overflow-y-auto pr-1">
                <div class="p-6 text-center bg-background-canvas dark:bg-slate-800/50 rounded-2xl border border-dashed border-border-subtle dark:border-slate-800">
                    <span class="material-symbols-outlined text-[32px] text-text-muted dark:text-slate-400 mb-1" aria-hidden="true">shopping_basket</span>
                    <div class="font-bold text-xs text-text-main dark:text-white">Keranjang Belanja Kosong</div>
                    <div class="text-[11px] text-text-muted dark:text-slate-300 mt-0.5">Pilih produk di katalog atau scan barcode.</div>
                </div>
            </div>

            <!-- Financial Summary Box -->
            <div class="flex flex-col gap-2 pt-2 border-t border-border-subtle dark:border-slate-800">
                <div class="flex items-center justify-between text-xs text-text-muted">
                    <span>Subtotal Bruto</span>
                    <span id="cartSubtotalDisplay" class="font-mono font-semibold text-text-main dark:text-slate-200">Rp 0</span>
                </div>
                <div class="flex items-center justify-between text-xs text-wholesale-badge-text dark:text-red-400 font-bold">
                    <span>Diskon Grosir Bertingkat</span>
                    <span id="cartDiscountDisplay" class="font-mono">-Rp 0</span>
                </div>
                <div class="flex items-center justify-between text-xs text-text-muted">
                    <span>PPN (0% Bebas Pajak Sembako)</span>
                    <span class="font-mono">Rp 0</span>
                </div>
                <div class="h-[1px] bg-border-subtle dark:border-slate-800 my-1"></div>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-text-main dark:text-white">Total Tagihan</span>
                    <span id="cartGrandTotalDisplay" class="font-extrabold text-2xl text-slate-900 dark:text-emerald-400 font-mono tracking-tight">Rp 0</span>
                </div>
            </div>

            <!-- Checkout Buttons -->
            <div class="flex flex-col gap-2 pt-2 border-t border-border-subtle dark:border-slate-800" id="panelMainActionBox">
                <button type="button" id="checkoutBtn" class="w-full h-12 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer" onclick="posApp.handleMainCheckoutClick()">
                    <span class="material-symbols-outlined text-[20px]" id="checkoutBtnIcon" aria-hidden="true">payments</span>
                    <span id="checkoutBtnText">Bayar &amp; Selesaikan (F4)</span>
                </button>
            </div>
        </div>
    </aside>
</div>

<!-- Modal Pembayaran POS (Tunai, QRIS, Tempo) -->
<div id="paymentModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="payModalTitle">
    <div class="modal-content" style="max-width: 580px;">
        <div class="modal-header">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-primary-fixed dark:bg-slate-800 text-primary dark:text-blue-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">payments</span>
                </div>
                <div>
                    <h2 class="modal-title text-base font-bold text-text-main dark:text-white" id="payModalTitle">Pilih Metode Pembayaran</h2>
                    <p class="text-[11px] text-text-muted">Selesaikan transaksi kasir grosir dengan metode pembayaran yang sesuai</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn p-1.5 rounded-lg hover:bg-background-canvas text-text-muted hover:text-text-main transition-colors" onclick="closeModal('paymentModal')" aria-label="Tutup Dialog Pembayaran">&times;</button>
        </div>
        <div class="modal-body flex flex-col gap-4">
            <!-- Total Banner -->
            <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl flex items-center justify-between border border-slate-200/90 dark:border-slate-800 shadow-2xs text-text-main dark:text-white transition-colors">
                <div class="flex flex-col">
                    <span class="text-[11px] font-bold tracking-wider uppercase text-text-muted dark:text-slate-400">Total Tagihan Kasir</span>
                    <span class="text-[11px] text-text-muted dark:text-slate-400 mt-0.5">Bebas PPN Sembako 0% • Termasuk diskon grosir</span>
                </div>
                <div class="text-right">
                    <span id="modalGrandTotalDisplay" class="font-mono font-extrabold text-2xl sm:text-3xl text-emerald-600 dark:text-emerald-400 tracking-tight">Rp 0</span>
                </div>
            </div>

            <!-- Interactive Payment Method Tabs (3 Rich Cards) -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-text-main dark:text-white">Pilih Saluran Pembayaran</label>
                <div class="grid grid-cols-3 gap-2.5">
                    <button type="button" id="modalTabTunai" class="relative flex flex-col items-center justify-center p-3 sm:p-3.5 rounded-2xl border-2 border-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/50 text-emerald-950 dark:text-emerald-100 font-bold text-xs shadow-xs ring-2 ring-emerald-500/20 transition-all cursor-pointer select-none active:scale-[0.98]" onclick="selectModalMethod('tunai')">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 absolute top-2.5 right-2.5 tab-dot"></span>
                        <span class="material-symbols-outlined text-[26px] mb-1 text-emerald-600 dark:text-emerald-400 tab-icon">payments</span>
                        <span class="font-bold text-xs tab-title">Kas Tunai</span>
                        <span class="text-[10px] text-emerald-800/80 dark:text-emerald-300/80 font-normal mt-0.5 tab-sub">Uang pas &amp; kembali</span>
                    </button>
                    <button type="button" id="modalTabQris" class="relative flex flex-col items-center justify-center p-3 sm:p-3.5 rounded-2xl border border-border-subtle dark:border-slate-800 bg-surface-white dark:bg-slate-800/70 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-blue-300 dark:hover:border-blue-700 font-medium text-xs transition-all cursor-pointer select-none active:scale-[0.98]" onclick="selectModalMethod('qris')">
                        <span class="w-2 h-2 rounded-full bg-blue-500 absolute top-2.5 right-2.5 tab-dot hidden"></span>
                        <span class="material-symbols-outlined text-[26px] mb-1 text-slate-400 dark:text-slate-500 tab-icon">qr_code_2</span>
                        <span class="font-bold text-xs tab-title">QRIS Dinamis</span>
                        <span class="text-[10px] text-text-muted dark:text-slate-500 font-normal mt-0.5 tab-sub">Shopee, DANA, Bank</span>
                    </button>
                    <button type="button" id="modalTabHutang" class="relative flex flex-col items-center justify-center p-3 sm:p-3.5 rounded-2xl border border-border-subtle dark:border-slate-800 bg-surface-white dark:bg-slate-800/70 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-amber-300 dark:hover:border-amber-700 font-medium text-xs transition-all cursor-pointer select-none active:scale-[0.98]" onclick="selectModalMethod('hutang')">
                        <span class="w-2 h-2 rounded-full bg-amber-500 absolute top-2.5 right-2.5 tab-dot hidden ring-2 ring-amber-300 dark:ring-amber-900"></span>
                        <span class="material-symbols-outlined text-[26px] mb-1 text-slate-400 dark:text-slate-500 tab-icon">event_note</span>
                        <span class="font-bold text-xs tab-title">Tempo / Kasbon</span>
                        <span class="text-[10px] text-text-muted dark:text-slate-500 font-normal mt-0.5 tab-sub">Piutang grosir</span>
                    </button>
                </div>
            </div>

            <!-- Tunai Section -->
            <div id="tunaiSection" class="flex flex-col gap-3">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-text-main dark:text-white" for="modalPayAmount">Uang Tunai Diterima (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-mono font-bold text-sm text-text-muted">Rp</span>
                        <input type="number" id="modalPayAmount" class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-mono font-bold text-lg text-text-main dark:text-white outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" placeholder="0" oninput="posApp.calculateChange()">
                    </div>
                </div>

                <!-- Quick Cash Presets -->
                <div class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-semibold text-text-muted">Pilihan Cepat Nominal:</span>
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-1.5">
                        <button type="button" class="py-1.5 px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs transition-all cursor-pointer text-center" onclick="posApp.setQuickCash('pas')">Uang Pas</button>
                        <button type="button" class="py-1.5 px-2 rounded-xl bg-surface-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-border-subtle dark:border-slate-700 text-[11px] font-bold text-text-main dark:text-white transition-all cursor-pointer text-center font-mono" onclick="posApp.setQuickCash(50000)">50rb</button>
                        <button type="button" class="py-1.5 px-2 rounded-xl bg-surface-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-border-subtle dark:border-slate-700 text-[11px] font-bold text-text-main dark:text-white transition-all cursor-pointer text-center font-mono" onclick="posApp.setQuickCash(100000)">100rb</button>
                        <button type="button" class="py-1.5 px-2 rounded-xl bg-surface-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-border-subtle dark:border-slate-700 text-[11px] font-bold text-text-main dark:text-white transition-all cursor-pointer text-center font-mono" onclick="posApp.setQuickCash(200000)">200rb</button>
                        <button type="button" class="py-1.5 px-2 rounded-xl bg-surface-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-border-subtle dark:border-slate-700 text-[11px] font-bold text-text-main dark:text-white transition-all cursor-pointer text-center font-mono" onclick="posApp.setQuickCash(500000)">500rb</button>
                        <button type="button" class="py-1.5 px-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold transition-all cursor-pointer text-center font-mono" onclick="posApp.setQuickCash('+10000')">+10rb</button>
                        <button type="button" class="py-1.5 px-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold transition-all cursor-pointer text-center font-mono" onclick="posApp.setQuickCash('+20000')">+20rb</button>
                        <button type="button" class="py-1.5 px-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold transition-all cursor-pointer text-center font-mono" onclick="posApp.setQuickCash('+50000')">+50rb</button>
                    </div>
                </div>

                <!-- Dynamic Change Display Card -->
                <div id="modalChangeCard" class="p-3.5 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 flex justify-between items-center transition-all">
                    <span id="modalChangeLabel" class="text-xs font-bold text-text-main dark:text-white">Kembalian Pelanggan:</span>
                    <span id="modalChangeDisplay" class="font-mono font-extrabold text-xl text-emerald-600 dark:text-emerald-400">Rp 0</span>
                </div>
            </div>

            <!-- QRIS Dinamis Section -->
            <div id="qrisSection" style="display: none;" class="p-4 bg-surface-white dark:bg-slate-800/80 rounded-2xl border border-blue-200 dark:border-blue-900/60 flex flex-col items-center gap-3 shadow-2xs">
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 text-xs font-bold border border-blue-200 dark:border-blue-800/60">
                    <span class="material-symbols-outlined text-[16px]">qr_code_2</span>
                    <span>QRIS Standar ASPI MPM Dinamis</span>
                </div>

                <!-- Box Total Tagihan QRIS Super Jelas -->
                <div class="w-full bg-blue-50/70 dark:bg-blue-950/40 p-3.5 rounded-2xl border border-blue-100 dark:border-blue-900/50 text-center">
                    <span class="text-xs text-blue-900 dark:text-blue-300 font-semibold uppercase tracking-wider block">Total Tagihan Terkunci Otomatis</span>
                    <span id="qrisLockedAmountDisplay" class="font-mono font-extrabold text-3xl text-blue-950 dark:text-blue-100 block mt-1">Rp 0</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-[11px] font-bold mt-1.5 border border-emerald-200 dark:border-emerald-800/60">
                        <span class="material-symbols-outlined text-[13px]">lock</span>
                        <span>Nominal Otomatis Terkunci Tanpa Perlu Ketik Manual</span>
                    </span>
                </div>

                <!-- Container Canvas QR Code Besar -->
                <div class="p-3 bg-white rounded-2xl border-2 border-slate-200 shadow-sm inline-block">
                    <div id="qrisQrCodeCanvas" class="flex items-center justify-center min-h-[200px] min-w-[200px]"></div>
                </div>

                <div class="text-xs text-text-muted text-center">
                    Merchant: <strong class="text-text-main dark:text-white" id="qrisMerchantDisplay">{{ $store['qris_merchant_name'] ?? 'MANXMARKET POS' }}</strong>
                </div>

                <!-- App Logos / Badges -->
                <div class="flex items-center justify-center gap-1.5 flex-wrap text-[10px] text-text-muted">
                    <span class="px-2 py-0.5 rounded-md bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-semibold">ShopeePay</span>
                    <span class="px-2 py-0.5 rounded-md bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-semibold">DANA</span>
                    <span class="px-2 py-0.5 rounded-md bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-semibold">GoPay</span>
                    <span class="px-2 py-0.5 rounded-md bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-semibold">BCA Mobile</span>
                    <span class="px-2 py-0.5 rounded-md bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-semibold">Livin' Mandiri</span>
                    <span class="px-2 py-0.5 rounded-md bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-semibold">BRImo</span>
                    <span class="px-2 py-0.5 rounded-md bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-semibold">OVO</span>
                </div>
            </div>

            <!-- Hutang/Tempo Section -->
            <div id="hutangSection" style="display: none;" class="flex flex-col gap-3.5">
                <!-- Customer Debt Status Summary -->
                <div id="modalCustomerDebtSummary"></div>

                <!-- Down Payment / DP Field -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-text-main dark:text-white" for="modalHutangDpAmount">Uang Muka / DP Dibayar Sekarang (Rp)</label>
                        <span class="text-[10px] text-text-muted dark:text-slate-400 font-medium">Bisa 0 jika tempo 100%</span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-mono font-bold text-sm text-text-muted">Rp</span>
                        <input type="number" id="modalHutangDpAmount" class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 font-mono font-bold text-base text-text-main dark:text-white outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all" value="0" min="0" placeholder="0" oninput="posApp.calculateHutangBreakdown()">
                    </div>
                </div>

                <!-- Tanggal Jatuh Tempo -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-text-main dark:text-white" for="modalDueDate">Tanggal Jatuh Tempo Pembayaran</label>
                        <div class="flex items-center gap-1.5" id="dueDatePresetGroup">
                            <button type="button" id="btnDays7" class="px-2.5 py-1 rounded-lg border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 text-text-muted dark:text-slate-300 text-[11px] font-semibold transition-all hover:border-amber-400 cursor-pointer" onclick="posApp.setModalDueDateDays(7)">7 Hari</button>
                            <button type="button" id="btnDays14" class="px-2.5 py-1 rounded-lg border border-amber-800 bg-amber-700 text-white text-[11px] font-bold transition-all cursor-pointer shadow-xs" onclick="posApp.setModalDueDateDays(14)">14 Hari</button>
                            <button type="button" id="btnDays30" class="px-2.5 py-1 rounded-lg border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 text-text-muted dark:text-slate-300 text-[11px] font-semibold transition-all hover:border-amber-400 cursor-pointer" onclick="posApp.setModalDueDateDays(30)">30 Hari</button>
                        </div>
                    </div>
                    <input type="date" id="modalDueDate" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-900 border border-border-subtle dark:border-slate-700 text-xs font-semibold text-text-main dark:text-white outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="{{ date('Y-m-d', strtotime('+14 days')) }}" onchange="posApp.calculateHutangBreakdown()">
                </div>

                <!-- Live Debt Calculation Notice Box -->
                <div id="modalDebtNotice" class="p-3.5 rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-amber-950 dark:text-amber-200 text-xs flex flex-col gap-1.5 shadow-2xs"></div>
            </div>
        </div>
        <div class="modal-footer flex items-center justify-between">
            <button type="button" class="px-4 py-2.5 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-xs font-bold text-text-muted hover:text-text-main transition-colors cursor-pointer" onclick="closeModal('paymentModal')">Batal</button>
            <button type="button" id="confirmPayBtn" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm hover:shadow transition-all flex items-center gap-2 cursor-pointer" onclick="posApp.submitCheckout()">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">verified</span>
                <span>Konfirmasi &amp; Selesaikan Transaksi</span>
            </button>
        </div>
    </div>
</div>

@include('components.thermal-receipt')

<!-- Mobile Sticky Bottom Cart Dock Bar (Visible when scrolled past cart on mobile/tablet) -->
<div id="mobileCartFloatWrap" class="fixed bottom-0 inset-x-0 z-40 lg:hidden transform transition-all duration-200 ease-out translate-y-full opacity-0 pointer-events-none">
    <div class="bg-surface-white dark:bg-slate-900 border-t border-border-subtle dark:border-slate-800 px-4 py-2.5 shadow-xl flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="relative flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px] text-primary dark:text-blue-400">shopping_cart</span>
                <span id="mobileCartFloatBadge" class="absolute -top-1.5 -right-2 text-[10px] font-mono tabular-nums font-extrabold px-1.5 py-0.2 rounded-full bg-blue-600 text-white min-w-[18px] text-center shadow-xs">0</span>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] uppercase font-bold tracking-wider text-text-muted dark:text-slate-400 leading-none">Total Belanja</div>
                <div id="mobileCartFloatTotal" class="font-mono tabular-nums font-extrabold text-base text-slate-900 dark:text-white leading-tight mt-0.5 truncate">Rp 0</div>
            </div>
        </div>
        <button type="button" onclick="posApp && posApp.scrollToCart()" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs flex items-center gap-1.5 active:scale-95 transition-all cursor-pointer shrink-0" aria-label="Ke Keranjang Kasir">
            <span>Ke Keranjang</span>
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_upward</span>
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    @php
    $productsJson = $products->map(function($p) {
        $rule = $p->activeWholesaleRule;
        $wholesaleDiscount = $rule ? match ($rule->discount_type) {
            'percentage' => (float) $p->retail_price * (float) $rule->discount_value / 100,
            'special_price' => max(0, (float) $p->retail_price - (float) $rule->discount_value),
            default => (float) $rule->discount_value,
        } : 0;
        return [
            'id' => $p->id,
            'category_id' => $p->category_id,
            'sku' => $p->sku,
            'barcode' => $p->barcode,
            'name' => $p->name,
            'unit' => $p->unit,
            'retail_price' => (float)$p->retail_price,
            'stock' => (int)$p->stock,
            'min_stock' => (int)$p->min_stock,
            'category_slug' => $p->category->slug ?? '',
            'category_name' => $p->category->name ?? '',
            'wholesale_min_qty' => $p->activeWholesaleRule->min_qty ?? null,
            'wholesale_discount_type' => $p->activeWholesaleRule->discount_type ?? null,
            'wholesale_discount_value' => $p->activeWholesaleRule ? (float)$p->activeWholesaleRule->discount_value : null,
            'wholesale_final_price' => $rule && $rule->rule_type === 'per_item_qty' ? max(0, (float) $p->retail_price - $wholesaleDiscount) : null,
            'image_url' => $p->image_url,
        ];
    });

    $customersJson = $customers->map(function($c) {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'phone' => $c->phone,
            'credit_limit' => (float)$c->credit_limit,
            'current_debt' => (float)($c->current_debt ?? 0),
        ];
    });
    @endphp

    const PRODUCTS_DATA = @json($productsJson);
    const CUSTOMERS_DATA = @json($customersJson);

    const QRIS_CONFIG = {
        staticQris: @json($store['qris_static_string'] ?? ''),
        merchantName: @json($store['qris_merchant_name'] ?? 'MANXMARKET POS')
    };

    let posApp = null;
    document.addEventListener('DOMContentLoaded', () => {
        posApp = new PosEngine(PRODUCTS_DATA, CUSTOMERS_DATA, QRIS_CONFIG);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'F4') {
            e.preventDefault();
            if (posApp) posApp.openPaymentModal();
        } else if (e.key === 'F2') {
            e.preventDefault();
            const input = document.getElementById('searchInput');
            if (input) input.focus();
        } else if (e.key === 'Escape') {
            // Refokus pencarian jika modal ditutup
            const input = document.getElementById('searchInput');
            if (input) setTimeout(() => input.focus(), 120);
        } else {
            // Scanner autofocus: Jika kasir scan barcode saat kursor berada di luar form input/modal
            const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            const isModalOpen = document.querySelector('.modal-overlay.active, .modal-backdrop:not(.hidden)');
            if (!isModalOpen && activeTag !== 'input' && activeTag !== 'textarea' && activeTag !== 'select') {
                const searchInput = document.getElementById('searchInput');
                if (searchInput && e.key && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                    searchInput.focus();
                }
            }
        }
    });
</script>
@endpush
