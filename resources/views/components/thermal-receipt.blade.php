<!-- Thermal Receipt Modal & Isolated Iframe Print Engine -->
<div id="receiptModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="receiptModalTitle">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]" aria-hidden="true">receipt_long</span>
                <h2 class="modal-title font-bold text-base text-text-main dark:text-white" id="receiptModalTitle">Struk Pembayaran Kasir</h2>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('receiptModal')" aria-label="Tutup Struk">&times;</button>
        </div>
        <div class="modal-body p-4 bg-slate-100 dark:bg-slate-900/80 flex justify-center">
            <div id="receiptContent" class="w-full max-w-[340px] bg-white text-slate-950 rounded-xl p-3 shadow-md border border-slate-200 dark:border-slate-800">
                <!-- Injected dynamically by JS -->
            </div>
        </div>
        <div class="modal-footer flex flex-wrap items-center justify-between gap-2">
            <button type="button" class="btn btn-secondary text-xs" onclick="closeModal('receiptModal')">Tutup</button>
            <div class="flex items-center gap-2">
                <button type="button" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-text-main dark:text-white text-xs font-bold flex items-center gap-1.5 shadow-2xs cursor-pointer transition-colors" onclick="printThermalReceipt('58mm')" title="Cetak format kertas 58mm (Printer mini thermal kasir)">
                    <span class="material-symbols-outlined text-[16px] text-blue-600 dark:text-blue-400">receipt</span>
                    <span>Cetak 58mm</span>
                </button>
                <button type="button" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-2xs cursor-pointer" onclick="printThermalReceipt('80mm')" title="Cetak format kertas 80mm (Printer desktop standar)">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>Cetak 80mm</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Close receipt modal helper
function handleCloseReceiptModal() {
    closeModal('receiptModal');
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        setTimeout(() => searchInput.focus(), 100);
    }
}
</script>
