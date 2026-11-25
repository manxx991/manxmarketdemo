<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Masuk ke MANXMARKET POS &amp; Grosir System. Portal otentikasi kasir, pemilik toko, staf gudang, dan pembeli grosir.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/login') }}">
    <title>Login - MANXMARKET POS &amp; Grosir</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo.png') }}">
    
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
    
    <!-- Instant Theme Initializer -->
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

    <!-- Compiled Production Tailwind CSS & Design System -->
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-background-canvas dark:bg-slate-950 text-text-main dark:text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased transition-colors">

<main role="main" aria-label="Portal Masuk Sistem" class="w-full max-w-md bg-surface-white dark:bg-slate-900 rounded-3xl p-8 shadow-xl border border-border-subtle dark:border-slate-800 relative transition-colors">
    <!-- Theme Toggle in Login Screen -->
    <div class="absolute right-6 top-6">
        <button type="button" onclick="toggleTheme()" class="w-8 h-8 rounded-lg border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 hover:bg-surface-white dark:hover:bg-slate-700 flex items-center justify-center text-text-main dark:text-slate-200 transition-colors cursor-pointer" title="Beralih Tema Terang / Gelap (F9)" aria-label="Beralih Tema Terang / Gelap">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">dark_mode</span>
        </button>
    </div>

    <div class="text-center mb-6">
        <div class="flex justify-center mb-3">
            <img src="{{ asset('assets/logo/logo.webp') }}" alt="Logo MANXMARKET" width="64" height="64" class="w-16 h-16 rounded-2xl object-contain bg-white shadow-sm p-1 border border-border-subtle dark:border-slate-800">
        </div>
        <div class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-blue-700 dark:text-blue-400 font-bold text-xs mb-2">
            MANXMARKET POS &amp; GROSIR
        </div>
        <h1 class="text-2xl font-extrabold text-text-main dark:text-white tracking-tight">Selamat Datang</h1>
        <p class="text-xs text-text-muted mt-1">Masuk ke akun operasional sesuai peran Anda</p>
    </div>

    @if ($errors->any())
        <div class="p-3 mb-4 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-900 dark:text-red-200 border border-red-200 dark:border-red-800 text-xs font-semibold flex items-center gap-2" role="alert">
            <span class="material-symbols-outlined text-[18px] text-red-600 dark:text-red-400" aria-hidden="true">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" class="flex flex-col gap-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-text-main dark:text-slate-200 mb-1.5" for="username">Username</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3.5 top-2.5 text-slate-500 dark:text-slate-400 text-[18px]" aria-hidden="true">person</span>
                <input type="text" id="username" name="username" value="{{ old('username') }}" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-xs text-text-main dark:text-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 transition-all font-mono" placeholder="Contoh: owner, kasir, gudang" required autofocus>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-text-main dark:text-slate-200 mb-1.5" for="password">Password</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3.5 top-2.5 text-slate-500 dark:text-slate-400 text-[18px]" aria-hidden="true">lock</span>
                <input type="password" id="password" name="password" class="w-full pl-10 pr-11 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-xs text-text-main dark:text-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 transition-all" placeholder="Masukkan password akun..." required>
                <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 transition-colors cursor-pointer flex items-center justify-center p-1" title="Tampilkan / Sembunyikan Password" aria-label="Tampilkan atau sembunyikan password">
                    <span class="material-symbols-outlined text-[18px]" id="passwordToggleIcon" aria-hidden="true">visibility</span>
                </button>
            </div>
        </div>

        <button type="submit" class="w-full py-3 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold text-sm shadow-sm transition-all mt-1 cursor-pointer">
            Masuk ke Sistem
        </button>
    </form>
</main>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('password');
    const icon = document.getElementById('passwordToggleIcon');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}

function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('manxmarket_theme', isDark ? 'dark' : 'light');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'F9') {
        e.preventDefault();
        toggleTheme();
    }
});
</script>
</body>
</html>
