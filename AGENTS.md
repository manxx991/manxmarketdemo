# Project Standards & Agent Guidelines

## UI & Design Rules
- Setiap perubahan antarmuka (UI/UX) wajib otomatis menerapkan standar **Impeccable** dan **Anti-slop**:
  - **No Slop**: Hindari dekorasi berlebihan, gradien buram, animasi lambat, dan elemen visual tanpa fungsi.
  - **Kontras Tinggi & Tema Ganda**: Wajib mendukung Mode Terang (`#F8FAFC`, `#FFFFFF`, `#0F172A`) dan Mode Gelap (`#020617`, `#0F172A`, `#F8FAFC`).
  - **Presisi Tipografi**: Gunakan font monospace (`font-mono`) untuk seluruh data operasional kasir (SKU, harga, nota, tanggal, kuantitas).
  - **Modal Dialog Asinkron**: Dilarang menggunakan fungsi bawaan browser (`alert()`, `confirm()`). Gunakan dialog Promise-based (`window.showConfirmDialog`) dan toast glassmorphism.
  - **Aksesibilitas (WAI-ARIA)**: Dukung keyboard shortcut (`Escape`, `Tab`, hotkeys kasir) dan struktur semantik standar.

## Operational & Code Standards
- Minimal diff, native/stdlib first.
- Jangan tambahkan dependensi npm/composer baru jika bisa diselesaikan dengan Tailwind, Blade, atau Vanilla JS.
- Setelah memodifikasi view Blade, selalu jalankan `php artisan view:clear`.
- Pastikan test suite PHPUnit tetap lulus (`./vendor/bin/phpunit`).
