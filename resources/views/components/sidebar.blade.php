@php
$user = Auth::user();
$role = $user ? $user->role : 'guest';
$route = Route::currentRouteName() ?? '';

$roleBadges = [
    'owner' => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/60 dark:text-purple-200 dark:border-purple-700/50',
    'kasir' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/60 dark:text-blue-200 dark:border-blue-700/50',
    'gudang' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/60 dark:text-amber-200 dark:border-amber-700/50',
    'pembeli' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/60 dark:text-emerald-200 dark:border-emerald-700/50',
];
@endphp

<aside class="app-sidebar no-print flex flex-col fixed top-0 bottom-0 left-0 z-50 transition-transform duration-200 ease-in-out" id="appSidebar" aria-label="Panel Navigasi Samping">
    <!-- Brand Header -->
    <div class="sidebar-header !h-auto py-5 px-4 flex flex-col items-center justify-center relative border-b border-border-subtle dark:border-slate-800">
        <div class="flex flex-col items-center gap-2 text-center select-none">
            <img src="{{ asset('assets/logo/logo.webp') }}" alt="Logo MANXMARKET" width="56" height="56" class="w-14 h-14 rounded-2xl object-contain bg-white shadow-md p-1.5 border border-border-subtle/80">
            <div class="flex flex-col items-center">
                <span class="sidebar-brand-title font-black tracking-wider text-base text-slate-900 dark:text-white uppercase leading-tight">
                    MANXMARKET
                </span>
                <span class="sidebar-brand-subtitle text-[9px] font-mono tracking-widest uppercase font-bold mt-0.5">
                    POS &amp; GROSIR SYSTEM
                </span>
            </div>
        </div>
        <button type="button" class="md:hidden absolute top-3 right-3 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white p-1" onclick="toggleSidebar(false)" aria-label="Tutup Menu Navigasi">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">close</span>
        </button>
    </div>

    <!-- Navigation List -->
    <nav class="sidebar-nav-container flex-1 overflow-y-auto p-3" aria-label="Navigasi Utama">
        <ul class="sidebar-nav-list flex flex-col gap-1 list-none p-0 m-0">
            @if(in_array($role, ['owner', 'kasir']))
                <li class="sidebar-section-title">Menu Operasional</li>
            @endif
            @if($role === 'owner')
                <li class="sidebar-item {{ str_starts_with($route, 'reports') ? 'active' : '' }}">
                    <a href="{{ route('reports.index') }}" @if(str_starts_with($route, 'reports')) aria-current="page" @endif class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-blue-600 dark:text-blue-400" aria-hidden="true">analytics</span>
                        <span>Laporan &amp; Analitik</span>
                    </a>
                </li>
            @endif

            @if(in_array($role, ['owner', 'kasir']))
                <li class="sidebar-item {{ str_starts_with($route, 'pos') ? 'active' : '' }}">
                    <a href="{{ route('pos.index') }}" @if(str_starts_with($route, 'pos')) aria-current="page" @endif class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-blue-600 dark:text-blue-400" aria-hidden="true">point_of_sale</span>
                        <span>Kasir (POS) Grosir</span>
                    </a>
                </li>
                <li class="sidebar-item {{ str_starts_with($route, 'transactions') ? 'active' : '' }}">
                    <a href="{{ route('transactions.index') }}" @if(str_starts_with($route, 'transactions')) aria-current="page" @endif class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-slate-600 dark:text-slate-400" aria-hidden="true">receipt_long</span>
                        <span>Riwayat &amp; Void</span>
                    </a>
                </li>
                <li class="sidebar-item {{ str_starts_with($route, 'receivables') ? 'active' : '' }}">
                    <a href="{{ route('receivables.index') }}" @if(str_starts_with($route, 'receivables')) aria-current="page" @endif class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-amber-600 dark:text-amber-400" aria-hidden="true">menu_book</span>
                        <span>Buku Kasbon / Piutang</span>
                    </a>
                </li>
            @endif

            <!-- SECTION 2: Inventaris & Portal -->
            @if(in_array($role, ['owner', 'gudang', 'pembeli']))
                <li class="sidebar-section-title mt-2">Inventaris &amp; Portal</li>

                @if(in_array($role, ['owner', 'gudang']))
                    <li class="sidebar-item {{ str_starts_with($route, 'inventory') ? 'active' : '' }}">
                        <a href="{{ route('inventory.index') }}" @if(str_starts_with($route, 'inventory')) aria-current="page" @endif class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-amber-600 dark:text-amber-500" aria-hidden="true">inventory_2</span>
                            <span>Stok &amp; Mutasi Gudang</span>
                        </a>
                    </li>
                @endif

                @if(in_array($role, ['owner', 'pembeli']))
                    <li class="sidebar-item {{ str_starts_with($route, 'portal') ? 'active' : '' }}">
                        <a href="{{ route('portal.index') }}" @if(str_starts_with($route, 'portal')) aria-current="page" @endif class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-emerald-600 dark:text-emerald-500" aria-hidden="true">storefront</span>
                            <span>Portal Pembeli</span>
                        </a>
                    </li>
                @endif
            @endif

            <!-- SECTION 3: Sistem & Hak Akses (Khusus Owner) -->
            @if($role === 'owner')
                <li class="sidebar-section-title mt-2">Sistem &amp; Hak Akses</li>

                <li class="sidebar-item {{ str_starts_with($route, 'roles') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" @if(str_starts_with($route, 'roles')) aria-current="page" @endif class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-emerald-600 dark:text-emerald-500" aria-hidden="true">manage_accounts</span>
                        <span>Manajemen Role &amp; User</span>
                    </a>
                </li>

                <li class="sidebar-item {{ str_starts_with($route, 'settings') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}" @if(str_starts_with($route, 'settings')) aria-current="page" @endif class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-slate-600 dark:text-slate-400" aria-hidden="true">tune</span>
                        <span>Metode Pembayaran &amp; Toko</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    <!-- Footer Profile & Session Control -->
    <div class="sidebar-footer p-3 border-t border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between bg-slate-100/90 border border-slate-200/80 dark:bg-slate-800/80 dark:border-slate-700/50 p-2.5 rounded-2xl shadow-2xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-xs" aria-hidden="true">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-xs font-bold text-slate-800 dark:text-white truncate leading-tight">{{ $user->name ?? 'Pengguna' }}</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full inline-block mt-0.5 border {{ $roleBadges[$role] ?? 'bg-slate-200 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-white dark:border-slate-600' }} w-fit uppercase font-semibold">
                        {{ $role }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-2 rounded-lg text-slate-600 hover:text-red-700 hover:bg-white dark:hover:bg-slate-700 dark:text-slate-300 dark:hover:text-red-400 transition-colors cursor-pointer" title="Keluar dari Sistem" aria-label="Keluar dari Sistem">
                        <span class="material-symbols-outlined text-[19px]" aria-hidden="true">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

@if(in_array($role, ['owner', 'kasir']))
    @include('components.quick-add-role-modal')
@endif
