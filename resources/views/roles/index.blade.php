@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Role - MANXMARKET')
@section('page_title', 'Manajemen Pengguna & Role')

@section('content')
<div class="flex flex-col gap-6 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl p-6 border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight">Manajemen Pengguna &amp; Hak Akses</h1>
        <p class="text-xs text-text-muted mt-1 leading-relaxed">
            {{ $userRole === 'owner' 
                ? 'Kelola akun seluruh role: Owner, Kasir, Gudang, dan Pembeli (Pelanggan Grosir) dengan plafon kredit.' 
                : 'Akses Kasir: Daftarkan akun Kasir baru dan kelola akun Pelanggan Grosir.' }}
        </p>
    </div>

    <!-- Action Toolbar: Tambah Pengguna Baru -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">person_add</span>
            </div>
            <div>
                <h2 class="font-bold text-sm text-text-main dark:text-white">Pendaftaran Akun Pengguna Baru</h2>
                <p class="text-[11px] text-text-muted">Pilih role untuk membuka formulir pembuatan akun operasional</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            @if($userRole === 'owner')
                <button type="button" onclick="openAddUserModal('kasir')" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                    <span>Tambah Kasir</span>
                </button>
            @endif
            <button type="button" onclick="openAddUserModal('pembeli')" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>Tambah Pembeli</span>
            </button>
            @if($userRole === 'owner')
                <button type="button" onclick="openAddUserModal('gudang')" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-800 hover:bg-amber-900 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">warehouse</span>
                    <span>Tambah Gudang</span>
                </button>
                <button type="button" onclick="openAddUserModal('owner')" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">shield_person</span>
                    <span>Tambah Owner</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 4 Bento KPI Cards: Roles Count -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        @if($userRole === 'owner')
            <div class="bg-surface-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] uppercase font-bold text-purple-700 dark:text-purple-400 tracking-wide truncate">Owner (Pemilik)</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 border border-purple-100 dark:border-purple-900/40 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-bold text-text-main dark:text-white font-mono leading-none">{{ $statsRows['owner'] ?? 0 }} <span class="text-xs text-text-muted font-normal font-sans">Akun</span></div>
                </div>
            </div>
        @endif

        <div class="bg-surface-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] uppercase font-bold text-blue-700 dark:text-blue-400 tracking-wide truncate">Kasir (POS)</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-bold text-text-main dark:text-white font-mono leading-none">{{ $statsRows['kasir'] ?? 0 }} <span class="text-xs text-text-muted font-normal font-sans">Akun</span></div>
            </div>
        </div>

        @if($userRole === 'owner')
            <div class="bg-surface-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] uppercase font-bold text-amber-700 dark:text-amber-400 tracking-wide truncate">Staf Gudang</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[18px]">warehouse</span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-bold text-text-main dark:text-white font-mono leading-none">{{ $statsRows['gudang'] ?? 0 }} <span class="text-xs text-text-muted font-normal font-sans">Akun</span></div>
                </div>
            </div>
        @endif

        <div class="bg-surface-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] uppercase font-bold text-emerald-700 dark:text-emerald-400 tracking-wide truncate">Pembeli (Grosir)</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">storefront</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-bold text-text-main dark:text-white font-mono leading-none">{{ $statsRows['pembeli'] ?? 0 }} <span class="text-xs text-text-muted font-normal font-sans">Akun</span></div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl p-5 border border-border-subtle dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('roles.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs font-mono outline-none focus:border-blue-500" placeholder="Cari nama, username, atau no telepon..." value="{{ $search }}">
            </div>
            <div class="w-48 relative">
                <label for="rolesRoleFilter" class="sr-only">Filter Role Pengguna</label>
                <select id="rolesRoleFilter" name="role" aria-label="Filter Role Pengguna" class="w-full pl-3.5 pr-8 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs">
                    <option value="all">Semua Role</option>
                    @if($userRole === 'owner')
                        <option value="owner" {{ $filterRole === 'owner' ? 'selected' : '' }}>Owner</option>
                        <option value="gudang" {{ $filterRole === 'gudang' ? 'selected' : '' }}>Gudang</option>
                    @endif
                    <option value="kasir" {{ $filterRole === 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="pembeli" {{ $filterRole === 'pembeli' ? 'selected' : '' }}>Pembeli (Grosir)</option>
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer">Filter</button>
            <a href="{{ route('roles.index') }}" class="px-4 py-2.5 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white text-xs font-semibold transition-colors">Reset</a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3.5">Nama Lengkap</th>
                        <th class="p-3.5">Username</th>
                        <th class="p-3.5">Role Akses</th>
                        <th class="p-3.5">No. Kontak</th>
                        <th class="p-3.5 text-right">Plafon Kredit</th>
                        <th class="p-3.5 text-center">Status</th>
                        @if($userRole === 'owner')
                            <th class="p-3.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($users as $u)
                        <tr id="userRow-{{ $u->id }}" class="hover:bg-background-subtle dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-3.5 font-semibold text-text-main dark:text-white">{{ $u->name }}</td>
                            <td class="p-3.5 font-mono text-text-muted dark:text-slate-400"><span class="text-slate-400 dark:text-slate-600 select-none">@</span>{{ $u->username }}</td>
                            <td class="p-3.5">
                                @if($u->role === 'owner')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60">OWNER</span>
                                @elseif($u->role === 'kasir')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">KASIR</span>
                                @elseif($u->role === 'gudang')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">GUDANG</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">PEMBELI</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-text-muted dark:text-slate-400 font-mono">{{ $u->phone ?: '-' }}</td>
                            <td class="p-3.5 text-right font-mono {{ $u->credit_limit > 0 ? 'font-bold text-secondary dark:text-blue-400' : 'text-text-muted dark:text-slate-500' }}">
                                {{ $u->role === 'pembeli' ? formatRupiah($u->credit_limit) : '-' }}
                            </td>
                            <td class="p-3.5 text-center user-status-cell">
                                @if($u->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/60">Nonaktif</span>
                                @endif
                            </td>
                            @if($userRole === 'owner')
                                <td class="p-3.5 text-right">
                                    @if($u->id !== Auth::id())
                                        <button type="button" id="toggleBtn-{{ $u->id }}" data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-username="{{ $u->username }}" data-role="{{ $u->role }}" data-active="{{ $u->is_active ? 1 : 0 }}" onclick="confirmToggleActive(Number(this.dataset.id), this.dataset.name, this.dataset.username, this.dataset.role, Number(this.dataset.active))" class="px-2.5 py-1 rounded-lg border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 hover:bg-background-subtle text-xs transition-colors cursor-pointer {{ $u->is_active ? 'text-text-muted hover:text-red-600 dark:hover:text-red-400' : 'text-emerald-700 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-text-muted">
                                Tidak ada akun pengguna yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-border-subtle dark:border-slate-800">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal: Tambah User / Role -->
<div id="addUserModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-border-subtle dark:border-slate-800">
        <div class="flex items-center gap-2.5 mb-4">
            <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">person_add</span>
            </div>
            <div>
                <h2 class="font-bold text-base text-text-main dark:text-white" id="userModalTitle">Tambah Akun Baru</h2>
                <p class="text-[11px] text-text-muted">Daftarkan akun hak akses operasional toko</p>
            </div>
        </div>
        <form onsubmit="submitAddUser(event)" class="flex flex-col gap-3.5 text-xs">
            @csrf
            <div>
                <label class="font-bold block text-text-main dark:text-white mb-1.5" for="userRoleField">Role Akun *</label>
                <div class="relative">
                    <select id="userRoleField" class="w-full pl-3.5 pr-8 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs font-medium appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs" onchange="togglePlafonField(this.value)">
                        @if($userRole === 'owner')
                            <option value="owner">Owner (Pemilik Toko)</option>
                            <option value="gudang">Staf Gudang (Inventaris)</option>
                        @endif
                        <option value="kasir">Kasir (POS Transaksi)</option>
                        <option value="pembeli">Pembeli (Pelanggan Grosir)</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                </div>
            </div>
            <div>
                <label class="font-bold block text-text-main dark:text-white mb-1.5">Nama Lengkap / Nama Toko *</label>
                <input type="text" id="userName" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="font-bold block text-text-main dark:text-white mb-1.5">Username Login *</label>
                <input type="text" id="userUsername" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="font-bold block text-text-main dark:text-white mb-1.5">Password *</label>
                <input type="password" id="userPassword" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="font-bold block text-text-main dark:text-white mb-1.5">Nomor WhatsApp / HP</label>
                <input type="text" id="userPhone" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500">
            </div>
            @if($userRole === 'owner')
                <div id="plafonContainer" class="hidden">
                    <label class="font-bold block text-text-main dark:text-white mb-1.5">Plafon Kredit Belanja Tempo (Rp)</label>
                    <input type="number" id="userCreditLimit" value="0" step="50000" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 font-mono font-bold text-secondary dark:text-blue-400 outline-none focus:border-blue-500">
                </div>
            @else
                <input type="hidden" id="userCreditLimit" value="0">
            @endif
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-border-subtle dark:border-slate-800 mt-2">
                <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2.5 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main font-semibold cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold shadow-xs transition-all cursor-pointer">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Konfirmasi Status Akun (Nonaktifkan / Aktifkan) -->
<div id="confirmToggleActiveModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-border-subtle dark:border-slate-800 transition-all transform scale-100">
        <!-- Header -->
        <div class="flex items-start gap-3.5 mb-4">
            <div id="toggleModalIconContainer" class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" id="toggleModalIcon">power_settings_new</span>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-base text-text-main dark:text-white leading-tight" id="toggleModalTitle">Nonaktifkan Akun Pengguna?</h2>
                <p class="text-xs text-text-muted mt-0.5" id="toggleModalSubtitle">Konfirmasi perubahan akses operasional toko</p>
            </div>
            <button type="button" onclick="closeModal('confirmToggleActiveModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 -mr-1 -mt-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Tutup">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Account Snapshot Box -->
        <div class="bg-background-canvas dark:bg-slate-800/60 rounded-2xl p-3.5 border border-border-subtle dark:border-slate-700/60 mb-4 flex flex-col gap-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 truncate">
                    <span class="material-symbols-outlined text-[16px] text-slate-400">person</span>
                    <span class="font-bold text-sm text-text-main dark:text-white truncate" id="toggleModalUserName">-</span>
                </div>
                <span class="font-mono text-xs text-slate-500 dark:text-slate-400 font-semibold bg-surface-white dark:bg-slate-800 px-2 py-0.5 rounded-md border border-border-subtle dark:border-slate-700 shrink-0" id="toggleModalUserUsername">@-</span>
            </div>
            <div class="flex items-center justify-between text-xs text-text-muted pt-2 border-t border-border-subtle dark:border-slate-700/40">
                <span class="text-[11px] font-semibold text-slate-500">Peran Akses:</span>
                <span id="toggleModalRoleBadge"></span>
            </div>
        </div>

        <!-- Impact Callout Notice -->
        <div id="toggleModalImpactBox" class="p-3.5 rounded-2xl mb-5 text-xs flex items-start gap-2.5 leading-relaxed">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5" id="toggleModalImpactIcon">info</span>
            <div id="toggleModalImpactText" class="flex-1"></div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-2.5 pt-2">
            <button type="button" onclick="closeModal('confirmToggleActiveModal')" class="px-4 py-2.5 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white font-semibold text-xs transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" id="confirmToggleActiveBtn" class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-sm transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                <span class="material-symbols-outlined text-[16px]" id="confirmToggleActiveBtnIcon">power_settings_new</span>
                <span id="confirmToggleActiveBtnText">Ya, Nonaktifkan Akun</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddUserModal(role) {
    document.getElementById('userRoleField').value = role;
    document.getElementById('userName').value = '';
    document.getElementById('userUsername').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userPhone').value = '';
    document.getElementById('userCreditLimit').value = '0';
    togglePlafonField(role);
    document.getElementById('userModalTitle').textContent = 'Tambah Role: ' + role.toUpperCase();
    openModal('addUserModal');
}

function togglePlafonField(role) {
    const p = document.getElementById('plafonContainer');
    if (role === 'pembeli') {
        p.classList.remove('hidden');
    } else {
        p.classList.add('hidden');
    }
}

async function submitAddUser(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
    }

    const payload = {
        role: document.getElementById('userRoleField').value,
        name: document.getElementById('userName').value,
        username: document.getElementById('userUsername').value,
        password: document.getElementById('userPassword').value,
        phone: document.getElementById('userPhone').value,
        credit_limit: document.getElementById('userCreditLimit').value
    };

    try {
        const resp = await fetch('{{ route("roles.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);
        
        closeModal('addUserModal');
        e.target.reset();

        // Modern Impeccable Interactive Notification
        if (typeof showUserCreatedToast === 'function') {
            showUserCreatedToast(res.user);
        } else if (typeof showToast === 'function') {
            showToast(res.message, 'success');
        }

        // Dynamically prepend new user row with smooth highlight
        const tbody = document.querySelector('table tbody');
        if (tbody && res.user) {
            const emptyState = tbody.querySelector('td[colspan]');
            if (emptyState) emptyState.closest('tr').remove();

            const roleBadges = {
                owner: '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60">OWNER</span>',
                kasir: '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">KASIR</span>',
                gudang: '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">GUDANG</span>',
                pembeli: '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">PEMBELI</span>'
            };

            const tr = document.createElement('tr');
            tr.id = `userRow-${res.user.id}`;
            tr.className = 'bg-blue-50/80 dark:bg-blue-950/50 border-l-4 border-l-blue-600 transition-all duration-700 animate-slide-in';
            const creditFormatted = res.user.role === 'pembeli' ? ('Rp ' + Number(res.user.credit_limit || 0).toLocaleString('id-ID')) : '-';
            const isOwnerRole = '{{ $userRole }}' === 'owner';

            tr.innerHTML = `
                <td class="p-3.5 font-semibold text-text-main dark:text-white flex items-center gap-2">
                    <span>${escapeHtml(res.user.name)}</span>
                    <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded bg-blue-600 text-white font-mono">Baru</span>
                </td>
                <td class="p-3.5 font-mono text-text-muted dark:text-slate-400">@${escapeHtml(res.user.username)}</td>
                <td class="p-3.5">${roleBadges[res.user.role] || escapeHtml(res.user.role ? res.user.role.toUpperCase() : '-')}</td>
                <td class="p-3.5 text-text-muted dark:text-slate-400 font-mono">${escapeHtml(res.user.phone || '-')}</td>
                <td class="p-3.5 text-right font-mono ${res.user.credit_limit > 0 ? 'font-bold text-secondary dark:text-blue-400' : 'text-text-muted dark:text-slate-500'}">
                    ${creditFormatted}
                </td>
                <td class="p-3.5 text-center user-status-cell">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">Aktif</span>
                </td>
                ${isOwnerRole ? `
                    <td class="p-3.5 text-right">
                        <button type="button" id="toggleBtn-${res.user.id}" data-id="${res.user.id}" data-name="${escapeHtml(res.user.name)}" data-username="${escapeHtml(res.user.username)}" data-role="${escapeHtml(res.user.role)}" data-active="1" onclick="confirmToggleActive(Number(this.dataset.id), this.dataset.name, this.dataset.username, this.dataset.role, Number(this.dataset.active))" class="px-2.5 py-1 rounded-lg border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 hover:bg-background-subtle text-xs text-text-muted hover:text-red-600 dark:hover:text-red-400 transition-colors cursor-pointer">
                            Nonaktifkan
                        </button>
                    </td>
                ` : ''}
            `;

            tbody.insertBefore(tr, tbody.firstChild);

            setTimeout(() => {
                tr.classList.remove('bg-blue-50/80', 'dark:bg-blue-950/50', 'border-l-4', 'border-l-blue-600');
                tr.classList.add('hover:bg-background-subtle', 'dark:hover:bg-slate-800/50');
            }, 3500);
        }
    } catch (err) {
        if (typeof showToast === 'function') {
            showToast('Error: ' + err.message, 'error');
        }
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Daftarkan Akun';
        }
    }
}

// Confirmation Dialog State
let currentToggleTarget = null;

function confirmToggleActive(userId, name, username, role, currentStatus) {
    const isCurrentlyActive = Boolean(Number(currentStatus));
    const nextStatus = isCurrentlyActive ? 0 : 1;
    currentToggleTarget = { userId, name, username, role, currentStatus: isCurrentlyActive, nextStatus };

    const modal = document.getElementById('confirmToggleActiveModal');
    if (!modal) return;

    const iconContainer = document.getElementById('toggleModalIconContainer');
    const icon = document.getElementById('toggleModalIcon');
    const title = document.getElementById('toggleModalTitle');
    const subtitle = document.getElementById('toggleModalSubtitle');
    const userName = document.getElementById('toggleModalUserName');
    const userUsername = document.getElementById('toggleModalUserUsername');
    const roleBadge = document.getElementById('toggleModalRoleBadge');
    const impactBox = document.getElementById('toggleModalImpactBox');
    const impactIcon = document.getElementById('toggleModalImpactIcon');
    const impactText = document.getElementById('toggleModalImpactText');
    const confirmBtn = document.getElementById('confirmToggleActiveBtn');
    const confirmBtnIcon = document.getElementById('confirmToggleActiveBtnIcon');
    const confirmBtnText = document.getElementById('confirmToggleActiveBtnText');

    userName.textContent = name;
    userUsername.textContent = '@' + username;

    const roleBadges = {
        owner: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60">OWNER</span>',
        kasir: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">KASIR</span>',
        gudang: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">GUDANG</span>',
        pembeli: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">PEMBELI</span>'
    };
    roleBadge.innerHTML = roleBadges[role] || (role ? role.toUpperCase() : '-');

    if (isCurrentlyActive) {
        // Deactivation Dialog Mode
        iconContainer.className = 'w-11 h-11 rounded-2xl bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400 flex items-center justify-center shrink-0 border border-red-200 dark:border-red-900/50';
        icon.textContent = 'power_settings_new';
        title.textContent = 'Nonaktifkan Akun Pengguna?';
        subtitle.textContent = 'Hak akses operasional dan login akun akan dibekukan';

        impactBox.className = 'p-3.5 rounded-2xl mb-5 text-xs flex items-start gap-2.5 leading-relaxed bg-red-50/80 text-red-900 dark:bg-red-950/30 dark:text-red-300 border border-red-200/80 dark:border-red-900/50';
        impactIcon.textContent = 'warning';
        impactIcon.className = 'material-symbols-outlined text-[18px] text-red-600 dark:text-red-400 shrink-0 mt-0.5';
        impactText.innerHTML = `
            <strong>Dampak Tindakan:</strong> Pengguna tidak akan dapat masuk ke sistem, sesi aktif dihentikan, dan hak transaksi kasir/inventaris dinonaktifkan hingga diaktifkan kembali.
        `;

        confirmBtn.className = 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-xs bg-red-600 hover:bg-red-700 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer';
        confirmBtnIcon.textContent = 'block';
        confirmBtnText.textContent = 'Ya, Nonaktifkan Akun';
    } else {
        // Activation Dialog Mode
        iconContainer.className = 'w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-900/50';
        icon.textContent = 'check_circle';
        title.textContent = 'Aktifkan Kembali Akun?';
        subtitle.textContent = 'Hak akses operasional dan login akan dipulihkan';

        impactBox.className = 'p-3.5 rounded-2xl mb-5 text-xs flex items-start gap-2.5 leading-relaxed bg-emerald-50/80 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-900/50';
        impactIcon.textContent = 'verified_user';
        impactIcon.className = 'material-symbols-outlined text-[18px] text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5';
        impactText.innerHTML = `
            <strong>Dampak Tindakan:</strong> Pengguna akan dapat segera login kembali dan menjalankan tugas operasional sesuai peran <strong>${role.toUpperCase()}</strong>.
        `;

        confirmBtn.className = 'px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-xs bg-emerald-800 hover:bg-emerald-900 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer';
        confirmBtnIcon.textContent = 'check_circle';
        confirmBtnText.textContent = 'Ya, Aktifkan Akun';
    }

    confirmBtn.onclick = executeToggleActive;
    openModal('confirmToggleActiveModal');
}

async function executeToggleActive() {
    if (!currentToggleTarget) return;
    const { userId, name, username, role, currentStatus, nextStatus } = currentToggleTarget;

    const confirmBtn = document.getElementById('confirmToggleActiveBtn');
    const confirmBtnText = document.getElementById('confirmToggleActiveBtnText');
    const originalText = confirmBtnText.textContent;

    confirmBtn.disabled = true;
    confirmBtnText.textContent = 'Memproses...';

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const resp = await fetch(`{{ url('api/roles') }}/${userId}/toggle-active`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });
        const res = await resp.json();
        if (!resp.ok || !res.success) throw new Error(res.message || 'Gagal mengubah status akun.');

        closeModal('confirmToggleActiveModal');

        const userObj = res.user || { id: userId, name, username, role, is_active: nextStatus };

        // Show modern status change toast
        if (typeof showUserStatusToast === 'function') {
            showUserStatusToast(userObj, nextStatus);
        } else if (typeof showToast === 'function') {
            showToast(res.message, nextStatus ? 'success' : 'warning');
        }

        // Dynamically update table row
        updateTableRowStatus(userId, name, username, role, nextStatus);

    } catch (err) {
        if (typeof showToast === 'function') {
            showToast('Error: ' + err.message, 'error');
        }
    } finally {
        confirmBtn.disabled = false;
        confirmBtnText.textContent = originalText;
    }
}

function updateTableRowStatus(userId, name, username, role, newStatus) {
    const row = document.getElementById(`userRow-${userId}`);
    if (!row) return;

    const isActive = Boolean(Number(newStatus));
    const statusCell = row.querySelector('.user-status-cell');
    if (statusCell) {
        statusCell.innerHTML = isActive
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">Aktif</span>'
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/60">Nonaktif</span>';
    }

    const toggleBtn = document.getElementById(`toggleBtn-${userId}`);
    if (toggleBtn) {
        toggleBtn.textContent = isActive ? 'Nonaktifkan' : 'Aktifkan';
        toggleBtn.className = `px-2.5 py-1 rounded-lg border border-border-subtle dark:border-slate-700 bg-surface-white dark:bg-slate-800 hover:bg-background-subtle text-xs transition-colors cursor-pointer ${isActive ? 'text-text-muted hover:text-red-600 dark:hover:text-red-400' : 'text-emerald-700 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-950/40'}`;
        toggleBtn.dataset.active = isActive ? '1' : '0';
    }

    // Glow highlight row
    const highlightClasses = isActive 
        ? ['bg-emerald-50/80', 'dark:bg-emerald-950/40', 'border-l-4', 'border-l-emerald-600']
        : ['bg-red-50/80', 'dark:bg-red-950/40', 'border-l-4', 'border-l-red-600'];

    row.classList.add(...highlightClasses);
    setTimeout(() => {
        row.classList.remove(...highlightClasses);
    }, 2500);
}
</script>
@endpush
