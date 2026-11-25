// assets/js/app.js - MANXMARKET POS & Grosir Frontend Interactions
// Complies with Antislop R-26 (functional controls) and R-32 (keyboard accessibility)

// Global Modal Handlers
function toggleSidebar(open) {
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (sidebar) {
        if (open === undefined) {
            sidebar.classList.toggle('open');
            if (backdrop) backdrop.classList.toggle('active');
        } else if (open) {
            sidebar.classList.add('open');
            if (backdrop) backdrop.classList.add('active');
        } else {
            sidebar.classList.remove('open');
            if (backdrop) backdrop.classList.remove('active');
        }
    }
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('active');
        const firstInput = modal.querySelector('input:not([type=hidden]), select, textarea, button:not(.modal-close-btn)');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 50);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        if (modal.classList.contains('modal-backdrop')) {
            modal.classList.add('hidden');
        }
    }
}

// Close active modal or mobile sidebar on Escape key press (Antislop R-32 Keyboard accessibility)
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (typeof window._resolveConfirmDialog === 'function' && window._confirmResolve) {
            window._resolveConfirmDialog(false);
        }
        const activeModals = document.querySelectorAll('.modal-overlay.active, .modal-backdrop:not(.hidden)');
        activeModals.forEach(m => {
            m.classList.remove('active');
            if (m.classList.contains('modal-backdrop')) {
                m.classList.add('hidden');
            }
        });
        const sidebar = document.getElementById('appSidebar');
        if (sidebar && sidebar.classList.contains('open')) {
            toggleSidebar(false);
        }
    }
});

// Theme Toggle Engine (Neutral Operational System - Light & Dark Mode)
window.toggleTheme = function() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('manxmarket_theme', isDark ? 'dark' : 'light');
    updateThemeUI(isDark);
    if (typeof showToast === 'function') {
        showToast(isDark ? 'Tema Gelap aktif (Shift Malam)' : 'Tema Terang aktif (Shift Siang)', 'info');
    }
};

function updateThemeUI(isDark) {
    const icon = document.getElementById('themeToggleIcon');
    if (icon) {
        icon.textContent = isDark ? 'light_mode' : 'dark_mode';
    }
    const btn = document.getElementById('themeToggleBtn');
    if (btn) {
        btn.setAttribute('title', isDark ? 'Beralih ke Tema Terang (F9)' : 'Beralih ke Tema Gelap (F9)');
    }
}

// Initial Theme Sync on page load
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    updateThemeUI(isDark);

    // Otorisasi UI: Sembunyikan kontrol pembatalan nota (VOID) dari staf kasir
    if (window.currentUserRole && window.currentUserRole !== 'owner') {
        document.querySelectorAll('.owner-only, [data-role-required="owner"], .btn-void, #voidModal, [onclick*="openVoidDialog"]').forEach(el => {
            el.remove();
        });
    }
});

// Shortcut F9 to Toggle Theme
document.addEventListener('keydown', function(e) {
    if (e.key === 'F9') {
        e.preventDefault();
        window.toggleTheme();
    }
});

// Format Rupiah Helper
function formatRp(num) {
    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

// Escape HTML Helper
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

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

// Convert Static QRIS (e.g. from DANA Bisnis / Shopee) to Dynamic QRIS with exact amount
function makeDynamicQris(staticQris, amount) {
    let str = (staticQris || '').trim();
    if (!str) {
        // Fallback default sample QRIS (Standar ASPI Nasional) so it NEVER fails to render
        str = '00020101021126680016ID.SHOPEE.WWW01189360081100112233440215ID10200500600700303UME51440014ID.CO.QRIS.WWW0215ID10200500600700303UME5204541153033605802ID5918TOKO SHOPEE BERKAH6006KENDAL61055137162070703A0163041234';
    }

    // Strip CRC tag (6304...)
    const last6304 = str.lastIndexOf('6304');
    if (last6304 !== -1) {
        str = str.substring(0, last6304);
    }

    // Switch tag 01 from 11 (Static) to 12 (Dynamic)
    str = str.replace('010211', '010212');

    // Format Tag 54 (Transaction Amount in Rupiah)
    const amountVal = Math.round(Number(amount || 0)).toString();
    const amountTag = '54' + amountVal.length.toString().padStart(2, '0') + amountVal;

    // Standard position: insert right before Tag 5802ID
    let payload = '';
    if (str.indexOf('5802ID') !== -1) {
        const parts = str.split('5802ID');
        let beforeCountry = parts[0];
        // Remove existing tag 54 between 5303360 and 5802ID if any
        if (beforeCountry.indexOf('5303360') !== -1) {
            const subParts = beforeCountry.split('5303360');
            beforeCountry = subParts[0] + '5303360';
        }
        payload = beforeCountry + amountTag + '5802ID' + parts[1];
    } else {
        payload = str + amountTag;
    }

    payload += '6304';
    const crc = crc16_ccitt(payload);
    return payload + crc;
}

// Thermal Receipt Modal Display
function showReceiptModal(tx) {
    const modal = document.getElementById('receiptModal');
    const content = document.getElementById('receiptContent');
    if (!modal || !content) return;

    const store = tx.store || {};
    const storeName = store.store_name || 'MANXMARKET POS & GROSIR';
    const storeAddress = store.store_address || 'Pasar Induk Kota Dagang';
    const storePhone = store.store_phone || '0812-8899-7766';
    const footerNotes = store.receipt_footer || 'Terima kasih atas kunjungan Anda.';
    const receiptStoreName = escapeHtml(storeName);
    const receiptStoreAddress = escapeHtml(storeAddress);
    const receiptStorePhone = escapeHtml(storePhone);
    const receiptFooterNotes = escapeHtml(footerNotes);
    const receiptInvoiceNumber = escapeHtml(tx.invoice_number);
    const receiptDate = escapeHtml(tx.date || tx.transaction_date);
    const receiptCashier = escapeHtml(tx.cashier || tx.cashier_name || 'Kasir');
    const receiptCustomer = escapeHtml(tx.customer || tx.customer_name || 'Pelanggan Umum');
    const rawPaymentMethod = tx.payment_method || '';
    const receiptPaymentMethod = escapeHtml(rawPaymentMethod);
    const receiptPaymentStatus = escapeHtml(tx.payment_status);
    const receiptDueDate = escapeHtml(tx.due_date);

    let itemsHtml = '';
    (tx.items || []).forEach(item => {
        const itemTotal = Number(item.subtotal_item || (item.unit_price * item.qty));
        const disc = Number(item.discount_per_item || 0);
        const itemName = escapeHtml(item.name || item.product_name_snapshot);
        const itemQty = escapeHtml(item.qty);
        itemsHtml += `
            <div style="margin-bottom: 5px;">
                <div style="font-weight: bold;">${itemName}</div>
                <div class="receipt-row">
                    <span>${itemQty} x ${formatRp(item.unit_price)} ${disc > 0 ? '(disc -' + formatRp(disc) + ')' : ''}</span>
                    <span>${formatRp(itemTotal)}</span>
                </div>
            </div>
        `;
    });

    content.innerHTML = `
        <div class="thermal-receipt">
            <div class="receipt-header">
                <div class="receipt-title">${receiptStoreName}</div>
                <div>${receiptStoreAddress}</div>
                <div>Telp: ${receiptStorePhone}</div>
                <div style="margin-top: 6px; font-weight: bold;">NOTA PENJUALAN</div>
            </div>

            <div class="receipt-row">
                <span>Nota:</span>
                <span style="font-weight: bold;">${receiptInvoiceNumber}</span>
            </div>
            <div class="receipt-row">
                <span>Tanggal:</span>
                <span>${receiptDate}</span>
            </div>
            <div class="receipt-row">
                <span>Kasir:</span>
                <span>${receiptCashier}</span>
            </div>
            <div class="receipt-row">
                <span>Pelanggan:</span>
                <span>${receiptCustomer}</span>
            </div>

            <div class="receipt-divider"></div>

            <div style="margin: 8px 0;">
                ${itemsHtml}
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-row">
                <span>Subtotal:</span>
                <span>${formatRp(tx.subtotal)}</span>
            </div>
            ${Number(tx.discount || tx.total_discount || 0) > 0 ? `
            <div class="receipt-row receipt-discount" style="color: #047857 !important;">
                <span>Diskon Grosir:</span>
                <span>-${formatRp(tx.discount || tx.total_discount)}</span>
            </div>` : ''}
            <div class="receipt-row" style="font-size: 14px; font-weight: bold; margin-top: 4px;">
                <span>TOTAL:</span>
                <span>${formatRp(tx.grand_total)}</span>
            </div>
            <div class="receipt-row">
                <span>Metode Bayar:</span>
                <span style="font-weight: bold;">${receiptPaymentMethod}</span>
            </div>
            <div class="receipt-row">
                <span>Nominal Bayar:</span>
                <span>${formatRp(tx.paid_amount)}</span>
            </div>
            ${rawPaymentMethod === 'TUNAI' || rawPaymentMethod === 'tunai' ? `
            <div class="receipt-row">
                <span>Kembalian:</span>
                <span>${formatRp(tx.change_amount)}</span>
            </div>` : ''}
            ${tx.remaining_debt > 0 ? `
            <div class="receipt-row receipt-debt" style="color: #b45309 !important; font-weight: bold;">
                <span>Sisa Hutang:</span>
                <span>${formatRp(tx.remaining_debt)}</span>
            </div>
            <div class="receipt-row receipt-debt" style="color: #b45309 !important;">
                <span>Jatuh Tempo:</span>
                <span>${receiptDueDate}</span>
            </div>` : ''}

            <div class="receipt-footer">
                <div>Status: ${receiptPaymentStatus}</div>
                <div style="margin-top: 6px;">${receiptFooterNotes}</div>
            </div>
        </div>
    `;

    openModal('receiptModal');
}

function closeReceiptModal() {
    closeModal('receiptModal');
}

// Dedicated Thermal Receipt Print Function (Isolated Iframe to prevent page leaks)
// Supports dynamic paper width: '58mm' (compact portable/bluetooth) and '80mm' (desktop standard)
window.printThermalReceipt = function(paperWidth) {
    const content = document.getElementById('receiptContent');
    if (!content) {
        window.print();
        return;
    }

    const width = paperWidth || localStorage.getItem('manxmarket_receipt_width') || '58mm';
    localStorage.setItem('manxmarket_receipt_width', width);

    let printFrame = document.getElementById('thermalPrintIframe');
    if (printFrame) {
        printFrame.remove();
    }

    printFrame = document.createElement('iframe');
    printFrame.id = 'thermalPrintIframe';
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.visibility = 'hidden';
    document.body.appendChild(printFrame);

    const is58 = (width === '58mm');
    const pageSize = is58 ? '58mm auto' : '80mm auto';
    const bodyWidth = is58 ? '48mm' : '72mm';
    const fontSize = is58 ? '9.5px' : '11px';
    const titleSize = is58 ? '11.5px' : '13px';
    const padding = is58 ? '2mm 1mm' : '4mm 2mm';

    const frameDoc = printFrame.contentWindow.document;
    frameDoc.open();
    frameDoc.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <title>Struk Pembayaran (${is58 ? '58mm' : '80mm'})</title>
            <style>
                @page {
                    size: ${pageSize};
                    margin: 0;
                }
                * {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                body {
                    margin: 0 auto;
                    padding: ${padding};
                    font-family: 'JetBrains Mono', Consolas, 'Courier New', monospace;
                    font-size: ${fontSize};
                    line-height: 1.3;
                    color: #000000;
                    background: #ffffff;
                    width: ${bodyWidth};
                }
                .thermal-receipt {
                    width: 100%;
                    border: none;
                    padding: 0;
                    margin: 0;
                    max-width: 100%;
                }
                .receipt-header {
                    text-align: center;
                    border-bottom: 1px dashed #000;
                    padding-bottom: 5px;
                    margin-bottom: 5px;
                }
                .receipt-title {
                    font-size: ${titleSize};
                    font-weight: bold;
                    margin-bottom: 2px;
                }
                .receipt-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 2.5px;
                    word-break: break-word;
                }
                .receipt-divider {
                    border-top: 1px dashed #000;
                    margin: 5px 0;
                }
                .receipt-footer {
                    text-align: center;
                    border-top: 1px dashed #000;
                    padding-top: 5px;
                    margin-top: 6px;
                    font-size: ${is58 ? '8.5px' : '10px'};
                }
            </style>
        </head>
        <body>
            ${content.innerHTML}
        </body>
        </html>
    `);
    frameDoc.close();

    setTimeout(() => {
        try {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        } catch (e) {
            window.print();
        }
        setTimeout(() => {
            if (printFrame && printFrame.parentNode) {
                printFrame.parentNode.removeChild(printFrame);
            }
        }, 3000);
    }, 250);
};

// Subtle audio tactile feedback for actions
function playSuccessTone() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        if (ctx.state === 'suspended') ctx.resume();
        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, now); // D5 note
        osc.frequency.exponentialRampToValueAtTime(880, now + 0.12); // A5 note
        gain.gain.setValueAtTime(0.04, now);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.25);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now);
        osc.stop(now + 0.26);
    } catch (e) {
        // Ignored if audio blocked or unsupported
    }
}

function playWarningTone() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        if (ctx.state === 'suspended') ctx.resume();
        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(440, now);
        osc.frequency.exponentialRampToValueAtTime(329.63, now + 0.14); // A4 -> E4 gentle descending
        gain.gain.setValueAtTime(0.04, now);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.28);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now);
        osc.stop(now + 0.29);
    } catch (e) {
        // Ignored if audio blocked or unsupported
    }
}

// Global Toast Notification
window.showToast = function(message, type = 'info') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none max-w-sm w-full px-4';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bgClasses = 
        type === 'error' ? 'bg-red-600 text-white shadow-red-200' :
        type === 'success' ? 'bg-emerald-600 text-white shadow-emerald-200' :
        type === 'warning' ? 'bg-amber-700 text-white shadow-amber-200' :
        'bg-slate-800 text-white shadow-slate-200';

    const iconName = type === 'error' ? 'error' : type === 'success' ? 'check_circle' : type === 'warning' ? 'warning' : 'info';
    toast.className = `p-3 rounded-xl shadow-lg border border-white/20 text-xs font-semibold flex items-center gap-2.5 transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-auto ${bgClasses}`;
    toast.innerHTML = `
        <span class="material-symbols-outlined text-[20px] flex-shrink-0">${iconName}</span>
        <div class="flex-1 leading-snug">${message}</div>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 3200);
};

