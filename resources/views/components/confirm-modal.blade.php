<!-- Global Impeccable & Antislop Modern Confirmation Modal -->
<div id="globalConfirmModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 z-[9998] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="globalConfirmTitle">
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-border-subtle dark:border-slate-800 transition-all transform scale-100 flex flex-col gap-4">
        <!-- Header -->
        <div class="flex items-start gap-3.5">
            <div id="globalConfirmIconContainer" class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" id="globalConfirmIcon">help</span>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-base text-text-main dark:text-white leading-tight" id="globalConfirmTitle">Konfirmasi Tindakan</h2>
                <p class="text-xs text-text-muted mt-1 leading-relaxed" id="globalConfirmMessage">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            </div>
            <button type="button" onclick="window._resolveConfirmDialog(false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 -mr-1 -mt-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" aria-label="Tutup Dialog">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Optional Details Notice Box -->
        <div id="globalConfirmDetailsBox" class="hidden p-3 rounded-xl text-xs flex items-start gap-2.5 leading-relaxed bg-background-canvas dark:bg-slate-800/60 border border-border-subtle dark:border-slate-700/50">
            <span class="material-symbols-outlined text-[16px] text-slate-400 shrink-0 mt-0.5" id="globalConfirmDetailsIcon">info</span>
            <div id="globalConfirmDetailsText" class="text-slate-600 dark:text-slate-300"></div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-border-subtle dark:border-slate-800">
            <button type="button" id="globalConfirmCancelBtn" onclick="window._resolveConfirmDialog(false)" class="px-4 py-2.5 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white font-semibold text-xs transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" id="globalConfirmActionBtn" onclick="window._resolveConfirmDialog(true)" class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-xs transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                <span class="material-symbols-outlined text-[16px]" id="globalConfirmActionIcon">check</span>
                <span id="globalConfirmActionText">Lanjutkan</span>
            </button>
        </div>
    </div>
</div>
