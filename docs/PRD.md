# Product Requirements Document (PRD): MANXMARKET: Aplikasi Kasir (POS) & Manajemen Grosir

**Nama Aplikasi:** MANXMARKET POS & Grosir  
**Status Dokumen:** Terimplementasi Penuh & Terverifikasi (v2.0)  
**Terakhir Diperbarui:** 29 Oktober 2026  
**Platform Target:** Web Application Responsif (Desktop PC Kasir, Tablet Layar Sentuh, & Mobile Browser)  
**Teknologi & Lingkungan:** PHP 8.1 Native, MySQL 8.x (InnoDB ACID Engine via Laragon), Tailwind CSS, Vanilla JS PosEngine, Standar QRIS EMVCo ASPI MPM  

---

## 1. Ringkasan Eksekutif & Tujuan Produk

**MANXMARKET** adalah aplikasi kasir (*Point of Sale* / POS) dan manajemen grosir terintegrasi yang dirancang khusus untuk memenuhi kebutuhan toko grosir dan ritel modern. Sistem ini menggabungkan kecepatan transaksi meja kasir, fleksibilitas diskon grosir bertingkat (*multi-tier wholesale*), kepatuhan standar pembayaran nasional **QRIS Dinamis EMVCo (Shopee Partner / DANA Bisnis / Bank)** dengan nominal terkunci otomatis, buku kasbon/piutang tempo dengan plafon kredit, serta pemisahan halaman dan hak akses ketat berbasis peran pengguna (**Owner**, **Kasir**, **Gudang**, dan **Pembeli**).

### Tujuan Utama Produk:
1. **Pemisahan Halaman Kerja Berdasarkan Peran (Role-Based Page Segregation):** Mencegah tumpang tindih operasional dengan mengarahkan pengguna secara otomatis ke halaman khusus sesuai tugas utamanya saat login.
2. **Desentralisasi Pendaftaran Pelanggan & Kasir (Role Action Delegation):** Memberikan wewenang kepada kasir di garda terdepan untuk mendaftarkan akun kasir pendamping dan pelanggan grosir baru beserta plafon kreditnya langsung dari meja kasir, tanpa harus bergantung pada kehadiran Owner.
3. **Efisiensi & Keamanan Pembayaran QRIS Dinamis:** Mengonversi string QRIS statis merchant menjadi QRIS dinamis ber-Tag 54 (*Transaction Amount*) standar Bank Indonesia / ASPI secara lokal dan instan di browser kasir, mengunci nominal belanja secara presisi guna mencegah salah ketik nominal uang atau kecurangan pembeli.
4. **Otomatisasi Diskon Grosir Bertingkat:** Menghitung potongan harga per item secara otomatis begitu kuantitas barang di keranjang mencapai ambang batas tier grosir.
5. **Keandalan Data Finansial & Inventaris:** Melindungi integritas transaksi dengan transaksi database MySQL (*ACID*), *row-level locking* (`FOR UPDATE`), mekanisme nota unik anti-duplikasi, serta jejak audit lengkap pada setiap pergerakan stok dan pembatalan (*void*).

---

## 2. Arsitektur Peran & Pemisahan Halaman (RBAC Matrix)

Sistem menerapkan sistem *Role-Based Access Control* (RBAC) pada level *middleware* backend PHP (`config/auth.php`) dan navigasi sidebar responsif (`includes/header.php`).

### 2.1. Alur Pengalihan Rute Utama (Landing Page Routing)
Begitu pengguna berhasil diautentikasi pada halaman `login.php` (atau mengakses `index.php`), sistem mengarahkan sesi secara otomatis ke halaman tujuan berikut:
* **👑 Owner** &rarr; `reports.php` (Dashboard Eksekutif, Analisis Laba Kotor, HPP, Omzet, & Rekapitulasi Pembayaran)
* **💳 Kasir** &rarr; `pos.php` (Katalog POS Kasir, Split Screen Keranjang, QRIS Dinamis, & Struk Thermal)
* **📦 Gudang** &rarr; `inventory.php` (Manajemen Produk, Kartu Stok, Koreksi Opname & Mutasi Fisik)
* **🏪 Pembeli** &rarr; `portal.php` (Portal Pelanggan Grosir, Plafon Kredit, Tagihan Kasbon, & Riwayat Belanja)