// Impeccable Interactive User Created Notification Card
window.showUserCreatedToast = function(user, options = {}) {
    if (!user) return;
    playSuccessTone();

    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col gap-2.5 pointer-events-none max-w-[400px] w-full px-4';
        document.body.appendChild(container);
    }

    const role = (user.role || 'pembeli').toLowerCase();
    const roleMeta = {
        owner: {
            title: 'Owner Toko',
            desc: 'Akses penuh ke seluruh operasional',
            badgeText: 'Owner',
            icon: 'shield_person',
            gradient: 'from-purple-600 to-indigo-600',
            bgLight: 'bg-purple-50 dark:bg-purple-950/70',
            textColor: 'text-purple-700 dark:text-purple-300',
            borderColor: 'border-purple-200 dark:border-purple-800/60',
            barColor: 'bg-gradient-to-r from-purple-500 to-indigo-500'
        },
        kasir: {
            title: 'Staf Kasir',
            desc: 'Hak akses POS kasir & transaksi nota',
            badgeText: 'Kasir',
            icon: 'point_of_sale',
            gradient: 'from-blue-600 to-cyan-600',
            bgLight: 'bg-blue-50 dark:bg-blue-950/70',
            textColor: 'text-blue-700 dark:text-blue-300',
            borderColor: 'border-blue-200 dark:border-blue-800/60',
            barColor: 'bg-gradient-to-r from-blue-500 to-cyan-500'
        },
        gudang: {
            title: 'Staf Gudang',
            desc: 'Hak akses mutasi stok & katalog produk',
            badgeText: 'Gudang',
            icon: 'inventory_2',
            gradient: 'from-amber-500 to-orange-600',
            bgLight: 'bg-amber-50 dark:bg-amber-950/70',
            textColor: 'text-amber-700 dark:text-amber-300',
            borderColor: 'border-amber-200 dark:border-amber-800/60',
            barColor: 'bg-gradient-to-r from-amber-500 to-orange-500'
        },
        pembeli: {
            title: 'Pelanggan Grosir',
            desc: 'Akun pelanggan & transaksi grosir/tempo',
            badgeText: 'Pembeli',
            icon: 'storefront',
            gradient: 'from-emerald-500 to-teal-600',
            bgLight: 'bg-emerald-50 dark:bg-emerald-950/70',
            textColor: 'text-emerald-700 dark:text-emerald-300',
            borderColor: 'border-emerald-200 dark:border-emerald-800/60',
            barColor: 'bg-gradient-to-r from-emerald-500 to-teal-500'
        }
    }[role] || {
        title: 'Pengguna Sistem',
        desc: 'Akun pengguna terdaftar',
        badgeText: role.toUpperCase(),
        icon: 'person',
        gradient: 'from-slate-600 to-slate-800',
        bgLight: 'bg-slate-50 dark:bg-slate-800',
        textColor: 'text-slate-700 dark:text-slate-300',
        borderColor: 'border-slate-200 dark:border-slate-700',
        barColor: 'bg-slate-500'
    };

    const formattedCredit = Number(user.credit_limit || 0) > 0 
        ? 'Rp ' + Number(user.credit_limit).toLocaleString('id-ID') 
        : null;

    const toast = document.createElement('div');
    toast.className = 'user-toast-card pointer-events-auto relative overflow-hidden rounded-2xl border border-slate-200/90 dark:border-slate-800/90 bg-surface-white/95 dark:bg-slate-900/95 backdrop-blur-xl shadow-2xl p-4 transition-all duration-300 transform translate-y-3 opacity-0 select-none flex flex-col gap-3 user-toast-enter toast-pulse-glow';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');

    toast.innerHTML = `
        <!-- Top Status Row -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br ${roleMeta.gradient} text-white flex items-center justify-center shadow-md">
                        <span class="material-symbols-outlined text-[22px]">${roleMeta.icon}</span>
                    </div>
                    <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900 absolute -bottom-1 -right-1 flex items-center justify-center text-[8px] text-white font-bold" title="Status Aktif">✓</span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <h4 class="font-bold text-xs text-text-main dark:text-white leading-tight">Pengguna Baru Berhasil Ditambahkan</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide border ${roleMeta.bgLight} ${roleMeta.textColor} ${roleMeta.borderColor}">
                            ${roleMeta.badgeText}
                        </span>
                    </div>
                    <div class="text-[11px] text-text-muted mt-0.5">${roleMeta.desc}</div>
                </div>
            </div>
            <button type="button" class="close-btn text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 -mr-1 -mt-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" aria-label="Tutup Notifikasi">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <!-- Identity Snippet -->
        <div class="bg-background-canvas dark:bg-slate-800/60 rounded-xl p-2.5 border border-border-subtle dark:border-slate-700/50 flex flex-col gap-1.5 text-xs">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5 truncate">
                    <span class="material-symbols-outlined text-[15px] text-slate-400">badge</span>
                    <span class="font-bold text-text-main dark:text-white truncate">${user.name}</span>
                </div>
                <span class="font-mono text-[11px] text-slate-600 dark:text-slate-300 font-bold bg-surface-white dark:bg-slate-800 px-2 py-0.5 rounded-md border border-border-subtle dark:border-slate-700 shrink-0">@${user.username}</span>
            </div>
            ${user.phone || formattedCredit ? `
                <div class="flex items-center justify-between text-[11px] text-text-muted pt-1 border-t border-border-subtle dark:border-slate-700/40">
                    ${user.phone ? `<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">call</span>${user.phone}</span>` : '<span></span>'}
                    ${formattedCredit ? `<span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">Plafon: ${formattedCredit}</span>` : ''}
                </div>
            ` : ''}
        </div>

        <!-- Interactive Actions -->
        <div class="flex items-center gap-2 pt-0.5">
            <button type="button" class="copy-user-btn flex-1 py-1.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-white text-white dark:text-slate-900 font-semibold text-xs transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                <span class="material-symbols-outlined text-[15px]">content_copy</span>
                <span>Salin Kredensial</span>
            </button>
            ${role === 'pembeli' && window.location.pathname.includes('/pos') ? `
                <button type="button" class="use-pos-btn py-1.5 px-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 font-semibold text-xs transition-colors flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-[15px]">point_of_sale</span>
                    <span>Pilih di Kasir</span>
                </button>
            ` : ''}
        </div>

        <!-- Bottom Animated Progress Bar -->
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <div class="progress-bar h-full ${roleMeta.barColor} w-full transition-all duration-[4800ms] ease-linear"></div>
        </div>
    `;

    container.appendChild(toast);

    // Entrance Animation
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-3', 'opacity-0');
        const progressBar = toast.querySelector('.progress-bar');
        if (progressBar) {
            requestAnimationFrame(() => {
                progressBar.style.width = '0%';
            });
        }
    });

    let dismissTimer = null;
    let remainingTime = 4800;
    let startTime = Date.now();

    const dismissToast = () => {
        if (dismissTimer) clearTimeout(dismissTimer);
        toast.classList.add('opacity-0', 'translate-y-3', 'scale-95');
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
            if (typeof options.onDismiss === 'function') {
                options.onDismiss();
            }
        }, 280);
    };

    dismissTimer = setTimeout(dismissToast, remainingTime);

    // Pause timer on hover
    toast.addEventListener('mouseenter', () => {
        if (dismissTimer) {
            clearTimeout(dismissTimer);
            remainingTime -= (Date.now() - startTime);
            const progressBar = toast.querySelector('.progress-bar');
            if (progressBar) {
                const computedWidth = window.getComputedStyle(progressBar).width;
                progressBar.style.transition = 'none';
                progressBar.style.width = computedWidth;
            }
        }
    });

    toast.addEventListener('mouseleave', () => {
        startTime = Date.now();
        if (remainingTime > 0) {
            const progressBar = toast.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.transition = `width ${remainingTime}ms linear`;
                progressBar.style.width = '0%';
            }
            dismissTimer = setTimeout(dismissToast, remainingTime);
        } else {
            dismissToast();
        }
    });

    // Close Button Event
    const closeBtn = toast.querySelector('.close-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', dismissToast);
    }

    // Copy Action Event
    const copyBtn = toast.querySelector('.copy-user-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            const copyText = `Nama: ${user.name}\nUsername: ${user.username}\nRole: ${role.toUpperCase()}${user.phone ? '\nNo Telp: ' + user.phone : ''}`;
            const copyDone = () => {
                copyBtn.innerHTML = `
                    <span class="material-symbols-outlined text-[15px] text-emerald-400">check</span>
                    <span class="text-emerald-400 dark:text-emerald-600">Tersalin!</span>
                `;
                setTimeout(() => {
                    copyBtn.innerHTML = `
                        <span class="material-symbols-outlined text-[15px]">content_copy</span>
                        <span>Salin Kredensial</span>
                    `;
                }, 2000);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(copyText).then(copyDone).catch(() => {
                    const temp = document.createElement('textarea');
                    temp.value = copyText;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                    copyDone();
                });
            } else {
                const temp = document.createElement('textarea');
                temp.value = copyText;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                copyDone();
            }
        });
    }

    // Use in POS action if available
    const usePosBtn = toast.querySelector('.use-pos-btn');
    if (usePosBtn) {
        usePosBtn.addEventListener('click', () => {
            const sel = document.getElementById('customerSelect');
            if (sel) {
                sel.value = user.id;
                sel.dispatchEvent(new Event('change'));
            }
            dismissToast();
        });
    }

    return toast;
};

