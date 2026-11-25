@extends('layouts.app')

@section('title', 'Metode Pembayaran & Pengaturan Toko - MANXMARKET')
@section('page_title', 'Metode Pembayaran & Pengaturan Toko')

@section('content')
<div class="flex flex-col gap-6 max-w-5xl mx-auto w-full">
    <!-- Header Section -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl p-6 border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">account_balance_wallet</span>
            </div>
            <div>
                <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight">Metode Pembayaran &amp; Pengaturan Toko</h1>
                <p class="text-xs text-text-muted mt-0.5 leading-relaxed">Kelola saluran pembayaran kasir (Kas Tunai, QRIS Dinamis ASPI, dan Tempo/Kasbon) serta konfigurasi struk kasir.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 px-4 py-3.5 rounded-2xl flex items-center gap-2.5 text-xs font-semibold shadow-xs">
            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[20px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Section 1: Saluran Metode Pembayaran Aktif -->
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-sm text-text-main dark:text-white">Saluran Metode Pembayaran POS</h2>
                <p class="text-[11px] text-text-muted">3 metode transaksi yang tersedia langsung pada meja kasir dan checkout modal.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Card 1: Kas Tunai -->
            <div class="p-4 rounded-2xl bg-surface-white dark:bg-slate-900 border border-border-subtle dark:border-slate-800 flex flex-col justify-between gap-3 shadow-xs">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">payments</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-bold border border-emerald-200 dark:border-emerald-800/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Aktif Otomatis
                    </span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-text-main dark:text-white">Kas Tunai (Cash POS)</h3>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">Pembayaran tunai langsung di kasir dengan kalkulasi kembalian otomatis dan tombol nominal cepat (+10rb, +50rb, uang pas).</p>
                </div>
                <div class="pt-2.5 border-t border-border-subtle dark:border-slate-800 flex items-center justify-between text-[11px] text-text-muted">
                    <span>Validasi Kembalian</span>
                    <strong class="text-text-main dark:text-white font-mono">Real-time (F4)</strong>
                </div>
            </div>

            <!-- Card 2: QRIS Dinamis ASPI -->
            <div class="p-4 rounded-2xl bg-surface-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/60 flex flex-col justify-between gap-3 shadow-xs">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-200 dark:border-blue-800/60 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">qr_code_2</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 text-[10px] font-bold border border-blue-200 dark:border-blue-800/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        Dinamis ASPI
                    </span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-text-main dark:text-white">QRIS Dinamis Bank &amp; E-Wallet</h3>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">Scan QRIS via ShopeePay, DANA Bisnis, BCA, GoPay, OVO, Mandiri, BRI. Nominal tagihan otomatis terkunci di smartphone pembeli.</p>
                </div>
                <div class="pt-2.5 border-t border-blue-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-text-muted">
                    <span>Integrasi Checksum</span>
                    <strong class="text-blue-600 dark:text-blue-400 font-mono">CRC-16 ASPI</strong>
                </div>
            </div>

            <!-- Card 3: Tempo / Kasbon -->
            <div class="p-4 rounded-2xl bg-surface-white dark:bg-slate-900 border border-amber-200 dark:border-amber-900/60 flex flex-col justify-between gap-3 shadow-xs">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">event_note</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-bold border border-amber-200 dark:border-amber-800/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Pelanggan Terdaftar
                    </span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-text-main dark:text-white">Pembayaran Tempo (Kasbon)</h3>
                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">Pencatatan piutang dagang bagi pembeli grosir dengan pembatasan plafon limit kredit dan audit jejak pembayaran cicilan.</p>
                </div>
                <div class="pt-2.5 border-t border-amber-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-text-muted">
                    <span>Default Tempo</span>
                    <strong class="text-amber-700 dark:text-amber-400 font-mono">{{ $settings['default_due_days'] ?? '14' }} Hari</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form method="POST" action="{{ route('settings.update') }}" class="flex flex-col gap-6">
        @csrf

        <!-- Section 2: Konfigurasi QRIS Dinamis & Simulator -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-3xl border border-border-subtle dark:border-slate-800 shadow-sm p-6 flex flex-col gap-5">
            <div class="flex items-center justify-between pb-4 border-b border-border-subtle dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-sm text-text-main dark:text-white">Konfigurasi QRIS Dinamis (Standar ASPI MPM)</h2>
                        <p class="text-[11px] text-text-muted">Masukkan identitas merchant dan payload QRIS statis toko dari penyedia (DANA Bisnis, ShopeePay, Bank BCA, dll).</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Form Inputs -->
                <div class="lg:col-span-7 flex flex-col gap-4 text-xs">
                    <div>
                        <label class="font-bold text-text-main dark:text-white block mb-1.5" for="qris_merchant_name">Nama Merchant / Usaha di QRIS *</label>
                        <input type="text" id="qris_merchant_name" name="qris_merchant_name" value="{{ $settings['qris_merchant_name'] ?? 'MANXMARKET POS' }}" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-semibold outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all" placeholder="Contoh: MANXMARKET GROSIR" oninput="updateLiveQrisPreview()">
                        <span class="text-[11px] text-text-muted mt-1 block">Nama ini akan tercantum di bawah kode QR pada layar kasir dan struk.</span>
                    </div>

                    <div>
                        <label class="font-bold text-text-main dark:text-white block mb-1.5" for="qris_static_string">Payload String QRIS Statis Toko (Standar ASPI MPM) *</label>
                        <textarea id="qris_static_string" name="qris_static_string" rows="4" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono text-[11px] outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all" placeholder="00020101021126..." oninput="updateLiveQrisPreview()">{{ $settings['qris_static_string'] ?? '' }}</textarea>
                        <span class="text-[11px] text-text-muted mt-1 block leading-relaxed">
                            Sistem secara otomatis menyisipkan tag nominal dinamis (Tag 54) dan menghitung ulang <strong>CRC-16 CCITT (Tag 63)</strong> sehingga pembeli tidak perlu mengetik nominal secara manual.
                        </span>
                    </div>

                    <!-- E-wallet Compatibility Pills -->
                    <div class="p-3 bg-background-canvas dark:bg-slate-800/60 rounded-2xl border border-border-subtle dark:border-slate-700 flex flex-col gap-2">
                        <span class="text-[11px] font-bold text-text-main dark:text-white flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-blue-600">verified</span>
                            Kompatibilitas QRIS Nasional (Interoperable)
                        </span>
                        <div class="flex items-center gap-1.5 flex-wrap text-[10px] text-text-muted font-medium">
                            <span class="px-2 py-0.5 rounded-md bg-surface-white dark:bg-slate-700 border border-border-subtle dark:border-slate-600">ShopeePay</span>
                            <span class="px-2 py-0.5 rounded-md bg-surface-white dark:bg-slate-700 border border-border-subtle dark:border-slate-600">DANA Bisnis</span>
                            <span class="px-2 py-0.5 rounded-md bg-surface-white dark:bg-slate-700 border border-border-subtle dark:border-slate-600">BCA Mobile</span>
                            <span class="px-2 py-0.5 rounded-md bg-surface-white dark:bg-slate-700 border border-border-subtle dark:border-slate-600">GoPay</span>
                            <span class="px-2 py-0.5 rounded-md bg-surface-white dark:bg-slate-700 border border-border-subtle dark:border-slate-600">Livin' Mandiri</span>
                            <span class="px-2 py-0.5 rounded-md bg-surface-white dark:bg-slate-700 border border-border-subtle dark:border-slate-600">BRImo</span>
                            <span class="px-2 py-0.5 rounded-md bg-surface-white dark:bg-slate-700 border border-border-subtle dark:border-slate-600">OVO</span>
                        </div>
                    </div>
                </div>

                <!-- Live Simulator & QR Preview Tool -->
                <div class="lg:col-span-5 flex flex-col gap-3">
                    <div class="p-4 rounded-2xl bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-700 flex flex-col items-center gap-3 shadow-2xs">
                        <div class="flex items-center justify-between w-full">
                            <span class="text-[11px] font-bold text-blue-900 dark:text-blue-300 uppercase tracking-wider">Simulator Scan QRIS</span>
                            <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-100 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">Siap Scan</span>
                        </div>

                        <!-- Sample Nominal Input -->
                        <div class="w-full bg-surface-white dark:bg-slate-800 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700 flex items-center justify-between gap-2">
                            <label for="testQrisAmount" class="text-[11px] text-text-muted font-medium">Uji Coba Nominal (Rp):</label>
                            <div class="flex items-center gap-1 font-mono font-bold text-xs">
                                <span>Rp</span>
                                <input type="number" id="testQrisAmount" value="50000" step="5000" class="w-24 text-right font-mono font-bold bg-background-canvas dark:bg-slate-900 px-2 py-1 rounded-lg border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-blue-500" oninput="updateLiveQrisPreview()">
                            </div>
                        </div>

                        <!-- Live QR Canvas Container -->
                        <div class="p-3 bg-white rounded-2xl border-2 border-slate-200 shadow-sm inline-block">
                            <div id="settingsQrisCanvas" class="flex items-center justify-center min-h-[160px] min-w-[160px]"></div>
                        </div>

                        <div class="text-center">
                            <span id="previewMerchantLabel" class="font-bold text-xs text-text-main dark:text-white block">{{ $settings['qris_merchant_name'] ?? 'MANXMARKET POS' }}</span>
                            <span id="previewAmountLabel" class="text-xs font-mono font-extrabold text-blue-700 dark:text-blue-400 mt-0.5 block">Rp 50.000</span>
                        </div>

                        <div class="text-[10px] text-text-muted text-center leading-relaxed">
                            Arahkan kamera smartphone dengan aplikasi ShopeePay / DANA / M-Banking Anda ke kode QR di atas untuk menguji coba keabsahan payload secara langsung.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Konfigurasi Pembayaran Tempo / Kasbon -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-3xl border border-border-subtle dark:border-slate-800 shadow-sm p-6 flex flex-col gap-5 transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-border-subtle dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/40 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">calendar_month</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-sm text-text-main dark:text-white">Konfigurasi Pembayaran Tempo &amp; Piutang Kasbon</h2>
                        <p class="text-[11px] text-text-muted mt-0.5">Atur standar tenor pelunasan piutang grosir, batas jatuh tempo otomatis, dan kendali risiko likuiditas.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40 text-[11px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Fitur Tempo Aktif
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 text-xs">
                <!-- Left: Setting Input & Presets -->
                <div class="lg:col-span-7 flex flex-col gap-4">
                    <div>
                        <label class="font-bold text-text-main dark:text-white block mb-1.5" for="default_due_days">
                            Standar Masa Jatuh Tempo (Tenor Kasbon) *
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="relative w-40">
                                <input type="number" id="default_due_days" name="default_due_days" value="{{ $settings['default_due_days'] ?? '14' }}" min="1" max="180" class="w-full pl-3.5 pr-12 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono font-bold text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/15 transition-all shadow-xs" required oninput="syncDueDayButtons(this.value)">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-text-muted font-semibold pointer-events-none">Hari</span>
                            </div>
                            <span class="text-xs text-text-muted font-medium">terhitung sejak tanggal nota transaksi kasir</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[11px] font-bold text-text-muted uppercase tracking-wider block mb-2">Pilihan Cepat Standar Tenor:</span>
                        <div class="flex flex-wrap items-center gap-2" id="dueDaysPresetContainer">
                            <button type="button" class="due-preset-btn px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer {{ ($settings['default_due_days'] ?? '14') == '7' ? 'bg-amber-500 text-slate-900 border-slate-900 shadow-xs font-bold' : 'bg-background-canvas dark:bg-slate-800 border-border-subtle dark:border-slate-700 text-text-main dark:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600' }}" onclick="selectDueDays(7, this)">
                                7 Hari (1 Minggu)
                            </button>
                            <button type="button" class="due-preset-btn px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer {{ ($settings['default_due_days'] ?? '14') == '14' ? 'bg-amber-500 text-slate-900 border-slate-900 shadow-xs font-bold' : 'bg-background-canvas dark:bg-slate-800 border-border-subtle dark:border-slate-700 text-text-main dark:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600' }}" onclick="selectDueDays(14, this)">
                                14 Hari (2 Minggu / Standar)
                            </button>
                            <button type="button" class="due-preset-btn px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer {{ ($settings['default_due_days'] ?? '14') == '30' ? 'bg-amber-500 text-slate-900 border-slate-900 shadow-xs font-bold' : 'bg-background-canvas dark:bg-slate-800 border-border-subtle dark:border-slate-700 text-text-main dark:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600' }}" onclick="selectDueDays(30, this)">
                                30 Hari (1 Bulan)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Rules & Safeguard Card -->
                <div class="lg:col-span-5 p-4 rounded-2xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 dark:border-amber-500/30 flex flex-col justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-1.5 font-bold text-xs text-amber-800 dark:text-amber-300">
                            <span class="material-symbols-outlined text-[17px]">shield</span>
                            <span>Kendali Risiko Piutang Grosir</span>
                        </div>
                        <ul class="mt-2 flex flex-col gap-1.5 text-[11px] text-text-muted leading-relaxed">
                            <li class="flex items-start gap-1.5">
                                <span class="material-symbols-outlined text-[14px] text-amber-600 dark:text-amber-400 mt-0.5 shrink-0">check_circle</span>
                                <span>Pembayaran tempo hanya diizinkan untuk <strong>Pelanggan Terdaftar</strong> dengan plafon aktif.</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="material-symbols-outlined text-[14px] text-amber-600 dark:text-amber-400 mt-0.5 shrink-0">check_circle</span>
                                <span>Plafon kredit divalidasi langsung oleh sistem kasir saat checkout kasbon (F4).</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="material-symbols-outlined text-[14px] text-amber-600 dark:text-amber-400 mt-0.5 shrink-0">check_circle</span>
                                <span>Pencatatan cicilan dan audit saldo dapat dipantau di menu <strong>Buku Piutang &amp; Kasbon</strong>.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Identitas Toko & Header/Footer Struk -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-3xl border border-border-subtle dark:border-slate-800 shadow-sm p-6 flex flex-col gap-4">
            <div class="flex items-center gap-3 pb-3 border-b border-border-subtle dark:border-slate-800">
                <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                </div>
                <div>
                    <h2 class="font-bold text-sm text-text-main dark:text-white">Identitas Usaha &amp; Format Nota Cetak (Thermal Struk)</h2>
                    <p class="text-[11px] text-text-muted">Informasi resmi yang tercetak pada kop dan catatan kaki nota struk kasir.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 text-xs">
                <!-- Inputs -->
                <div class="lg:col-span-7 flex flex-col gap-4">
                    <div>
                        <label class="font-bold text-text-main dark:text-white block mb-1.5" for="store_name">Nama Toko / Badan Usaha *</label>
                        <input type="text" id="store_name" name="store_name" value="{{ $settings['store_name'] ?? 'MANXMARKET GROSIR' }}" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-bold outline-none focus:border-purple-500" required oninput="updateLiveReceiptPreview()">
                    </div>

                    <div>
                        <label class="font-bold text-text-main dark:text-white block mb-1.5" for="store_address">Alamat Toko Lengkap *</label>
                        <textarea id="store_address" name="store_address" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-purple-500" required oninput="updateLiveReceiptPreview()">{{ $settings['store_address'] ?? 'Pasar Brangsong, Kendal' }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-bold text-text-main dark:text-white block mb-1.5" for="store_phone">Nomor Telepon / WhatsApp</label>
                            <input type="text" id="store_phone" name="store_phone" value="{{ $settings['store_phone'] ?? '08123456789' }}" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-purple-500" oninput="updateLiveReceiptPreview()">
                        </div>
                        <div>
                            <label class="font-bold text-text-main dark:text-white block mb-1.5" for="receipt_footer">Catatan Kaki Struk (Footer)</label>
                            <input type="text" id="receipt_footer" name="receipt_footer" value="{{ $settings['receipt_footer'] ?? 'Terima kasih atas kunjungan Anda!' }}" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-purple-500" oninput="updateLiveReceiptPreview()">
                        </div>
                    </div>
                </div>

                <!-- Live Receipt Thermal Preview -->
                <div class="lg:col-span-5 flex flex-col gap-2">
                    <span class="text-[11px] font-bold text-text-muted uppercase tracking-wider">Preview Kop Nota Struk 58mm/80mm</span>
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 font-mono text-[11px] text-slate-800 dark:text-slate-200 shadow-sm flex flex-col gap-2">
                        <div class="text-center pb-2 border-b border-dashed border-slate-400">
                            <span id="previewStoreName" class="font-bold text-sm block tracking-wider uppercase">{{ $settings['store_name'] ?? 'MANXMARKET GROSIR' }}</span>
                            <span id="previewStoreAddress" class="text-[10px] text-slate-600 dark:text-slate-400 block mt-0.5">{{ $settings['store_address'] ?? 'Pasar Brangsong, Kendal' }}</span>
                            <span id="previewStorePhone" class="text-[10px] text-slate-600 dark:text-slate-400 block">Telp: {{ $settings['store_phone'] ?? '08123456789' }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-600 dark:text-slate-400">
                            <span>No: INV-20261102-001</span>
                            <span>{{ date('d/m/Y H:i') }}</span>
                        </div>
                        <div class="py-1 border-y border-dashed border-slate-300 dark:border-slate-700 text-[10px] flex flex-col gap-1">
                            <div class="flex justify-between">
                                <span>Beras Ramos Super (5 Kg)</span>
                                <span>65.000</span>
                            </div>
                            <div class="flex justify-between text-slate-700 dark:text-slate-200">
                                <span>Minyak Goreng 2L x 2</span>
                                <span>68.000</span>
                            </div>
                        </div>
                        <div class="flex justify-between font-bold text-xs pt-1">
                            <span>TOTAL BAYAR</span>
                            <span>Rp 133.000</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-600 dark:text-slate-400">
                            <span>METODE</span>
                            <span class="font-bold">QRIS DINAMIS / TUNAI</span>
                        </div>
                        <div class="text-center pt-2 border-t border-dashed border-slate-400 text-[10px] text-slate-600 dark:text-slate-400">
                            <span id="previewReceiptFooter">{{ $settings['receipt_footer'] ?? 'Terima kasih atas kunjungan Anda!' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Action Bar (Placed directly under Section 4: Identitas Usaha) -->
        <div class="p-4 sm:p-5 bg-surface-white dark:bg-slate-900 rounded-3xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors">
            <div class="flex items-center gap-2.5 text-xs text-text-muted">
                <span class="material-symbols-outlined text-[22px] text-emerald-600 dark:text-emerald-400">check_circle</span>
                <span>Pastikan seluruh data konfigurasi metode pembayaran dan identitas usaha telah sesuai sebelum menyimpan.</span>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold text-xs shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Simpan Seluruh Pengaturan</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// EMVCo QRIS CCITT-16 Checksum Calculation
function crc16_ccitt(str) {
    let crc = 0xFFFF;
    for (let c = 0; c < str.length; c++) {
        crc ^= str.charCodeAt(c) << 8;
        for (let i = 0; i < 8; i++) {
            if (crc & 0x8000) {
                crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
            } else {
                crc = (crc << 1) & 0xFFFF;
            }
        }
    }
    return crc.toString(16).toUpperCase().padStart(4, '0');
}

// Convert Static QRIS to Dynamic QRIS
function makeDynamicQris(staticQris, amount) {
    let str = (staticQris || '').trim();
    if (!str) {
        str = '00020101021126680016ID.SHOPEE.WWW01189360081100112233440215ID10200500600700303UME51440014ID.CO.QRIS.WWW0215ID10200500600700303UME5204541153033605802ID5918TOKO SHOPEE BERKAH6006KENDAL61055137162070703A0163041234';
    }

    const last6304 = str.lastIndexOf('6304');
    if (last6304 !== -1) {
        str = str.substring(0, last6304);
    }

    // Set Dynamic Indicator Tag 010212
    str = str.replace('010211', '010212');

    // Insert Tag 54 (Transaction Amount)
    const amtStr = String(Math.round(amount || 0));
    const amtLen = String(amtStr.length).padStart(2, '0');
    const tag54 = '54' + amtLen + amtStr;

    const tag58Idx = str.indexOf('5802ID');
    if (tag58Idx !== -1) {
        str = str.substring(0, tag58Idx) + tag54 + str.substring(tag58Idx);
    } else {
        str = str + tag54;
    }

    // Append CRC-16 Tag 6304
    const toCheck = str + '6304';
    const crc = crc16_ccitt(toCheck);
    return toCheck + crc;
}

function updateLiveQrisPreview() {
    const rawStatic = document.getElementById('qris_static_string')?.value || '';
    const merchantName = document.getElementById('qris_merchant_name')?.value || 'MANXMARKET POS';
    const amount = Number(document.getElementById('testQrisAmount')?.value || 50000);

    const merchLabel = document.getElementById('previewMerchantLabel');
    if (merchLabel) merchLabel.textContent = merchantName;

    const amtLabel = document.getElementById('previewAmountLabel');
    if (amtLabel) amtLabel.textContent = 'Rp ' + amount.toLocaleString('id-ID');

    const canvas = document.getElementById('settingsQrisCanvas');
    if (!canvas || typeof QRCode === 'undefined') return;

    canvas.innerHTML = '';
    const payload = makeDynamicQris(rawStatic, amount);
    try {
        new QRCode(canvas, {
            text: payload,
            width: 160,
            height: 160,
            colorDark: "#0b1c30",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
        const qrImage = canvas.querySelector('img');
        if (qrImage) qrImage.alt = `Kode QRIS dinamis untuk nominal Rp ${amount.toLocaleString('id-ID')}`;
    } catch (e) {
        canvas.innerHTML = '<span class="text-xs text-red-500">QR Gagal: ' + e.message + '</span>';
    }
}

function updateLiveReceiptPreview() {
    const name = document.getElementById('store_name')?.value || 'MANXMARKET GROSIR';
    const addr = document.getElementById('store_address')?.value || 'Pasar Brangsong, Kendal';
    const phone = document.getElementById('store_phone')?.value || '08123456789';
    const footer = document.getElementById('receipt_footer')?.value || 'Terima kasih atas kunjungan Anda!';

    const pName = document.getElementById('previewStoreName');
    const pAddr = document.getElementById('previewStoreAddress');
    const pPhone = document.getElementById('previewStorePhone');
    const pFooter = document.getElementById('previewReceiptFooter');

    if (pName) pName.textContent = name;
    if (pAddr) pAddr.textContent = addr;
    if (pPhone) pPhone.textContent = 'Telp: ' + phone;
    if (pFooter) pFooter.textContent = footer;
}

function selectDueDays(days, btn) {
    const input = document.getElementById('default_due_days');
    if (input) input.value = days;
    syncDueDayButtons(days);
}

function syncDueDayButtons(val) {
    const buttons = document.querySelectorAll('.due-preset-btn');
    buttons.forEach(b => {
        const text = b.textContent;
        const matches = text.includes(`${val} Hari`);
        if (matches) {
            b.className = 'due-preset-btn px-3.5 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer bg-amber-500 text-slate-900 border-slate-900 shadow-xs';
        } else {
            b.className = 'due-preset-btn px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer bg-background-canvas dark:bg-slate-800 border-border-subtle dark:border-slate-700 text-text-main dark:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    updateLiveQrisPreview();
    updateLiveReceiptPreview();
});
</script>
@endpush