### 2.2. Matriks Hak Akses & Wewenang Peran

| Modul & Fitur | Halaman URL | Owner | Kasir | Gudang | Pembeli |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **Laporan & Analitik Keuangan** | `/reports` | **Akses Penuh** | Ditolak (403) | Ditolak (403) | Ditolak (403) |
| **Meja Kasir POS & Transaksi** | `/pos` | Akses Penuh | **Akses Penuh** | Ditolak (403) | Ditolak (403) |
| **Riwayat Nota & Eksekusi Void** | `/transactions` | **Akses Penuh (Bisa Void)** | Lihat & Cetak Saja* | Ditolak (403) | Ditolak (403) |
| **Katalog Stok & Mutasi Gudang** | `/inventory` | Akses Penuh | Ditolak (403) | **Akses Penuh** | Ditolak (403) |
| **Buku Hutang Piutang & Cicilan** | `/receivables` | Akses Penuh | **Akses Penuh** | Ditolak (403) | Ditolak (403) |
| **Manajemen Role & User (Penuh)** | `/roles` | **Akses Eksklusif** | Ditolak (403)* | Ditolak (403) | Ditolak (403) |
| **Pengaturan Toko & QRIS Merchant** | `/settings` | **Akses Penuh** | Ditolak (403) | Ditolak (403) | Ditolak (403) |
| **Portal Pelanggan Grosir** | `/portal` | Akses Pantau | Ditolak (403) | Ditolak (403) | **Akses Penuh** |

*\*Catatan:* 
- Kasir dapat mendaftarkan pelanggan baru khusus role `pembeli` langsung dari meja POS (`/pos`) via endpoint `/api/customers/register` dengan plafon kredit otomatis Rp 0 (ditegakkan ketat di server).
- Eksekusi pembatalan nota (VOID) pada `/transactions` mutlak wewenang Owner; kontrol void disembunyikan total dari UI kasir dan dilindungi middleware `role:owner`.

---

## 3. Fitur Khusus: Pendaftaran Pelanggan & Hak Akses Role

Sesuai kebutuhan operasional pasar grosir dan mitigasi risiko kasbon liar, pembagian wewenang pengguna ditetapkan ketat sebagai berikut:

### 3.1. Hak Eksklusif Owner (`/roles` & `/api/roles`)
* **Cakupan Penuh:** Owner memegang wewenang mutlak untuk membuat dan mengelola seluruh jenis akun (`owner`, `kasir`, `gudang`, `pembeli`).
* **Fitur Eksklusif:**
  * Mengaktifkan atau menonaktifkan akun staf/pelanggan (*toggle active status*).
  * Menentukan serta mengubah batas plafon kredit hutang kasbon (*credit_limit*) untuk pelanggan grosir.
  * Audit seluruh daftar pengguna dan staf toko.

### 3.2. Hak Terbatas Kasir Meja POS (`/api/customers/register`)
* **Pendaftaran Pelanggan Baru Khusus Pembeli:** Kasir berwenang mendaftarkan pelanggan walk-in baru langsung melalui modal `+ Pelanggan` di meja kasir (`/pos`).
* **Proteksi Mutlak Server (Strict Server-Side Enforcement):**
  * Server secara mutlak memaksakan `role = 'pembeli'` dan `credit_limit = 0.00`, mengabaikan input apapun dari client.
  * Kasir tidak memiliki akses ke halaman `/roles` maupun endpoint `/api/roles` (keduanya terkunci ke `role:owner` dengan respon **HTTP 403 Forbidden**).
  * Pembuatan akun staf dan kenaikan plafon kredit belanja tempo mutlak wewenang Owner.

---

## 4. Spesifikasi Fungsional Modul Utama