// Impeccable User Status Toggle (Deactivation / Activation) Toast
window.showUserStatusToast = function(user, isActive) {
    if (!user) return;
    if (isActive) {
        playSuccessTone();
    } else {
        playWarningTone();
    }

    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col gap-2.5 pointer-events-none max-w-[390px] w-full px-4';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const isDeactivated = !isActive;

    const icon = isDeactivated ? 'power_settings_new' : 'check_circle';
    const title = isDeactivated ? 'Akun Telah Dinonaktifkan' : 'Akun Berhasil Diaktifkan';
    const desc = isDeactivated 
        ? 'Akses operasional toko dan sesi login dibekukan sementara.'
        : 'Hak akses login dan operasional sistem telah dipulihkan.';
    const badgeColor = isDeactivated 
        ? 'bg-red-50 text-red-700 dark:bg-red-950/70 dark:text-red-300 border-red-200 dark:border-red-800/60'
        : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60';
    const iconGradient = isDeactivated 
        ? 'from-red-600 to-rose-700 shadow-md shadow-red-500/20'
        : 'from-emerald-600 to-teal-700 shadow-md shadow-emerald-500/20';
    const barColor = isDeactivated 
        ? 'bg-gradient-to-r from-red-500 to-rose-600'
        : 'bg-gradient-to-r from-emerald-500 to-teal-600';

    toast.className = 'pointer-events-auto relative overflow-hidden rounded-2xl border border-slate-200/90 dark:border-slate-800/90 bg-surface-white/95 dark:bg-slate-900/95 backdrop-blur-xl shadow-2xl p-4 transition-all duration-300 transform translate-y-3 opacity-0 select-none flex flex-col gap-2.5 user-toast-enter';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');

    toast.innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br ${iconGradient} text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">${icon}</span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <h4 class="font-bold text-xs text-text-main dark:text-white leading-tight">${title}</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide border ${badgeColor}">
                            ${isDeactivated ? 'Nonaktif' : 'Aktif'}
                        </span>
                    </div>
                    <div class="text-[11px] text-text-muted mt-0.5 truncate">${user.name} <span class="font-mono text-[10px]">(@${user.username})</span></div>
                </div>
            </div>
            <button type="button" class="close-btn text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 -mr-1 -mt-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" aria-label="Tutup Notifikasi">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed bg-background-canvas dark:bg-slate-800/60 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700/50 flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px] ${isDeactivated ? 'text-red-500' : 'text-emerald-500'} shrink-0">${isDeactivated ? 'info' : 'verified_user'}</span>
            <span class="leading-snug">${desc}</span>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <div class="progress-bar h-full ${barColor} w-full transition-all duration-[4000ms] ease-linear"></div>
        </div>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-3', 'opacity-0');
        const bar = toast.querySelector('.progress-bar');
        if (bar) {
            requestAnimationFrame(() => {
                bar.style.width = '0%';
            });
        }
    });

    let dismissTimer = null;
    let remainingTime = 4000;
    let startTime = Date.now();

    const dismissToast = () => {
        if (dismissTimer) clearTimeout(dismissTimer);
        toast.classList.add('opacity-0', 'translate-y-3', 'scale-95');
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 280);
    };

    dismissTimer = setTimeout(dismissToast, remainingTime);

    toast.addEventListener('mouseenter', () => {
        if (dismissTimer) {
            clearTimeout(dismissTimer);
            remainingTime -= (Date.now() - startTime);
            const bar = toast.querySelector('.progress-bar');
            if (bar) {
                const computedWidth = window.getComputedStyle(bar).width;
                bar.style.transition = 'none';
                bar.style.width = computedWidth;
            }
        }
    });

    toast.addEventListener('mouseleave', () => {
        startTime = Date.now();
        if (remainingTime > 0) {
            const bar = toast.querySelector('.progress-bar');
            if (bar) {
                bar.style.transition = `width ${remainingTime}ms linear`;
                bar.style.width = '0%';
            }
            dismissTimer = setTimeout(dismissToast, remainingTime);
        } else {
            dismissToast();
        }
    });

    const closeBtn = toast.querySelector('.close-btn');
    if (closeBtn) closeBtn.addEventListener('click', dismissToast);

    return toast;
};

// Global Impeccable & Antislop Confirm Dialog Promise Handler
window._confirmResolve = null;

window._resolveConfirmDialog = function(val) {
    const modal = document.getElementById('globalConfirmModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('active');
    }
    if (typeof window._confirmResolve === 'function') {
        const resolve = window._confirmResolve;
        window._confirmResolve = null;
        resolve(Boolean(val));
    }
};

window.showConfirmDialog = function(options = {}) {
    return new Promise((resolve) => {
        window._confirmResolve = resolve;

        const modal = document.getElementById('globalConfirmModal');
        if (!modal) {
            // Safe fallback if modal DOM missing
            resolve(true);
            return;
        }

        const title = options.title || 'Konfirmasi Tindakan';
        const message = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        const confirmText = options.confirmText || 'Lanjutkan';
        const cancelText = options.cancelText || 'Batal';
        const variant = options.variant || 'primary'; // 'danger' | 'warning' | 'primary' | 'success'
        const iconName = options.icon || (variant === 'danger' ? 'warning' : (variant === 'warning' ? 'help' : 'info'));
        const details = options.details || '';

        const titleEl = document.getElementById('globalConfirmTitle');
        const messageEl = document.getElementById('globalConfirmMessage');
        const iconEl = document.getElementById('globalConfirmIcon');
        const iconContainer = document.getElementById('globalConfirmIconContainer');
        const actionBtn = document.getElementById('globalConfirmActionBtn');
        const actionIcon = document.getElementById('globalConfirmActionIcon');
        const actionText = document.getElementById('globalConfirmActionText');
        const cancelBtn = document.getElementById('globalConfirmCancelBtn');
        const detailsBox = document.getElementById('globalConfirmDetailsBox');
        const detailsText = document.getElementById('globalConfirmDetailsText');

        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.innerHTML = message;
        if (iconEl) iconEl.textContent = iconName;
        if (actionText) actionText.textContent = confirmText;
        if (cancelBtn) cancelBtn.textContent = cancelText;

        if (details && detailsBox && detailsText) {
            detailsBox.classList.remove('hidden');
            detailsText.innerHTML = details;
        } else if (detailsBox) {
            detailsBox.classList.add('hidden');
        }

        // Color & Badge Variant Styling
        if (variant === 'danger') {
            if (iconContainer) iconContainer.className = 'w-11 h-11 rounded-2xl bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400 flex items-center justify-center shrink-0 border border-red-200 dark:border-red-900/50';
            if (actionBtn) actionBtn.className = 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-xs bg-red-600 hover:bg-red-700 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer';
            if (actionIcon) actionIcon.textContent = options.actionIcon || 'delete';
        } else if (variant === 'warning') {
            if (iconContainer) iconContainer.className = 'w-11 h-11 rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-200 dark:border-amber-900/50';
            if (actionBtn) actionBtn.className = 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-xs bg-amber-600 hover:bg-amber-700 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer';
            if (actionIcon) actionIcon.textContent = options.actionIcon || 'check';
        } else if (variant === 'success') {
            if (iconContainer) iconContainer.className = 'w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-900/50';
            if (actionBtn) actionBtn.className = 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-xs bg-emerald-600 hover:bg-emerald-700 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer';
            if (actionIcon) actionIcon.textContent = options.actionIcon || 'check';
        } else {
            if (iconContainer) iconContainer.className = 'w-11 h-11 rounded-2xl bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-200 dark:border-blue-900/50';
            if (actionBtn) actionBtn.className = 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-xs bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer';
            if (actionIcon) actionIcon.textContent = options.actionIcon || 'check';
        }

        modal.classList.remove('hidden');
        modal.classList.add('active');

        // Focus accessibility
        setTimeout(() => {
            if (variant === 'danger' && cancelBtn) {
                cancelBtn.focus();
            } else if (actionBtn) {
                actionBtn.focus();
            }
        }, 50);
    });
};

