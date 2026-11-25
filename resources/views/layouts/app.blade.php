<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'MANXMARKET adalah sistem Point of Sale (POS) dan manajemen grosir modern terintegrasi dengan fitur QRIS dinamis, buku kasbon/piutang, kartu stok gudang, dan struk thermal.')">
    <meta name="robots" content="index, follow">

    <!-- Open Graph & Social Cards -->
    <meta property="og:site_name" content="MANXMARKET">
    <meta property="og:title" content="@yield('title', 'MANXMARKET POS & Grosir')">
    <meta property="og:description" content="Sistem Kasir (POS) dan Manajemen Grosir Modern Terintegrasi untuk efisiensi transaksi, pembukuan kasbon tempo, dan stok barang.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('assets/logo/logo.png') }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <title>@yield('title', 'MANXMARKET POS & Grosir')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo.png') }}">

    <!-- Structured Data (JSON-LD) for SEO & Agentic Browsing -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "MANXMARKET POS & Grosir",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web Browser",
        "description": "Sistem Kasir (POS) dan Manajemen Grosir Modern Terintegrasi",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR"
        }
    }
    </script>

    <!-- Google Fonts & Material Symbols (Zero-CLS Optional Text Display & Block Icons) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=optional" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=block" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=optional">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=block">
    </noscript>

    <!-- Instant Theme Initializer (Prevents Theme Flash) -->
    <script>
    (function() {
        const savedTheme = localStorage.getItem('manxmarket_theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
    </script>

    <!-- Production Compiled Tailwind CSS & Design System -->
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body class="font-sans bg-background-canvas dark:bg-slate-950 text-text-main dark:text-slate-100 min-h-screen flex flex-col antialiased">

<!-- Skip Link for Keyboard Accessibility & Screen Readers -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-slate-900 focus:text-white focus:rounded-xl focus:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-xs">Lewati ke konten utama</a>

<div class="sidebar-backdrop z-40" id="sidebarBackdrop" onclick="toggleSidebar(false)"></div>

<div class="app-layout">
    @include('components.sidebar')

    <!-- Main Content Area -->
    <div class="app-main-area flex-1 flex flex-col min-w-0">
        @include('components.topbar')

        <!-- Page Content Landmark -->
        <main id="main-content" role="main" aria-label="Konten Utama Aplikasi" class="app-content-body flex-1 p-4 md:p-6 lg:p-8 max-w-[1700px] w-full mx-auto">
            @yield('content')
        </main>
    </div>
</div>

<!-- Global Modern Confirm Modal -->
@include('components.confirm-modal')

<!-- Floating Toast Container -->
<div id="toastContainer" class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none" aria-live="polite" aria-atomic="true"></div>

<!-- Scripts -->
<script defer src="{{ asset('assets/js/qrcode.min.js') }}"></script>
<script defer src="{{ asset('assets/js/app.min.js') }}?v={{ file_exists(public_path('assets/js/app.min.js')) ? filemtime(public_path('assets/js/app.min.js')) : time() }}"></script>
<script>
window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
window.appUrl = "{{ url('/') }}";
window.currentUserRole = "{{ Auth::check() ? Auth::user()->role : '' }}";

function toggleSidebar(open) {
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!sidebar) return;
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

function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('manxmarket_theme', isDark ? 'dark' : 'light');
    showToast(isDark ? 'Mode Malam (Gelap) diaktifkan' : 'Mode Siang (Terang) diaktifkan', 'info');
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type} pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-xs font-semibold animate-slide-in`;
    
    let iconName = 'info';
    let bgClasses = 'bg-slate-900 text-white border-slate-700';
    if (type === 'success') {
        iconName = 'check_circle';
        bgClasses = 'bg-emerald-700 text-white border-emerald-600';
    } else if (type === 'warning') {
        iconName = 'warning';
        bgClasses = 'bg-amber-600 text-white border-amber-500';
    } else if (type === 'error' || type === 'danger') {
        iconName = 'error';
        bgClasses = 'bg-red-700 text-white border-red-600';
    }

    toast.className += ' ' + bgClasses;
    toast.innerHTML = `
        <span class="material-symbols-outlined text-[18px] shrink-0">${iconName}</span>
        <span class="flex-1">${message}</span>
    `;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        modal.classList.remove('hidden');
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

// Global Keyboard Shortcuts (F2: search, F9: theme toggle, ESC: close modals)
document.addEventListener('keydown', function(e) {
    if (e.key === 'F9') {
        e.preventDefault();
        toggleTheme();
    } else if (e.key === 'Escape') {
        if (typeof window._resolveConfirmDialog === 'function' && window._confirmResolve) {
            window._resolveConfirmDialog(false);
        }
        document.querySelectorAll('.modal-overlay.active, .modal-backdrop:not(.hidden)').forEach(m => {
            m.classList.remove('active');
            if (m.classList.contains('modal-backdrop')) {
                m.classList.add('hidden');
            }
        });
    }
});

// Flash messages from server
@if(session('success'))
    document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));
@endif
@if(session('error'))
    document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));
@endif
@if(isset($errors) && $errors->any())
    document.addEventListener('DOMContentLoaded', () => showToast("{{ $errors->first() }}", 'error'));
@endif
</script>

@stack('scripts')
</body>
</html>