### 4.1. Meja Kasir POS & QRIS Dinamis (`pos.php`)
* **Layout Split-Screen Responsif:** Menggunakan CSS Grid `grid-cols-1 lg:grid-cols-[1fr_390px] xl:grid-cols-[1fr_420px]` yang memastikan katalog produk di kiri dan panel keranjang kasir di kanan selalu sejajar (*sticky*) pada seluruh resolusi monitor kasir.
* **Filter Kategori Cepat:** Tombol kategori pembungkus (*flex-wrap*) dengan indikator aktif kontras (`bg-primary text-white`) yang memfilter kartu produk secara instan.
* **Tombol Tambah Produk Ganda:** Produk dapat ditambahkan ke keranjang belanja baik dengan mengeklik kartu produk maupun mengeklik tombol `[ + ]` langsung.
* **Panel Tab Metode Pembayaran Terpadu:**
  * **[ 💵 Tunai ]:** Form input nominal uang diterima dengan kalkulasi otomatis uang kembalian atau status kurang bayar. Dilengkapi tombol pecahan cepat di modal (*Uang Pas, 50k, 100k, 200k, 500k*).
  * **[ 📱 QRIS Dinamis ]:** Panel QRIS langsung aktif di bawah keranjang belanja kasir. Menampilkan canvas QR code beresolusi tinggi (di-render via `qrcode.min.js`), nama merchant resmi, dan banner total tagihan terkunci ekstra besar. Kasir dapat memperbesar QRIS ke modal layar penuh (F4) atau menyelesaikan transaksi langsung dalam 1 klik.
  * **[ 📝 Tempo / Kasbon ]:** Mewajibkan pemilihan *Pembeli Terdaftar*, menampilkan status sisa plafon kredit dan batas jatuh tempo piutang.
* **Notifikasi Toast Non-Blocking:** Seluruh peringatan sistem menggunakan toast dinamis (`showToast`) yang ramah pengguna dan tidak membekukan antarmuka browser.
* **Penomoran Nota Unik Anti-Benturan:** Format faktur `INV-YYYYMMDD-XXXX` yang dihitung secara dinamis dari nilai maksimum urutan hari ini dengan verifikasi keunikan sebelum penyimpanan database.

### 4.2. Logika Standar QRIS Dinamis ASPI & EMVCo MPM (`includes/qris_helper.php`)
Sistem mendukung integrasi QRIS dari penyedia mana pun (Shopee Partner / ShopeePay, DANA Bisnis, GoPay Merchant, BCA, Mandiri, BRI, LinkAja) dengan mekanisme:
1. **Identifikasi Header:** Membaca payload string QRIS statis diawali `000201`.
2. **Peralihan Tag 01:** Mengubah nilai Tag 01 dari `010211` (*Static Initiation Point*) menjadi `010212` (*Dynamic Initiation Point*).
3. **Penyisipan Tag 54 (Nominal Transaksi):** Menyusun Tag 54 dengan format `54` + `[panjang 2 digit]` + `[nominal rupiah bulat tanpa desimal]`, disisipkan sebelum kode negara `5802ID`.
4. **Kalkulasi Ulang Checksum CRC-16/CCITT:** Menghitung kembali checksum 16-bit dengan polinomial `0x1021` dan nilai awal `0xFFFF` pada Tag 63 (`6304[CRC]`).
5. **Fallback Offline Cerdas:** Jika string QRIS di menu pengaturan toko belum diisi, sistem otomatis menggunakan payload QRIS nasional standar agar rendering kode QR di layar kasir tetap berjalan sempurna.

### 4.3. Manajemen Produk, Stok & Koreksi Opname (`inventory.php`)
* **Katalog Produk Ber-SKU:** Pengelolaan identitas barang, barcode, satuan (pcs, renceng, karton, sak, kg), harga modal (HPP), harga eceran, dan saldo stok fisik.
* **Indikator Tier Grosir:** Pemasangan aturan diskon grosir per produk (misal: pembelian ≥ 5 pcs diskon Rp2.000/pcs) yang langsung terhubung ke mesin POS.
* **Koreksi Stok Opname (*Stock Adjustment*):** Pencatatan mutasi barang masuk (*restock*), penyesuaian fisik (*opname*), dan barang rusak dengan audit trail di tabel `stock_movements`.
* **Notifikasi Stok Menipis (*Low Stock Alert*):** Badge otomatis jika stok barang ≤ batas minimum stok (`min_stock`).

