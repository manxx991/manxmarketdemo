# MANXMARKET: Aplikasi Kasir (POS) & Manajemen Grosir

Aplikasi Kasir dan Stok Gudang berbasis Laravel 10, PHP 8.1, dan MySQL 8.x.

## Fitur Utama
- **POS Meja Kasir:** Transaksi cepat, pencarian barcode, split-screen keranjang, dan cetak struk thermal 58mm/80mm.
- **Pembayaran QRIS Dinamis:** Standar EMVCo ASPI MPM dengan nominal belanja terkunci presisi di browser kasir.
- **Buku Piutang & Kasbon:** Manajemen belanja tempo, pembayaran bertahap/cicilan, dan batas limit kredit pelanggan.
- **Katalog & Stok Gudang:** Manajemen SKU, barcode produk, restock, opname fisik, dan audit mutasi stok.
- **Otorisasi Ketat (RBAC):** Hak akses terisolasi untuk Owner, Kasir, Gudang, dan Pembeli (termasuk proteksi void eksklusif Owner).
- **Otomasi Cadangan:** Pencadangan database otomatis harian pukul 22.00 dengan rotasi 7 hari dan sinkronisasi offsite.