// Global POS Helper Functions for 100% resilient button clickability
window.selectPosMethod = function(method) {
    if (window.posApp) {
        window.posApp.setPaymentMethod(method);
    }
};

window.selectModalMethod = function(method) {
    if (window.posApp) {
        window.posApp.handlePaymentMethodChange(method);
    }
};

// POS Screen Engine
class PosEngine {
    constructor(products, customers, qrisConfig) {
        this.products = products || [];
        this.customers = customers || [];
        this.qrisConfig = qrisConfig || { staticQris: '', merchantName: '' };
        this.cart = [];
        this.activeCategory = 'all';
        this.searchQuery = '';
        this.selectedCustomerId = '';
        this.activePaymentMethod = 'tunai';

        this.initDoms();
        this.bindEvents();
        this.initMobileCartObserver();
        if (!this.catalogGrid || !this.catalogGrid.querySelector('article')) {
            this.renderCatalog();
        }
        if (!this.cartItemsList || !this.cartItemsList.children.length) {
            this.renderCart();
        }
    }

    initMobileCartObserver() {
        const panel = document.getElementById('posCartPanel');
        const floatWrap = document.getElementById('mobileCartFloatWrap');
        if (!panel || !floatWrap) return;

        const updateVisibility = () => {
            const r = panel.getBoundingClientRect();
            if (r.bottom < 80) {
                floatWrap.classList.remove('translate-y-full', 'translate-y-2', 'opacity-0', 'pointer-events-none');
                floatWrap.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
            } else {
                floatWrap.classList.add('translate-y-full', 'opacity-0', 'pointer-events-none');
                floatWrap.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
            }
        };

        window.addEventListener('scroll', updateVisibility, { passive: true });
        updateVisibility();
    }

    scrollToCart() {
        const panel = document.getElementById('posCartPanel');
        if (panel) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    initDoms() {
        this.catalogGrid = document.getElementById('catalogGrid');
        this.cartItemsList = document.getElementById('cartItemsList');
        this.cartCountBadge = document.getElementById('cartCountBadge');
        this.cartSubtotalDisplay = document.getElementById('cartSubtotalDisplay');
        this.cartDiscountDisplay = document.getElementById('cartDiscountDisplay');
        this.cartGrandTotalDisplay = document.getElementById('cartGrandTotalDisplay');
        this.checkoutBtn = document.getElementById('checkoutBtn');
        this.customerSelect = document.getElementById('customerSelect');
        this.customerDebtWarning = document.getElementById('customerDebtWarning');
        this.searchInput = document.getElementById('searchInput');
    }

    bindEvents() {
        // Search Input & Barcode Scanning
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.searchQuery = e.target.value.toLowerCase().trim();
                this.renderCatalog();
            });