### 4.4. Buku Hutang Piutang & Pembayaran Cicilan (`receivables.php`)
* **Pelacakan Saldo Kasbon:** Monitoring seluruh transaksi tempo pelanggan yang belum lunas (`status = 'belum_lunas'`).
* **Monitoring Plafon Kredit (*Credit Limit*):** Menampilkan sisa limit belanja pelanggan serta penanda visual toko yang melewati batas kredit.
* **Pencatatan Pembayaran Fleksibel:** Mendukung pembayaran sebagian (cicilan bertahap) maupun pelunasan penuh melalui metode **Tunai** dan **QRIS Dinamis**.
* **Nomor Kuitansi Unik:** Bukti bayar otomatis berformat `PAY-YYYYMMDD-XXXX` yang tersimpan di tabel `receivable_payments`.

### 4.5. Riwayat Transaksi & Pembatalan Void (`/transactions`)
* **Audit Trail Transaksi Kasir:** Pencarian dan filter berdasarkan nomor nota, rentang tanggal, status (*Lunas*, *Belum Lunas*, *Void*), dan metode pembayaran.
* **Cetak Ulang Struk Thermal (*Reprint*):** Modal struk kasir yang dapat langsung dicetak ke printer thermal 58mm/80mm atau disimpan sebagai PDF.
* **Mekanisme Pembatalan Nota (Void Eksklusif Owner):**
  * Otorisasi mutlak milik **Owner**. Tombol void dihilangkan dari antarmuka kasir dan endpoint diproteksi middleware `role:owner`.
  * Mewajibkan pengisian alasan pembatalan minimal 5 karakter.
  * Status transaksi diubah menjadi `void` (tidak dihapus fisik).
  * Seluruh kuantitas barang yang terjual **otomatis dikembalikan (*restock*) ke saldo gudang**.
  * Tagihan piutang terkait (jika tempo) otomatis dibatalkan (kecuali jika sudah ada cicilan yang masuk).

### 4.6. Laporan Keuangan & Analisis Laba Kotor (`reports.php`)
* **Filter Periode Terstruktur:** Pilihan rentang cepat *Harian*, *Mingguan*, *Bulanan*, serta kalender tanggal kustom.
* **Bento KPI Keuangan:** Omzet Kotor, Total Diskon Grosir, Omzet Bersih, Total HPP Modal, Estimasi Laba Kotor, dan Persentase Margin Laba.
* **Breakdown Metode Pembayaran:** Distribusi perolehan kas dari transaksi Tunai, QRIS, dan Piutang.
* **10 Produk Terlaris (*Top Selling*):** Ranking barang paling laku beserta kuantitas dan kontribusi omzet.
* **Ekspor Data:** Fitur unduh laporan lengkap dalam format **Excel / CSV** dan cetak format cetak PDF.

### 4.7. Portal Pelanggan Grosir (`portal.php`)
* Area khusus bagi pengguna dengan role **Pembeli** untuk memeriksa sisa plafon kredit tempo, daftar nota kasbon yang belum dibayar beserta jatuh temponya, riwayat belanja masa lalu, dan katalog harga grosir khusus.

---

## 5. Arsitektur Database MySQL (`db_manxmarket`)

Aplikasi berjalan di atas basis data relasional **MySQL (Laragon)** dengan storage engine **InnoDB** penuh untuk mendukung integritas relasional dan penguncian transaksi.

```
+---------------------------------------------------------------------------------+
|                                 DATABASE SCHEMA                                 |
+---------------------------------------------------------------------------------+

  [ categories ] 1 --- * [ products ] 1 --- * [ wholesale_rules ]
                                |
                                | 1
                                *
                      [ transaction_items ] * --- 1 [ transactions ]
                                                         | 1
                                                         |
                                                         | 1
                                                    [ receivables ] 1 --- * [ receivable_payments ]
                                                         |
                                                         *
  [ users ] 1 -------------------------------------------+ (customer_id / cashier_id / user_id)
      |
      +--- 1 --- * [ stock_movements ]
```

