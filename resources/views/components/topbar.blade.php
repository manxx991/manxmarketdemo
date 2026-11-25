<header class="app-topbar h-16 bg-surface-white dark:bg-slate-900 border-b border-border-subtle dark:border-slate-800 px-4 md:px-6 flex items-center justify-between z-30 sticky top-0 shadow-xs transition-colors" role="banner">
    <div class="flex items-center gap-3">
        <button type="button" class="md:hidden p-2 rounded-xl text-text-muted hover:text-text-main dark:hover:text-white hover:bg-background-canvas dark:hover:bg-slate-800 cursor-pointer" onclick="toggleSidebar(true)" aria-label="Buka Menu">
            <span class="material-symbols-outlined text-[24px]">menu</span>
        </button>
        <div>
            <span class="text-sm md:text-base font-bold text-text-main dark:text-white leading-tight block">@yield('page_title', 'Sistem Kasir & Grosir')</span>
        </div>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        <!-- Day/Night Mode Switcher -->
        <button type="button" onclick="toggleTheme()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 hover:bg-surface-white dark:hover:bg-slate-700 text-xs font-semibold text-text-main dark:text-slate-200 transition-colors shadow-2xs cursor-pointer" title="Ganti Mode Tampilan">
            <span class="flex items-center gap-1.5 dark:hidden">
                <span class="material-symbols-outlined text-[16px] text-amber-500">light_mode</span>
                <span>Mode Terang</span>
            </span>
            <span class="hidden dark:flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-blue-400">dark_mode</span>
                <span>Mode Gelap</span>
            </span>
        </button>
    </div>
</header>