            this.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.handleBarcodeOrFirstMatch();
                }
            });
        }

        // Category Pills
        const pills = document.querySelectorAll('.category-pill');
        pills.forEach(pill => {
            pill.addEventListener('click', () => {
                pills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                this.activeCategory = pill.dataset.cat;
                this.renderCatalog();
            });
        });

        // Customer Selection
        if (this.customerSelect) {
            this.customerSelect.addEventListener('change', (e) => {
                this.selectedCustomerId = e.target.value;
                this.updateCustomerDebtInfo();
            });
        }

        // Direct Payment Method Tabs on Cart Panel
        const btnTunai = document.getElementById('panelTabTunai');
        const btnQris = document.getElementById('panelTabQris');
        const btnHutang = document.getElementById('panelTabHutang');
        if (btnTunai) btnTunai.addEventListener('click', () => this.setPaymentMethod('tunai'));
        if (btnQris) btnQris.addEventListener('click', () => this.setPaymentMethod('qris'));
        if (btnHutang) btnHutang.addEventListener('click', () => this.setPaymentMethod('hutang'));

        // Modal Payment Method Tabs
        const mTabTunai = document.getElementById('modalTabTunai');
        const mTabQris = document.getElementById('modalTabQris');
        const mTabHutang = document.getElementById('modalTabHutang');
        if (mTabTunai) mTabTunai.addEventListener('click', () => this.handlePaymentMethodChange('tunai'));
        if (mTabQris) mTabQris.addEventListener('click', () => this.handlePaymentMethodChange('qris'));
        if (mTabHutang) mTabHutang.addEventListener('click', () => this.handlePaymentMethodChange('hutang'));

        // Main Checkout Button
        const mainCheckoutBtn = document.getElementById('checkoutBtn');
        if (mainCheckoutBtn) {
            mainCheckoutBtn.addEventListener('click', () => this.handleMainCheckoutClick());
        }
    }

    handleBarcodeOrFirstMatch() {
        const query = (this.searchQuery || '').trim();
        if (!query) return;

        // Exact match on barcode or sku first (case-insensitive & trimmed)
        const exact = this.products.find(p => 
            (p.barcode && p.barcode.trim().toLowerCase() === query.toLowerCase()) || 
            (p.sku && p.sku.trim().toLowerCase() === query.toLowerCase())
        );
        if (exact) {
            this.addToCart(exact.id);
            if (this.searchInput) this.searchInput.value = '';
            this.searchQuery = '';
            this.renderCatalog();
            return;
        }

        // If filtered catalog has exactly 1 result, add it
        const filtered = this.getFilteredProducts();
        if (filtered.length === 1) {
            this.addToCart(filtered[0].id);
            if (this.searchInput) this.searchInput.value = '';
            this.searchQuery = '';
            this.renderCatalog();
            return;
        }

        if (filtered.length === 0) {
            if (typeof playWarningTone === 'function') playWarningTone();
            if (typeof showToast === 'function') {
                showToast(`Barcode / SKU "${query}" tidak ditemukan di katalog.`, 'warning');
            }
        }
    }

    getFilteredProducts() {
        return this.products.filter(p => {
            const matchesCat = (this.activeCategory === 'all' || p.category_slug === this.activeCategory);
            const matchesQuery = (!this.searchQuery || 
                p.name.toLowerCase().includes(this.searchQuery) || 
                p.sku.toLowerCase().includes(this.searchQuery) || 
                (p.barcode && p.barcode.includes(this.searchQuery))
            );
            return matchesCat && matchesQuery;
        });
    }

    filterCategory(cat, element) {
        document.querySelectorAll('.category-pill').forEach(p => {
            p.classList.remove('active', 'bg-primary', 'text-white', 'shadow-xs');
            p.classList.add('bg-background-canvas', 'text-secondary', 'border', 'border-border-subtle');
        });
        if (element) {
            element.classList.remove('bg-background-canvas', 'text-secondary', 'border', 'border-border-subtle');
            element.classList.add('active', 'bg-primary', 'text-white', 'shadow-xs');
        }
        this.activeCategory = cat;
        this.renderCatalog();
    }

    renderCatalog() {
        if (!this.catalogGrid) return;
        const filtered = this.getFilteredProducts();

        if (filtered.length === 0) {
            this.catalogGrid.innerHTML = `
                <div class="col-span-full bg-surface-white dark:bg-slate-900 rounded-2xl p-8 border border-border-subtle dark:border-slate-800 text-center transition-colors">
                    <span class="material-symbols-outlined text-[40px] text-text-muted mb-2">search_off</span>
                    <div class="font-bold text-sm text-text-main dark:text-white">Tidak ada produk yang cocok</div>
                    <div class="text-xs text-text-muted mt-1">Periksa kata kunci pencarian atau ganti kategori.</div>
                </div>
            `;
            return;
        }

        this.catalogGrid.innerHTML = filtered.map(p => {
            const isOutOfStock = p.stock <= 0;
            const wholesaleDiscount = p.wholesale_discount_type === 'percentage'
                ? Number(p.retail_price || 0) * Number(p.wholesale_discount_value || 0) / 100
                : p.wholesale_discount_type === 'special_price'
                    ? Math.max(0, Number(p.retail_price || 0) - Number(p.wholesale_discount_value || 0))
                    : Number(p.wholesale_discount_value || 0);
            const wholesalePrice = Math.max(0, Number(p.retail_price || 0) - wholesaleDiscount);
            let stockBadge = 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
            if (p.stock <= 0) stockBadge = 'bg-red-50 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800/60';
            else if (p.stock <= p.min_stock) stockBadge = 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60';

            let catIcon = 'inventory_2';
            let catBg = 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300';
            const cat = (p.category_slug || '').toLowerCase();
            if (cat.includes('sembako') || cat.includes('beras')) {
                catIcon = 'rice_bowl';
                catBg = 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400';
            } else if (cat.includes('minyak') || cat.includes('bumbu')) {
                catIcon = 'oil_barrel';
                catBg = 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400';
            } else if (cat.includes('minuman') || cat.includes('kopi')) {
                catIcon = 'local_cafe';
                catBg = 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400';
            } else if (cat.includes('mie') || cat.includes('makanan') || cat.includes('snack')) {
                catIcon = 'fastfood';
                catBg = 'bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-400';
            } else if (cat.includes('kebersihan') || cat.includes('rumah')) {
                catIcon = 'sanitizer';
                catBg = 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-400';
            }

            return `
                <article class="group relative bg-surface-white dark:bg-slate-900 rounded-2xl p-3.5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between border border-border-subtle dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 ${isOutOfStock ? 'opacity-60 pointer-events-none' : ''}" data-sku="${escapeHtml(p.sku)}" data-price="${Number(p.retail_price || 0)}">
                    <button type="button" onclick="posApp.addToCart(${p.id})" class="absolute inset-0 w-full h-full rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 cursor-pointer z-10" aria-label="Tambah ${escapeHtml(p.name)} harga ${formatRp(p.retail_price)} ke keranjang" ${isOutOfStock ? 'disabled' : ''}></button>
                    <div>
                        <!-- Card Visual Top Bar -->
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-blue-700 dark:text-blue-300 border border-slate-200/80 dark:border-slate-700/60 tracking-wide select-none whitespace-nowrap shrink-0">
                                ${escapeHtml(p.unit)}
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg font-mono ${stockBadge} select-none whitespace-nowrap shrink-0">
                                ${p.stock <= 0 ? 'Habis' : 'Stok: ' + p.stock}
                            </span>
                        </div>

                        <!-- Product Thumbnail Image Container -->
                        <div class="w-full h-28 rounded-xl bg-white p-2 border border-slate-100 dark:border-slate-800 flex items-center justify-center mb-2.5 overflow-hidden group-hover:scale-[1.02] transition-transform">
                            <img src="${p.image_url || 'assets/produk/' + (p.sku || '').toLowerCase() + '.webp'}" alt="${escapeHtml(p.name)}" width="180" height="180" class="w-full h-full object-contain aspect-square" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            <div class="hidden w-full h-full rounded-lg ${catBg} flex items-center justify-center" aria-hidden="true">
                                <span class="material-symbols-outlined text-[32px] opacity-85">${catIcon}</span>
                            </div>
                        </div>

                        <span class="text-[10px] font-mono text-slate-700 dark:text-slate-300 font-bold tracking-wider uppercase block select-none truncate">SKU: ${escapeHtml(p.sku)}</span>
                        <h3 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-slate-100 group-hover:text-primary dark:group-hover:text-blue-400 transition-colors line-clamp-2 min-h-[2.5rem] mt-0.5 leading-snug">${escapeHtml(p.name)}</h3>
                        
                        ${p.wholesale_min_qty ? `
                            <div class="bg-wholesale-badge-bg dark:bg-red-950/40 border border-red-200/80 dark:border-red-900/50 px-2.5 py-1 rounded-xl mt-2 flex items-center justify-between gap-2 text-[11px] text-wholesale-badge-text dark:text-red-300 font-bold">
                                <span class="flex items-center gap-1 min-w-0 truncate">
                                    <span class="material-symbols-outlined text-[13px] shrink-0" aria-hidden="true">sell</span>
                                    <span class="truncate">&ge; ${p.wholesale_min_qty} ${escapeHtml(p.unit)}</span>
                                </span>
                                <span class="font-mono whitespace-nowrap shrink-0">@ ${formatRp(p.wholesale_final_price ?? wholesalePrice)}</span>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Retail Price Section -->
                    <div class="mt-auto pt-3 border-t border-border-subtle dark:border-slate-800 flex items-center justify-between">
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-700 dark:text-slate-300 select-none">Harga Ecer</span>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 font-mono select-none">Rp</span>
                                <span class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white font-mono tracking-tight leading-none">${Number(p.retail_price || 0).toLocaleString('id-ID')}</span>
                                <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 font-sans ml-0.5">/${escapeHtml(p.unit)}</span>
                            </div>
                        </div>
                    </div>
                </article>
            `;
        }).join('');
    }

    addToCart(productId) {
        const prod = this.products.find(p => p.id === productId);
        if (!prod) return;

        if (prod.stock <= 0) {
            if (typeof playWarningTone === 'function') playWarningTone();
            showToast(`Stok produk ${prod.name} sedang kosong.`, 'error');
            return;
        }

        const existing = this.cart.find(c => c.product_id === productId);
        if (existing) {
            if (existing.qty + 1 > prod.stock) {
                if (typeof playWarningTone === 'function') playWarningTone();
                showToast(`Tidak dapat menambah barang melebihi stok yang tersedia (${prod.stock} ${prod.unit}).`, 'warning');
                return;
            }
            existing.qty += 1;
        } else {
            this.cart.push({
                product_id: prod.id,
                name: prod.name,
                sku: prod.sku,
                unit: prod.unit,
                image_url: prod.image_url || `assets/produk/${(prod.sku || '').toLowerCase()}.webp`,
                unit_price: Number(prod.retail_price),
                wholesale_min_qty: Number(prod.wholesale_min_qty || 0),
                wholesale_discount_value: Number(prod.wholesale_discount_value || 0),
                wholesale_discount_type: prod.wholesale_discount_type || 'fixed_amount',
                stock: prod.stock,
                qty: 1
            });
        }

        if (typeof playSuccessTone === 'function') playSuccessTone();
        this.renderCart();
    }

    updateQty(productId, newQty) {
        const item = this.cart.find(c => c.product_id === productId);
        if (!item) return;

        newQty = parseInt(newQty, 10);
        if (isNaN(newQty) || newQty <= 0) {
            this.removeFromCart(productId);
            return;
        }

        if (newQty > item.stock) {
            showToast(`Maksimal stok tersedia adalah ${item.stock} ${item.unit}.`, 'warning');
            item.qty = item.stock;
        } else {
            item.qty = newQty;
        }

        this.renderCart();
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(c => c.product_id !== productId);
        this.renderCart();
    }

    async clearCart() {
        if (!this.cart || this.cart.length === 0) return;
        const confirmed = await window.showConfirmDialog({
            title: 'Kosongkan Keranjang Kasir?',
            message: 'Semua item yang telah dipilih akan dihapus dari transaksi saat ini.',
            confirmText: 'Ya, Kosongkan',
            cancelText: 'Batal',
            variant: 'danger',
            icon: 'delete_sweep',
            actionIcon: 'delete'
        });
        if (confirmed) {
            this.cart = [];
            this.renderCart();
            if (typeof showToast === 'function') {
                showToast('Keranjang transaksi berhasil dikosongkan.', 'info');
            }
        }
    }

    calculateCart() {
        let subtotal = 0;
        let totalDiscount = 0;
        let totalQty = 0;

        const evaluatedItems = this.cart.map(item => {
            const rawItemSubtotal = item.unit_price * item.qty;
            let discountPerItem = 0;

            // Tiered wholesale discount evaluation
            if (item.wholesale_min_qty > 0 && item.qty >= item.wholesale_min_qty) {
                if (item.wholesale_discount_type === 'fixed_amount') {
                    discountPerItem = item.wholesale_discount_value;
                } else if (item.wholesale_discount_type === 'percentage') {
                    discountPerItem = (item.unit_price * item.wholesale_discount_value) / 100;
                } else if (item.wholesale_discount_type === 'special_price') {
                    discountPerItem = Math.max(0, item.unit_price - item.wholesale_discount_value);
                }
            }

            const itemDiscountTotal = discountPerItem * item.qty;
            const finalItemSubtotal = rawItemSubtotal - itemDiscountTotal;

            subtotal += rawItemSubtotal;
            totalDiscount += itemDiscountTotal;
            totalQty += item.qty;

            return {
                ...item,
                discount_per_item: discountPerItem,
                item_discount_total: itemDiscountTotal,
                final_subtotal: finalItemSubtotal
            };
        });

        const grandTotal = subtotal - totalDiscount;

        return {
            items: evaluatedItems,
            subtotal,
            totalDiscount,
            grandTotal,
            totalQty
        };
    }

    renderCart() {
        if (!this.cartItemsList) return;
        const calc = this.calculateCart();

        if (this.cartCountBadge) {
            this.cartCountBadge.textContent = calc.totalQty;
        }
        const catCartBadge = document.getElementById('catalogCartCount');
        if (catCartBadge) catCartBadge.textContent = calc.totalQty;
        const floatBadge = document.getElementById('mobileCartFloatBadge');
        if (floatBadge) floatBadge.textContent = calc.totalQty;
        const floatTotal = document.getElementById('mobileCartFloatTotal');
        if (floatTotal) floatTotal.textContent = formatRp(calc.grandTotal);

        if (calc.items.length === 0) {
            this.cartItemsList.innerHTML = `
                <div class="p-6 text-center bg-background-canvas dark:bg-slate-800/50 rounded-2xl border border-dashed border-border-subtle dark:border-slate-800">
                    <span class="material-symbols-outlined text-[32px] text-text-muted dark:text-slate-500 mb-1">shopping_basket</span>
                    <div class="font-bold text-xs text-text-main dark:text-white">Keranjang Belanja Kosong</div>
                    <div class="text-[11px] text-text-muted dark:text-slate-400 mt-0.5">Pilih produk di katalog atau scan barcode.</div>
                </div>
            `;
            if (this.cartSubtotalDisplay) this.cartSubtotalDisplay.textContent = 'Rp 0';
            if (this.cartDiscountDisplay) this.cartDiscountDisplay.textContent = '-Rp 0';
            if (this.cartGrandTotalDisplay) this.cartGrandTotalDisplay.textContent = 'Rp 0';
            if (this.checkoutBtn) this.checkoutBtn.disabled = false;
            return;
        }

        this.cartItemsList.innerHTML = calc.items.map(it => `
            <div class="p-3 rounded-2xl bg-background-canvas dark:bg-slate-800/60 border border-border-subtle dark:border-slate-700/60 flex flex-col gap-2 hover:border-slate-300 dark:hover:border-slate-600 transition-colors">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-white p-1 shrink-0 border border-border-subtle dark:border-slate-700 overflow-hidden flex items-center justify-center">
                        <img src="${it.image_url || 'assets/produk/' + (it.sku || '').toLowerCase() + '.webp'}" alt="${escapeHtml(it.name)}" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                        <span class="material-symbols-outlined text-[18px] text-slate-400 hidden">inventory_2</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-xs text-text-main dark:text-white truncate">${escapeHtml(it.name)}</div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[11px] font-mono text-text-muted dark:text-slate-400">@ ${formatRp(it.unit_price - it.discount_per_item)}</span>
                            ${it.discount_per_item > 0 ? `<span class="text-[10px] line-through text-text-muted dark:text-slate-500 font-mono">${formatRp(it.unit_price)}</span>` : ''}
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-bold text-xs text-text-main dark:text-white font-mono">${formatRp(it.final_subtotal)}</div>
                        <button type="button" class="text-text-muted dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 text-xs mt-0.5 inline-flex cursor-pointer transition-colors" onclick="posApp.removeFromCart(${it.product_id})" title="Hapus dari keranjang">
                            <span class="material-symbols-outlined text-[16px]">delete</span>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1.5 border-t border-border-subtle/70 dark:border-slate-700/50">
                    ${it.discount_per_item > 0 ? `
                        <span class="text-[10px] bg-wholesale-badge-bg dark:bg-red-950/50 text-wholesale-badge-text dark:text-red-300 border border-red-200 dark:border-red-900/50 font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">verified</span>
                            Hemat Grosir (-${formatRp(it.item_discount_total)})
                        </span>
                    ` : (it.wholesale_min_qty > 0 ? `
                        <span class="text-[10px] text-text-muted dark:text-slate-400 font-medium">
                            Tambah ${it.wholesale_min_qty - it.qty} ${it.unit} lagi utk grosir
                        </span>
                    ` : `<span></span>`)}
                    <div class="flex items-center gap-1 bg-surface-white dark:bg-slate-800 px-1.5 py-0.5 rounded-xl border border-border-subtle dark:border-slate-700 shadow-xs">
                        <button class="w-6 h-6 rounded-lg text-secondary dark:text-blue-400 hover:bg-background-canvas dark:hover:bg-slate-700 flex items-center justify-center font-bold text-sm cursor-pointer transition-colors" type="button" onclick="posApp.updateQty(${it.product_id}, ${it.qty - 1})">-</button>
                        <span class="font-bold text-xs text-text-main dark:text-white px-1.5 min-w-[22px] text-center font-mono">${it.qty}</span>
                        <button class="w-6 h-6 rounded-lg bg-primary text-white hover:bg-primary-hover flex items-center justify-center font-bold text-sm shadow-xs cursor-pointer transition-colors" type="button" onclick="posApp.updateQty(${it.product_id}, ${it.qty + 1})">+</button>
                    </div>
                </div>
            </div>
        `).join('');

        if (this.cartSubtotalDisplay) this.cartSubtotalDisplay.textContent = formatRp(calc.subtotal);
        if (this.cartDiscountDisplay) this.cartDiscountDisplay.textContent = '-' + formatRp(calc.totalDiscount);
        if (this.cartGrandTotalDisplay) this.cartGrandTotalDisplay.textContent = formatRp(calc.grandTotal);
        if (this.checkoutBtn) this.checkoutBtn.disabled = false;

        // Auto update QRIS & Tunai calculations in real time when cart updates
        if (this.activePaymentMethod === 'qris') {
            const panelQris = document.getElementById('panelQrisSection');
            if (panelQris) panelQris.style.display = 'flex';
            this.renderDynamicQris(calc.grandTotal);
        } else if (this.activePaymentMethod === 'tunai') {
            this.calculatePanelChange();
        }
    }

    setPaymentMethod(method) {
        this.activePaymentMethod = method;
        const calc = this.calculateCart();

        // Update Panel Tabs Styling
        const tabs = {
            tunai: document.getElementById('panelTabTunai'),
            qris: document.getElementById('panelTabQris'),
            hutang: document.getElementById('panelTabHutang')
        };
        const activeStyles = {
            tunai: "flex flex-col items-center justify-center p-2 rounded-xl border-2 border-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold text-xs shadow-xs ring-2 ring-emerald-500/20 transition-all cursor-pointer",
            qris: "flex flex-col items-center justify-center p-2 rounded-xl border-2 border-blue-500 bg-blue-50/90 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200 font-bold text-xs shadow-xs ring-2 ring-blue-500/20 transition-all cursor-pointer",
            hutang: "flex flex-col items-center justify-center p-2 rounded-xl border-2 border-amber-500 bg-amber-50/90 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 font-bold text-xs shadow-xs ring-2 ring-amber-500/20 transition-all cursor-pointer"
        };
        const inactiveStyles = {
            tunai: "flex flex-col items-center justify-center p-2 rounded-xl border border-border-subtle bg-surface-white dark:bg-slate-800/80 text-text-muted hover:text-text-main hover:border-emerald-300 dark:hover:border-emerald-700 font-semibold text-xs transition-all cursor-pointer",
            qris: "flex flex-col items-center justify-center p-2 rounded-xl border border-border-subtle bg-surface-white dark:bg-slate-800/80 text-text-muted hover:text-text-main hover:border-blue-300 dark:hover:border-blue-700 font-semibold text-xs transition-all cursor-pointer",
            hutang: "flex flex-col items-center justify-center p-2 rounded-xl border border-border-subtle bg-surface-white dark:bg-slate-800/80 text-text-muted hover:text-text-main hover:border-amber-300 dark:hover:border-amber-700 font-semibold text-xs transition-all cursor-pointer"
        };
        Object.keys(tabs).forEach(k => {
            const btn = tabs[k];
            if (!btn) return;
            btn.className = (k === method) ? activeStyles[k] : inactiveStyles[k];
        });

        // Update Label
        const label = document.getElementById('panelSelectedMethodLabel');
        if (label) {
            label.textContent = (method === 'tunai' ? 'Tunai / Cash' : (method === 'qris' ? 'QRIS Dinamis' : 'Tempo / Kasbon'));
            label.className = 'text-[11px] font-bold ' + (method === 'tunai' ? 'text-emerald-600 dark:text-emerald-400' : (method === 'qris' ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400'));
        }

        // Toggle Panel Section Visibility
        const tunaiSec = document.getElementById('panelTunaiSection');
        const qrisSec = document.getElementById('panelQrisSection');
        const hutangSec = document.getElementById('panelHutangSection');

        if (tunaiSec) tunaiSec.style.display = (method === 'tunai') ? 'flex' : 'none';
        if (qrisSec) qrisSec.style.display = (method === 'qris') ? 'flex' : 'none';
        if (hutangSec) hutangSec.style.display = (method === 'hutang') ? 'flex' : 'none';

        // Update Action Button
        const icon = document.getElementById('checkoutBtnIcon');
        const text = document.getElementById('checkoutBtnText');
        if (method === 'qris') {
            if (icon) icon.textContent = 'qr_code_2';
            if (text) text.textContent = 'Buka QRIS Layar Penuh (F4)';
            this.renderDynamicQris(calc.grandTotal);
        } else if (method === 'tunai') {
            if (icon) icon.textContent = 'payments';
            if (text) text.textContent = 'Bayar Tunai & Selesaikan (F4)';
            this.calculatePanelChange();
        } else if (method === 'hutang') {
            if (icon) icon.textContent = 'event_note';
            if (text) text.textContent = 'Catat Tempo & Selesaikan (F4)';
        }
    }

    addPanelCash(delta) {
        const cashInput = document.getElementById('panelCashInput');
        if (!cashInput) return;
        const cur = Number(cashInput.value || 0);
        cashInput.value = cur + Number(delta);
        this.calculatePanelChange();
    }

    syncPanelCashToInput() {
        const calc = this.calculateCart();
        const cashInput = document.getElementById('panelCashInput');
        if (cashInput) {
            cashInput.value = calc.grandTotal;
            this.calculatePanelChange();
        }
    }

    calculatePanelChange() {
        const calc = this.calculateCart();
        const grandTotal = calc.grandTotal;
        const cashInput = document.getElementById('panelCashInput');
        const display = document.getElementById('panelChangeDisplay');
        if (!cashInput || !display) return;

        const paid = Number(cashInput.value || 0);
        if (paid <= 0) {
            display.textContent = 'Rp 0';
            display.className = 'font-mono text-text-muted text-sm font-semibold';
        } else if (paid >= grandTotal) {
            display.textContent = formatRp(paid - grandTotal);
            display.className = 'font-mono text-emerald-600 dark:text-emerald-400 text-sm font-extrabold';
        } else {
            display.textContent = 'Kurang ' + formatRp(grandTotal - paid);
            display.className = 'font-mono text-red-600 dark:text-red-400 text-xs font-bold';
        }
    }

    handleMainCheckoutClick() {
        const calc = this.calculateCart();
        if (calc.items.length === 0) {
            showToast('Keranjang belanja masih kosong. Silakan pilih produk dari katalog kasir terlebih dahulu.', 'warning');
            return;
        }
        this.openPaymentModal(this.activePaymentMethod || 'tunai');
    }

    async submitQrisDirect() {
        const calc = this.calculateCart();
        if (calc.items.length === 0) {
            showToast('Keranjang belanja masih kosong. Silakan pilih produk dari katalog kasir terlebih dahulu.', 'warning');
            return;
        }
        this.activePaymentMethod = 'qris';
        await this.submitCheckout();
    }

    updateCustomerDebtInfo() {
        if (!this.customerDebtWarning) return;
        if (!this.selectedCustomerId) {
            this.customerDebtWarning.style.display = 'none';
            return;
        }

        const cust = this.customers.find(c => c.id == this.selectedCustomerId);
        if (cust) {
            const limit = Number(cust.credit_limit || 0);
            const currentDebt = Number(cust.current_debt || 0);
            const remainingPlafon = Math.max(0, limit - currentDebt);

            this.customerDebtWarning.style.display = 'block';
            this.customerDebtWarning.innerHTML = `
                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <span class="material-symbols-outlined text-[15px] text-blue-500 dark:text-blue-400 shrink-0">badge</span>
                        <span class="font-bold text-xs text-slate-800 dark:text-white truncate">${cust.name}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono shrink-0">${cust.phone || '-'}</span>
                </div>
                <div class="mt-2 flex flex-col gap-1">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 dark:text-slate-400">Hutang Berjalan:</span>
                        <strong class="text-amber-600 dark:text-amber-400 font-mono font-bold">${formatRp(currentDebt)}</strong>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 dark:text-slate-400">Plafon Kredit:</span>
                        <strong class="font-mono font-semibold text-slate-700 dark:text-slate-200">${formatRp(limit)}</strong>
                    </div>
                    ${limit > 0 ? `
                        <div class="mt-1 pt-1.5 border-t border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-between text-[11px] font-bold ${remainingPlafon > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'}">
                            <span>Sisa Plafon Tempo:</span>
                            <span class="font-mono">${formatRp(remainingPlafon)}</span>
                        </div>
                    ` : ''}
                </div>
            `;
        }
    }

    openPaymentModal(preferredMethod) {
        const calc = this.calculateCart();
        if (calc.items.length === 0) {
            showToast('Keranjang belanja masih kosong. Silakan pilih produk terlebih dahulu.', 'warning');
            return;
        }

        const grandTotal = calc.grandTotal;
        const totalDisplay = document.getElementById('modalGrandTotalDisplay');
        if (totalDisplay) totalDisplay.textContent = formatRp(grandTotal);

        const methodToUse = preferredMethod || this.activePaymentMethod || 'tunai';
        this.handlePaymentMethodChange(methodToUse, grandTotal);

        openModal('paymentModal');
    }

    handlePaymentMethodChange(method, grandTotal) {
        this.activePaymentMethod = method;
        const total = (typeof grandTotal === 'number' && grandTotal >= 0) ? grandTotal : this.calculateCart().grandTotal;

        // Update Modal Tabs styling & active classes
        const tabs = [
            { id: 'modalTabTunai', key: 'tunai', icon: 'payments' },
            { id: 'modalTabQris', key: 'qris', icon: 'qr_code_2' },
            { id: 'modalTabHutang', key: 'hutang', icon: 'event_note' }
        ];

        tabs.forEach(t => {
            const btn = document.getElementById(t.id);
            if (!btn) return;
            const isActive = (t.key === method);
            const dot = btn.querySelector('.tab-dot');
            const icon = btn.querySelector('.tab-icon');
            const sub = btn.querySelector('.tab-sub');

            if (isActive) {
                if (t.key === 'hutang') {
                    btn.className = "relative flex flex-col items-center justify-center p-3 sm:p-3.5 rounded-2xl border-2 border-amber-500 bg-amber-50 dark:bg-amber-950/60 text-amber-950 dark:text-amber-100 font-bold text-xs shadow-md ring-2 ring-amber-500/25 transition-all cursor-pointer select-none active:scale-[0.98]";
                    if (icon) icon.className = "material-symbols-outlined text-[26px] mb-1 text-amber-600 dark:text-amber-400 tab-icon";
                    if (sub) sub.className = "text-[10px] text-amber-800/90 dark:text-amber-300/90 font-medium mt-0.5 tab-sub";
                } else if (t.key === 'qris') {
                    btn.className = "relative flex flex-col items-center justify-center p-3 sm:p-3.5 rounded-2xl border-2 border-blue-500 bg-blue-50 dark:bg-blue-950/60 text-blue-950 dark:text-blue-100 font-bold text-xs shadow-md ring-2 ring-blue-500/25 transition-all cursor-pointer select-none active:scale-[0.98]";
                    if (icon) icon.className = "material-symbols-outlined text-[26px] mb-1 text-blue-600 dark:text-blue-400 tab-icon";
                    if (sub) sub.className = "text-[10px] text-blue-800/90 dark:text-blue-300/90 font-medium mt-0.5 tab-sub";
                } else {
                    btn.className = "relative flex flex-col items-center justify-center p-3 sm:p-3.5 rounded-2xl border-2 border-emerald-500 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-100 font-bold text-xs shadow-md ring-2 ring-emerald-500/25 transition-all cursor-pointer select-none active:scale-[0.98]";
                    if (icon) icon.className = "material-symbols-outlined text-[26px] mb-1 text-emerald-600 dark:text-emerald-400 tab-icon";
                    if (sub) sub.className = "text-[10px] text-emerald-800/90 dark:text-emerald-300/90 font-medium mt-0.5 tab-sub";
                }
                if (dot) dot.classList.remove('hidden');
            } else {
                btn.className = "relative flex flex-col items-center justify-center p-3 sm:p-3.5 rounded-2xl border border-border-subtle dark:border-slate-800 bg-surface-white dark:bg-slate-800/70 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600 font-medium text-xs transition-all cursor-pointer select-none active:scale-[0.98]";
                if (icon) icon.className = "material-symbols-outlined text-[26px] mb-1 text-slate-400 dark:text-slate-500 tab-icon";
                if (sub) sub.className = "text-[10px] text-text-muted dark:text-slate-500 font-normal mt-0.5 tab-sub";
                if (dot) dot.classList.add('hidden');
            }
        });

        // Update modal footer button theme & text
        const confirmBtn = document.getElementById('confirmPayBtn');
        if (confirmBtn) {
            if (method === 'hutang') {
                confirmBtn.className = "px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer";
                confirmBtn.innerHTML = `
                    <span class="material-symbols-outlined text-[18px]">event_note</span>
                    <span>Konfirmasi Transaksi Tempo (Kasbon)</span>
                `;
            } else if (method === 'qris') {
                confirmBtn.className = "px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer";
                confirmBtn.innerHTML = `
                    <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                    <span>Konfirmasi QRIS Telah Berhasil</span>
                `;
            } else {
                confirmBtn.className = "px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer";
                confirmBtn.innerHTML = `
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                    <span>Konfirmasi Pembayaran Tunai (F4)</span>
                `;
            }
        }

        const tunaiBox = document.getElementById('tunaiSection');
        const qrisBox = document.getElementById('qrisSection');
        const hutangBox = document.getElementById('hutangSection');

        if (tunaiBox) tunaiBox.style.display = (method === 'tunai') ? 'flex' : 'none';
        if (qrisBox) qrisBox.style.display = (method === 'qris') ? 'flex' : 'none';
        if (hutangBox) hutangBox.style.display = (method === 'hutang') ? 'flex' : 'none';

        const payInput = document.getElementById('modalPayAmount');
        if (method === 'qris') {
            if (payInput) {
                payInput.value = total;
                payInput.readOnly = true;
            }
            this.renderDynamicQris(total);
            this.calculateChange();
        } else if (method === 'tunai') {
            if (payInput) {
                payInput.readOnly = false;
                payInput.value = total;
            }
            this.calculateChange();
        } else if (method === 'hutang') {
            const dpInput = document.getElementById('modalHutangDpAmount');
            if (dpInput && (!dpInput.value || Number(dpInput.value) >= total)) {
                dpInput.value = 0;
            }
            this.updateCustomerDebtInfoModal();
            this.calculateHutangBreakdown();
        }
    }

    calculateHutangBreakdown() {
        const total = this.calculateCart().grandTotal;
        const dpInput = document.getElementById('modalHutangDpAmount');
        let dp = Number(dpInput ? dpInput.value : 0);
        if (dp < 0 || isNaN(dp)) dp = 0;
        if (dp > total) {
            dp = total;
            if (dpInput) dpInput.value = total;
        }

        const remainingDebt = Math.max(0, total - dp);
        const notice = document.getElementById('modalDebtNotice');
        const dueDate = document.getElementById('modalDueDate')?.value;
        const dueDateFormatted = dueDate ? dueDate.split('-').reverse().join('/') : '-';

        if (notice) {
            notice.innerHTML = `
                <div class="flex items-center justify-between text-[11px] pb-1.5 border-b border-amber-200/60 dark:border-amber-800/40">
                    <span class="text-amber-900/80 dark:text-amber-300/80">Total Belanja:</span>
                    <span class="font-mono font-bold text-text-main dark:text-white">${formatRp(total)}</span>
                </div>
                ${dp > 0 ? `
                <div class="flex items-center justify-between text-[11px] text-emerald-700 dark:text-emerald-300 pb-1.5 border-b border-amber-200/60 dark:border-amber-800/40">
                    <span>Uang Muka (DP Tunai):</span>
                    <span class="font-mono font-bold">-${formatRp(dp)}</span>
                </div>` : ''}
                <div class="flex items-center justify-between text-xs font-bold pt-1">
                    <span class="text-amber-950 dark:text-amber-100 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[15px] text-amber-600 dark:text-amber-400">pending_actions</span>
                        <span>Masuk Catatan Piutang:</span>
                    </span>
                    <span class="font-mono font-extrabold text-sm sm:text-base text-amber-800 dark:text-amber-300">${formatRp(remainingDebt)}</span>
                </div>
                <div class="text-[10px] text-amber-800/80 dark:text-amber-400/80 flex items-center gap-1 pt-0.5">
                    <span class="material-symbols-outlined text-[13px]">calendar_month</span>
                    <span>Wajib dilunasi sebelum: <strong class="font-mono font-bold text-amber-950 dark:text-amber-200">${dueDateFormatted}</strong></span>
                </div>
            `;
        }
    }

    updateCustomerDebtInfoModal() {
        const box = document.getElementById('modalCustomerDebtSummary');
        if (!box) return;
        if (!this.selectedCustomerId) {
            box.innerHTML = `
                <div class="p-3.5 bg-amber-50/90 dark:bg-amber-950/50 rounded-2xl border border-amber-200 dark:border-amber-800/60 text-xs text-amber-900 dark:text-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <span class="material-symbols-outlined text-[20px] text-amber-600 dark:text-amber-400 shrink-0 mt-0.5">warning</span>
                        <div class="min-w-0">
                            <strong class="font-bold block leading-tight text-amber-950 dark:text-amber-100">Pelanggan Grosir Belum Dipilih</strong>
                            <span class="text-[11px] text-amber-800/90 dark:text-amber-300/90 mt-0.5 block leading-relaxed">Transaksi tempo wajib dikaitkan dengan identitas pembeli terdaftar.</span>
                        </div>
                    </div>
                    <button type="button" onclick="posApp.promptSelectCustomer()" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold text-[11px] shadow-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <span class="material-symbols-outlined text-[15px]">person_search</span>
                        <span>Pilih Akun Pembeli</span>
                    </button>
                </div>
            `;
            return;
        }
        const cust = this.customers.find(c => c.id == this.selectedCustomerId);
        if (cust) {
            const limit = Number(cust.credit_limit || 0);
            const debt = Number(cust.current_debt || 0);
            const remaining = Math.max(0, limit - debt);
            box.innerHTML = `
                <div class="p-3.5 bg-surface-white dark:bg-slate-800 rounded-2xl border border-border-subtle dark:border-slate-700 text-xs flex flex-col gap-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 font-bold text-text-main dark:text-white">
                            <span class="material-symbols-outlined text-[16px] text-emerald-600">person</span>
                            <span>${cust.name}</span>
                        </div>
                        <span class="font-mono text-[11px] text-text-muted dark:text-slate-400">${cust.phone || '-'}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 pt-1.5 border-t border-border-subtle dark:border-slate-700/60 text-[11px]">
                        <div>
                            <span class="text-text-muted dark:text-slate-400 block text-[10px]">Hutang Berjalan</span>
                            <strong class="font-mono text-amber-700 dark:text-amber-400">${formatRp(debt)}</strong>
                        </div>
                        <div>
                            <span class="text-text-muted dark:text-slate-400 block text-[10px]">Plafon Kredit</span>
                            <strong class="font-mono text-text-main dark:text-white">${formatRp(limit)}</strong>
                        </div>
                        <div>
                            <span class="text-text-muted dark:text-slate-400 block text-[10px]">Sisa Plafon</span>
                            <strong class="font-mono ${remaining > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600'}">${formatRp(remaining)}</strong>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    promptSelectCustomer() {
        closeModal('paymentModal');
        const select = document.getElementById('customerSelect');
        if (select) {
            select.focus();
            select.scrollIntoView({ behavior: 'smooth', block: 'center' });
            select.classList.add('ring-2', 'ring-amber-500');
            setTimeout(() => select.classList.remove('ring-2', 'ring-amber-500'), 2500);
            if (typeof showToast === 'function') {
                showToast('Silakan pilih akun pembeli grosir pada panel kasir.', 'info');
            }
        }
    }

    setModalDueDateDays(days) {
        const input = document.getElementById('modalDueDate');
        if (!input) return;
        const d = new Date();
        d.setDate(d.getDate() + Number(days));
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        input.value = `${year}-${month}-${day}`;

        // Highlight active preset button (white/gray default, orange when active)
        const presetDays = [7, 14, 30];
        presetDays.forEach(num => {
            const btn = document.getElementById(`btnDays${num}`);
            if (btn) {
                if (num === Number(days)) {
                    btn.className = "px-2.5 py-1 rounded-lg border border-amber-800 bg-amber-700 text-white text-[11px] font-bold transition-all cursor-pointer shadow-xs";
                } else {
                    btn.className = "px-2.5 py-1 rounded-lg border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 text-text-muted dark:text-slate-400 text-[11px] font-semibold transition-all hover:border-amber-400 cursor-pointer";
                }
            }
        });

        this.calculateHutangBreakdown();
    }

    renderDynamicQris(amount) {
        const formatted = formatRp(amount || 0);
        const merchantName = (this.qrisConfig && this.qrisConfig.merchantName) ? this.qrisConfig.merchantName : 'MANXMARKET POS';

        // 1. Update text displays (Panel & Modal)
        const panelAmtDisplay = document.getElementById('panelQrisAmountDisplay');
        const modalAmtDisplay = document.getElementById('qrisLockedAmountDisplay');
        const panelMerchDisplay = document.getElementById('panelQrisMerchantDisplay');
        const modalMerchDisplay = document.getElementById('qrisMerchantDisplay');

        if (panelAmtDisplay) panelAmtDisplay.textContent = formatted;
        if (modalAmtDisplay) modalAmtDisplay.textContent = formatted;
        if (panelMerchDisplay) panelMerchDisplay.textContent = merchantName;
        if (modalMerchDisplay) modalMerchDisplay.textContent = merchantName;

        // If no amount, show placeholder message
        if (!amount || amount <= 0) {
            const panelCanvas = document.getElementById('panelQrisCanvas');
            if (panelCanvas) {
                panelCanvas.innerHTML = `
                    <div class="p-3 text-center text-xs text-text-muted flex flex-col items-center">
                        <span class="material-symbols-outlined text-[32px] text-blue-500 mb-1">shopping_cart</span>
                        <div class="font-bold text-xs text-text-main">Keranjang Belanja Masih Rp 0</div>
                        <div class="text-[10px] text-text-muted mt-1">Pilih produk di katalog untuk memunculkan QRIS dengan total tagihan terkunci.</div>
                    </div>
                `;
            }
            const modalCanvas = document.getElementById('qrisQrCodeCanvas');
            if (modalCanvas) {
                modalCanvas.innerHTML = `
                    <div class="p-4 text-center text-xs text-text-muted flex flex-col items-center">
                        <span class="material-symbols-outlined text-[36px] text-blue-500 mb-1">shopping_cart</span>
                        <div class="font-bold text-xs text-text-main">Belum Ada Item Belanja</div>
                        <div class="text-[11px] text-text-muted mt-1">Pilih produk di katalog terlebih dahulu.</div>
                    </div>
                `;
            }
            return;
        }

        // 2. Generate EMVCo QRIS Payload
        const staticQris = (this.qrisConfig && this.qrisConfig.staticQris) ? this.qrisConfig.staticQris : '';
        const dynamicPayload = makeDynamicQris(staticQris, amount);

        if (!dynamicPayload || typeof QRCode === 'undefined') {
            return;
        }

        // 3. Render into Panel Canvas
        const panelCanvas = document.getElementById('panelQrisCanvas');
        if (panelCanvas) {
            panelCanvas.innerHTML = '';
            try {
                new QRCode(panelCanvas, {
                    text: dynamicPayload,
                    width: 160,
                    height: 160,
                    colorDark: "#0b1c30",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {
                panelCanvas.innerHTML = `<span class="text-xs text-red-500">QR Gagal: ${e.message}</span>`;
            }
        }

        // 4. Render into Modal Canvas
        const modalCanvas = document.getElementById('qrisQrCodeCanvas');
        if (modalCanvas) {
            modalCanvas.innerHTML = '';
            try {
                new QRCode(modalCanvas, {
                    text: dynamicPayload,
                    width: 200,
                    height: 200,
                    colorDark: "#0b1c30",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {
                modalCanvas.innerHTML = `<span class="text-xs text-red-500">QR Gagal: ${e.message}</span>`;
            }
        }
    }

    calculateChange() {
        const calc = this.calculateCart();
        const grandTotal = calc.grandTotal;
        const payInput = document.getElementById('modalPayAmount');
        const paid = Number(payInput ? payInput.value : 0);
        const changeDisplay = document.getElementById('modalChangeDisplay');
        const changeCard = document.getElementById('modalChangeCard');
        const changeLabel = document.getElementById('modalChangeLabel');
        const debtNoticeDisplay = document.getElementById('modalDebtNotice');

        const activeMethod = this.activePaymentMethod || 'tunai';

        if (activeMethod === 'tunai' || activeMethod === 'qris') {
            if (debtNoticeDisplay) debtNoticeDisplay.style.display = 'none';
            if (changeDisplay) {
                if (paid >= grandTotal) {
                    const kembalian = paid - grandTotal;
                    changeDisplay.textContent = formatRp(kembalian);
                    changeDisplay.className = 'font-mono font-extrabold text-xl text-emerald-600 dark:text-emerald-400';
                    if (changeLabel) changeLabel.textContent = 'Kembalian Pelanggan:';
                    if (changeCard) changeCard.className = 'p-3.5 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 flex justify-between items-center transition-all';
                } else {
                    const kurang = grandTotal - paid;
                    changeDisplay.textContent = 'Kurang ' + formatRp(kurang);
                    changeDisplay.className = 'font-mono font-bold text-sm text-red-600 dark:text-red-400';
                    if (changeLabel) changeLabel.textContent = 'Kekurangan Uang:';
                    if (changeCard) changeCard.className = 'p-3.5 bg-red-50/70 dark:bg-red-950/30 rounded-2xl border border-red-200 dark:border-red-800/60 flex justify-between items-center transition-all';
                }
            }
        } else if (activeMethod === 'hutang') {
            const debt = Math.max(0, grandTotal - paid);
            if (debtNoticeDisplay) {
                debtNoticeDisplay.style.display = 'block';
                debtNoticeDisplay.innerHTML = `Sisa tagihan yang akan dicatat sebagai <strong>Hutang/Tempo</strong>: <strong>${formatRp(debt)}</strong>`;
            }
            if (changeDisplay) {
                changeDisplay.textContent = paid >= grandTotal ? formatRp(paid - grandTotal) : 'Rp 0';
                changeDisplay.className = 'font-mono font-extrabold text-xl text-emerald-600 dark:text-emerald-400';
                if (changeLabel) changeLabel.textContent = 'Kembalian:';
                if (changeCard) changeCard.className = 'p-3.5 bg-background-canvas dark:bg-slate-800/60 rounded-2xl border border-border-subtle dark:border-slate-700 flex justify-between items-center transition-all';
            }
        }
    }

    setQuickCash(amount) {
        const calc = this.calculateCart();
        const grandTotal = calc.grandTotal;
        const payInput = document.getElementById('modalPayAmount');
        if (!payInput) return;

        if (amount === 'pas') {
            payInput.value = grandTotal;
        } else if (typeof amount === 'string' && amount.startsWith('+')) {
            const add = Number(amount.replace('+', ''));
            payInput.value = Number(payInput.value || 0) + add;
        } else {
            payInput.value = Number(amount);
        }
        this.calculateChange();
    }

    async submitCheckout() {
        const calc = this.calculateCart();
        if (calc.items.length === 0) {
            showToast('Keranjang belanja masih kosong.', 'warning');
            return;
        }

        const activeMethod = this.activePaymentMethod || 'tunai';
        const paidAmount = (activeMethod === 'qris') 
            ? calc.grandTotal 
            : (activeMethod === 'hutang' 
                ? Number(document.getElementById('modalHutangDpAmount')?.value || 0) 
                : Number(document.getElementById('modalPayAmount')?.value || 0));
        const dueDate = document.getElementById('modalDueDate')?.value || '';

        if (activeMethod === 'tunai' && paidAmount < calc.grandTotal) {
            showToast('Uang tunai diterima kurang dari total belanja.', 'error');
            return;
        }

        if (activeMethod === 'hutang') {
            if (!this.selectedCustomerId) {
                showToast('Pembayaran Tempo/Kasbon wajib memilih Pembeli Terdaftar.', 'warning');
                return;
            }
            if (paidAmount >= calc.grandTotal) {
                showToast('Uang muka (DP) sama dengan atau melebihi total tagihan. Silakan gunakan metode Kas Tunai.', 'warning');
                return;
            }
        }

        const payload = {
            customer_id: this.selectedCustomerId || null,
            customer_name_manual: '',
            payment_method: activeMethod,
            paid_amount: paidAmount,
            due_date: dueDate,
            items: calc.items.map(it => ({
                product_id: it.product_id,
                qty: it.qty
            }))
        };

        const submitBtn = document.getElementById('confirmPayBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses Transaksi...';
        }

        try {
            const checkoutUrl = window.appUrl ? `${window.appUrl}/api/pos/checkout` : 'api/pos/checkout';
            const resp = await fetch(checkoutUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken || ''
                },
                body: JSON.stringify(payload)
            });
            const result = await resp.json();
            if (!result.success) {
                throw new Error(result.message || 'Gagal memproses transaksi.');
            }

            closeModal('paymentModal');
            this.cart = [];
            this.renderCart();

            // Refresh products stock in memory
            (result.transaction.items || []).forEach(sold => {
                const p = this.products.find(x => x.id === sold.product_id);
                if (p) p.stock -= sold.qty;
            });
            this.renderCatalog();

            showToast('Transaksi berhasil diselesaikan!', 'success');

            // Show Printable Thermal Receipt Modal
            showReceiptModal(result.transaction);

        } catch (err) {
            showToast('Error: ' + err.message, 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                if (activeMethod === 'hutang') {
                    submitBtn.textContent = 'Konfirmasi Transaksi Tempo (Kasbon)';
                } else if (activeMethod === 'qris') {
                    submitBtn.textContent = 'Konfirmasi QRIS Telah Berhasil';
                } else {
                    submitBtn.textContent = 'Konfirmasi Pembayaran Tunai (F4)';
                }
            }
        }
    }
}