### Rincian Tabel Utama:
1. **`users`:** Menyimpan akun dengan atribut `id`, `name`, `username`, `password` (bcrypt), `role` (`ENUM('owner','kasir','gudang','pembeli')`), `phone`, `credit_limit`, `is_active`.
2. **`categories`:** Kategori produk (`id`, `name`, `slug`, `description`).
3. **`products`:** Katalog SKU barang (`id`, `category_id`, `sku`, `barcode`, `name`, `unit`, `cost_price`, `retail_price`, `stock`, `min_stock`, `is_active`).
4. **`wholesale_rules`:** Skema harga grosir (`id`, `product_id`, `rule_type`, `min_qty`, `min_amount`, `discount_type`, `discount_value`, `is_active`).
5. **`transactions`:** Nota penjualan kasir (`id`, `invoice_number`, `cashier_id`, `customer_id`, `customer_name_manual`, `transaction_date`, `subtotal`, `total_discount`, `grand_total`, `paid_amount`, `change_amount`, `payment_method`, `payment_status`, `void_by`, `void_reason`, `void_at`).
6. **`transaction_items`:** Item transaksi (`id`, `transaction_id`, `product_id`, `qty`, `cost_price_snapshot`, `unit_price`, `discount_per_item`, `subtotal_item`).
7. **`receivables`:** Buku piutang tempo (`id`, `transaction_id`, `customer_id`, `total_debt`, `total_paid`, `remaining_debt`, `due_date`, `status`).
8. **`receivable_payments`:** Kuitansi pembayaran cicilan (`id`, `receivable_id`, `payment_number`, `received_by`, `payment_date`, `amount_paid`, `payment_method`, `reference_note`, `notes`).
9. **`stock_movements`:** Buku mutasi fisik (`id`, `product_id`, `user_id`, `reference_id`, `movement_type`, `qty_change`, `stock_before`, `stock_after`, `notes`, `created_at`).
10. **`store_settings`:** Konfigurasi toko (`setting_key`, `setting_value`), meliputi identitas toko, nomor kontak, footer struk thermal, string QRIS statis, dan nama merchant ASPI.

---

## 6. Desain Visual & Standar Antarmuka (Warm Modern POS)

* **Palet Warna Utama:**
  * **Brand Primary:** Warm Crimson Red (`#b52330`)
  * **Brand Container / Accent:** `#ff5a5f`
  * **Primary Fixed Tint:** `#ffebee` (latar tab aktif)
  * **Status Lunas / Kas:** Emerald Green (`#047857` / `#10b981`)
  * **Status Tempo / Perhatian:** Amber Gold (`#b45309` / `#f59e0b`)
  * **Status Void / Bahaya:** Rose Red (`#b91c1c` / `#ef4444`)
  * **Warna Latar Belakang:** Slate Canvas (`#f8fafc`) & Card White (`#ffffff`)
* **Tipografi:** *Plus Jakarta Sans* (antarmuka umum) dan *JetBrains Mono* (angka nominal uang, SKU, barcode, dan kode invoice).
* **Ikonografi:** *Material Symbols Outlined* dari Google Fonts.
* **Kompatibilitas Layar Cetak:** Pengaturan CSS `@media print` otomatis menyembunyikan navigasi (*no-print*) dan mengoptimalkan kontras hitam-putih untuk printer struk thermal 58mm/80mm.

---

## 7. Status Pengujian & Verifikasi Akhir Sistem

Dokumentasi ini mencerminkan sistem yang telah lolos seluruh tahapan pengujian fungsional dan verifikasi browser:
* **Pengujian Role Isolation:** Owner, Kasir, Gudang, dan Pembeli hanya dapat mengakses halamannya masing-masing.
* **Pengujian Delegasi Role:** Kasir berhasil membuat akun kasir baru dan pembeli grosir, serta ditolak (403) saat mencoba membuat role Owner.
* **Pengujian Transaksi POS:** Berhasil memproses pembayaran Tunai, QRIS Dinamis (nominal terkunci otomatis), dan Hutang Tempo.
* **Pengujian Cetak Struk:** Struk thermal ter-render presisi dengan rincian diskon grosir, nomor faktur urut, dan status lunas.
* **Pengujian Stabilitas Antarmuka:** 0 error konsol JavaScript pada seluruh 9 rute aplikasi.