# MANUAL BOOK MANXMARKET

## Aplikasi Kasir dan Manajemen Grosir

| Informasi dokumen | Keterangan |
| --- | --- |
| Status | Draf untuk ditinjau |
| Nama aplikasi | MANXMARKET POS & Grosir System |
| Jenis aplikasi | Aplikasi web |
| Pengguna | Owner, kasir, petugas gudang, pembeli, administrator |
| Versi aplikasi | Belum dicantumkan pada source code yang diperiksa |

> **Catatan penyuntingan:** Ganti alamat server, nama toko, nama petugas, dan informasi lain yang masih umum setelah penerapan. Jangan memasukkan password, API key, atau rahasia konfigurasi ke dalam manual. Gambar pada dokumen ini masih berupa tempat untuk screenshot. Ikuti daftar pengambilan screenshot di bagian Daftar Gambar.

## PRAKATA

Manual book ini menjelaskan cara memasang, menjalankan, dan menggunakan MANXMARKET. Panduan disusun dari source code proyek, termasuk rute, controller, tampilan, konfigurasi, migration, seeder, dan README.

MANXMARKET membantu toko mencatat transaksi kasir, mengelola produk dan stok, mencatat pembayaran piutang, serta melihat laporan. Aplikasi menyediakan halaman berbeda sesuai peran akun. Pengguna hanya perlu mengikuti bagian yang sesuai dengan tugasnya.

Dokumen ini masih berupa draf. Administrator perlu menyesuaikan petunjuk instalasi dengan hosting yang dipakai dan mengambil screenshot dari versi aplikasi yang akan digunakan. Sebelum membuka aplikasi untuk pemakaian toko, uji alur transaksi dan pastikan pencadangan database dapat dipulihkan.

## DAFTAR ISI

- [Prakata](#prakata)
- [Daftar Isi](#daftar-isi)
- [Daftar Gambar](#daftar-gambar)
- [Bab 1 Pendahuluan](#bab-1-pendahuluan)
- [Bab 2 Instalasi dan Konfigurasi](#bab-2-instalasi-dan-konfigurasi)
- [Bab 3 Petunjuk Penggunaan Program](#bab-3-petunjuk-penggunaan-program)
- [Bab 4 Penutup](#bab-4-penutup)
- [Daftar Pustaka](#daftar-pustaka)
- [Lampiran A: Panduan Screenshot](#lampiran-a-panduan-screenshot)

> Nomor halaman belum dicantumkan karena dokumen masih berformat Markdown dan belum memiliki tata letak halaman tetap.

## DAFTAR GAMBAR

Daftar berikut adalah urutan gambar yang perlu dimasukkan setelah screenshot tersedia. Simpan berkas gambar di `docs/images/manual/` atau ubah jalur pada tautan gambar bila Anda memilih folder lain.

| Nomor | Judul gambar | Halaman/modul | Berkas yang disarankan |
| --- | --- | --- | --- |
| 1 | Halaman login MANXMARKET | Login | `01-login.png` |
| 2 | Halaman laporan dan analitik owner | Owner | `02-owner-laporan.png` |
| 3 | Halaman POS dan keranjang belanja | Kasir | `03-kasir-pos.png` |
| 4 | Pilihan pembayaran pada halaman POS | Kasir | `04-kasir-pembayaran.png` |
| 5 | Daftar dan detail transaksi | Owner/Kasir | `05-riwayat-transaksi.png` |
| 6 | Halaman buku piutang | Owner/Kasir | `06-buku-piutang.png` |
| 7 | Halaman inventaris dan katalog produk | Owner/Gudang | `07-inventaris-produk.png` |
| 8 | Form tambah atau edit produk | Owner/Gudang | `08-form-produk.png` |
| 9 | Form mutasi atau opname stok | Owner/Gudang | `09-penyesuaian-stok.png` |
| 10 | Halaman manajemen akun dan peran | Owner | `10-manajemen-akun.png` |
| 11 | Halaman pengaturan toko | Owner | `11-pengaturan-toko.png` |
| 12 | Portal pembeli | Pembeli | `12-portal-pembeli.png` |

### Cara menambahkan gambar

1. Ambil screenshot pada aplikasi yang sudah berjalan, bukan dari mockup.
2. Gunakan data contoh. Samarkan nama, nomor telepon, alamat, nomor nota, QRIS, dan informasi pelanggan yang sebenarnya.
3. Pangkas gambar agar hanya menampilkan halaman atau dialog yang sedang dijelaskan. Pastikan tulisan dan angka tetap terbaca.
4. Simpan gambar dengan nama pada tabel di atas ke `docs/images/manual/`.
5. Pada subbab terkait, ganti catatan tempat gambar dengan format Markdown berikut:

   ```md
   ![Halaman login MANXMARKET](images/manual/01-login.png)
   *Gambar 1. Halaman login MANXMARKET.*
   ```

6. Perbarui nomor dan judul pada Daftar Gambar bila urutannya berubah.

## BAB 1 PENDAHULUAN

### 1.1 Deskripsi program

MANXMARKET adalah aplikasi web untuk operasional kasir dan pengelolaan grosir. Aplikasi menyimpan data pengguna, produk, transaksi, stok, pembayaran piutang, dan pengaturan toko pada database. Setelah login, halaman awal mengikuti peran akun: owner diarahkan ke laporan, kasir ke POS, gudang ke inventaris, dan pembeli ke portal.

### 1.2 Tujuan dan manfaat

MANXMARKET dibuat untuk membantu pengguna:

- mencatat penjualan dan metode pembayaran melalui POS;
- mencari produk menggunakan nama, SKU, atau barcode;
- mengelola katalog, kategori, harga, gambar produk, dan persediaan;
- mencatat pembayaran tempo secara bertahap;
- melihat riwayat transaksi dan mutasi stok;
- memantau laporan penjualan dan mengunduh laporan CSV sebagai owner;
- membatasi akses menu berdasarkan peran akun.

### 1.3 Pengguna aplikasi

| Peran | Penggunaan utama | Akses yang tersedia |
| --- | --- | --- |
| Owner | Mengawasi kegiatan toko | Laporan, POS, transaksi, piutang, inventaris, akun, pengaturan, dan portal pembeli |
| Kasir | Melayani transaksi pelanggan | POS, transaksi, pembayaran piutang, dan pendaftaran pembeli dari POS |
| Gudang | Mengelola barang dan persediaan | Produk, kategori, mutasi stok, opname, dan riwayat stok |
| Pembeli | Melihat informasi akun belanja | Portal, saldo/tagihan, riwayat belanja, dan katalog |

### 1.4 Teknologi yang digunakan

| Komponen | Teknologi atau acuan source |
| --- | --- |
| Bahasa dan framework server | PHP 8.1 atau lebih baru sesuai batas `composer.json`, Laravel 10 |
| Database | MySQL 8.x sesuai README proyek |
| Tampilan | Blade, HTML, CSS, Tailwind CSS, JavaScript |
| Pengelolaan aset | Vite dan npm, untuk membangun ulang aset frontend |
| Autentikasi | Login berbasis sesi Laravel |
| Server lokal untuk pengembangan | Laragon atau `php artisan serve` |

### 1.5 Fitur-fitur yang tersedia

| Fitur | Keterangan |
| --- | --- |
| POS | Memilih produk, mengatur kuantitas, menghitung total, memilih pelanggan, dan menyimpan transaksi |
| Harga grosir | Menyediakan aturan harga atau potongan berdasarkan jumlah pembelian produk |
| Pembayaran | Mencatat pembayaran tunai, QRIS, atau tempo sesuai data transaksi |
| Inventaris | Menambah produk dan kategori, menyimpan gambar produk, mencari katalog, dan melihat stok |
| Mutasi stok | Mencatat stok masuk, koreksi stok, opname fisik, dan riwayat pergerakan |
| Transaksi | Mencari nota berdasarkan kata kunci, rentang tanggal, status, atau metode pembayaran; mencetak ulang struk |
| Void | Owner dapat membatalkan transaksi dengan alasan; catatan transaksi tetap tersimpan |
| Piutang | Melihat tagihan, sisa utang, jatuh tempo, cicilan, dan riwayat pembayaran |
| Laporan | Menampilkan ringkasan penjualan dan menyediakan ekspor CSV untuk owner |
| Manajemen pengguna | Owner dapat membuat akun dengan peran yang tersedia dan mengubah status aktif akun |
| Pengaturan toko | Menyimpan identitas toko, teks footer struk, dan data payload QRIS |
| Portal pembeli | Menampilkan informasi akun pembeli, tagihan, riwayat transaksi, dan katalog |

### 1.6 Ruang lingkup dan batasan aplikasi

MANXMARKET mencakup pencatatan transaksi kasir, data produk, stok, piutang, laporan, pengaturan toko, dan portal pembeli. Rute yang diperiksa belum menyediakan checkout mandiri oleh pembeli melalui portal. Pencatatan QRIS di aplikasi juga tidak menjadi konfirmasi bahwa dana sudah diterima. Kasir atau owner perlu memeriksa pembayaran melalui kanal merchant yang digunakan toko.

Aplikasi tidak menggantikan pencatatan akuntansi resmi atau prosedur operasional toko. Pengguna perlu memeriksa data penting, terutama jumlah uang, stok hasil opname, status pembayaran, dan alasan void. Fitur yang belum tersedia pada source tidak dibahas sebagai fungsi aplikasi.

## BAB 2 INSTALASI DAN KONFIGURASI

> Petunjuk ini menjadi acuan instalasi baru. Sesuaikan nama folder, database, domain, izin file, dan detail server dengan lingkungan yang dipakai. Jangan menjalankan migration atau seeder pada database toko yang sudah berisi data tanpa rencana dan backup.

### 2.1 Source code aplikasi

Source code proyek berada di folder instalasi MANXMARKET. Bagian yang perlu dikenal administrator:

| Lokasi | Kegunaan |
| --- | --- |
| `app/` | Model, controller, service, dan aturan aplikasi |
| `routes/web.php` | Rute halaman web dan endpoint aplikasi |
| `resources/views/` | Tampilan Blade |
| `resources/css/` dan `resources/js/` | Source aset frontend |
| `public/` | Aset yang disajikan web server |
| `database/migrations/` | Definisi skema database |
| `database/seeders/` | Seeder akun awal |
| `.env.example` | Contoh nama konfigurasi lingkungan |
| `composer.json` dan `composer.lock` | Dependensi PHP |
| `package.json` dan `package-lock.json` | Script dan dependensi build frontend |
| `README.md` | Ringkasan fitur dan acuan teknologi proyek |

Source tidak mencantumkan tautan repository publik. Administrator proyek perlu memberikan URL repository atau paket source resmi kepada petugas instalasi melalui kanal yang sesuai.

### 2.2 Persiapan instalasi

Siapkan hal-hal berikut sebelum memasang aplikasi:

1. Komputer atau hosting yang dapat menjalankan versi PHP sesuai `composer.json` dan mendukung ekstensi yang diperlukan Laravel serta MySQL.
2. Composer untuk memasang dependensi PHP.
3. MySQL 8.x atau versi yang disetujui administrator proyek.
4. Node.js dan npm bila aset frontend perlu dibangun ulang.
5. Akses terminal dan editor untuk mengatur `.env`.
6. Nama database, username, password database, URL aplikasi, dan domain yang telah disiapkan.
7. Akses backup bila aplikasi dipasang ulang atau dipindahkan dari server lama.

Salin `.env.example` menjadi `.env` hanya untuk instalasi baru. Jangan menimpa `.env` pada aplikasi yang sudah berjalan karena file itu berisi konfigurasi dan rahasia lingkungan.

### 2.3 Instalasi pada komputer lokal

Contoh ini menggunakan Windows dan Laragon. Pastikan folder proyek sudah tersedia pada komputer.

1. Jalankan Laragon, lalu hidupkan Apache dan MySQL.
2. Letakkan source code pada folder web Laragon, misalnya `D:\laragon\www\manxmarket`.
3. Buka terminal pada folder proyek.
4. Pasang dependensi PHP:

   ```powershell
   composer install
   ```

5. Untuk instalasi baru, salin file konfigurasi contoh:

   ```powershell
   Copy-Item .env.example .env
   ```

6. Buat database kosong melalui HeidiSQL atau alat administrasi MySQL.
7. Atur koneksi database pada bagian [Konfigurasi Database](#25-konfigurasi-database).
8. Isi `APP_URL` sesuai alamat lokal yang dipakai.
9. Buat kunci aplikasi dan jalankan migration:

   ```powershell
   php artisan key:generate
   php artisan migrate
   ```

10. Bila perlu membangun aset frontend, jalankan:

   ```powershell
   npm ci
   npm run build
   ```

11. Buat akun awal dengan mengikuti [Pembuatan akun](#26-pembuatan-akun-awal).
12. Jalankan aplikasi melalui virtual host Laragon yang document root-nya mengarah ke folder `public`, atau untuk pengembangan saja:

   ```powershell
   php artisan serve
   ```

13. Buka alamat lokal aplikasi di browser dan masuk menggunakan akun yang sudah disiapkan.

Jangan menjalankan `php artisan migrate:fresh` pada database yang berisi data. Perintah tersebut menghapus tabel lalu membuatnya kembali.

### 2.4 Konfigurasi aplikasi

Atur variabel `.env` berdasarkan lingkungan. Contoh berikut hanya menunjukkan nama variabel penting. Ganti seluruh nilai contoh dengan konfigurasi yang sesuai.

```dotenv
APP_NAME="MANXMARKET"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://manxmarket.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_manxmarket
DB_USERNAME=nama_pengguna_database
DB_PASSWORD=password_database
```

Untuk hosting produksi, gunakan `APP_ENV=production` dan `APP_DEBUG=false`. Buat `APP_KEY` menggunakan Artisan, bukan dengan menyalin kunci dari komputer lain. Jangan menaruh `.env` di folder publik atau mengunggahnya ke repository.

### 2.5 Konfigurasi database

Untuk instalasi baru, buat database kosong. Contoh perintah MySQL:

```sql
CREATE DATABASE db_manxmarket
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Jika database bernama sama sudah ada, jangan menimpa atau menghapus isinya. Periksa dulu dengan administrator.

1. Buat database kosong dengan encoding `utf8mb4`.
2. Buat user database khusus aplikasi dan berikan hak yang dibutuhkan aplikasi pada database tersebut.
3. Masukkan host, port, nama database, username, dan password ke `.env`.
4. Pastikan `DB_CONNECTION=mysql` karena `.env.example` proyek menggunakan SQLite sebagai nilai contoh awal, sementara README menetapkan MySQL 8.x.
5. Jalankan `php artisan migrate` pada database yang sudah dipastikan benar.
6. Jalankan `php artisan migrate:status` untuk melihat status migration.
7. Pastikan aplikasi dapat membuka halaman login dan membaca data database.

Jangan mengimpor `docs/schema.sql` ke database produksi tanpa meninjau isinya. File tersebut dapat berisi perintah penghapusan tabel.

### 2.6 Pembuatan akun awal

Seeder proyek membuat akun dengan username `owner`, `kasir`, `gudang`, dan `pembeli`. Seeder mengambil password awal dari variabel `SEED_DEFAULT_PASSWORD`. Tetapkan nilai sementara yang kuat di `.env` sebelum menjalankan:

```powershell
php artisan db:seed
```

Setiap akun contoh yang dibuat oleh seeder memakai password awal yang sama. Sampaikan kredensial secara privat kepada pemilik akun, lalu ganti atau amankan password awal sebelum penggunaan operasional. Jangan menuliskan password tersebut di manual ini. Jika akun sudah dikelola oleh owner, owner dapat membuat akun pengguna melalui menu manajemen akun.

### 2.7 Deployment ke hosting

Langkah rinci dapat berbeda menurut panel dan layanan hosting. Minta penyedia hosting memastikan versi PHP, ekstensi PHP, Composer, MySQL, izin direktori, SSL, dan proses menjalankan perintah Artisan sesuai kebutuhan aplikasi.

1. Siapkan domain, sertifikat HTTPS, database, dan user database pada hosting.
2. Unggah source code ke direktori aplikasi di luar document root publik bila struktur hosting memungkinkan.
3. Arahkan document root domain ke folder `public` proyek. Jangan menjadikan root source code sebagai folder publik.
4. Atur file `.env` produksi dengan kredensial database yang khusus untuk aplikasi, `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` HTTPS yang benar.
5. Pasang dependensi produksi menggunakan Composer sesuai kebijakan hosting, misalnya `composer install --no-dev --optimize-autoloader`.
6. Bangun aset frontend pada lingkungan build tepercaya dengan `npm ci` dan `npm run build`, lalu unggah hasil build yang diperlukan.
7. Buat `APP_KEY` sekali untuk instalasi baru, kemudian jalankan `php artisan migrate --force` setelah memastikan database target dan backup.
8. Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis oleh proses PHP. Jangan memberi izin tulis yang lebih luas dari kebutuhan.
9. Aktifkan HTTPS, uji login dan alur utama dengan akun percobaan, lalu periksa log jika ada kegagalan.
10. Siapkan backup database terjadwal dan uji pemulihan pada database uji.

Jangan menjalankan `php artisan serve` sebagai web server hosting. Source proyek yang diperiksa tidak menyertakan URL staging atau panduan deployment khusus provider. Administrator perlu memvalidasi langkah ini pada hosting tujuan sebelum memindahkan data operasional.

### 2.8 Menjalankan aplikasi

Pada komputer lokal, hidupkan web server dan MySQL melalui Laragon. Buka domain lokal yang telah diarahkan ke folder `public`. Untuk uji pengembangan, jalankan `php artisan serve` dari folder proyek dan akses alamat yang ditampilkan pada terminal.

Pada hosting, buka domain HTTPS yang diberikan administrator. Setelah berhasil login, aplikasi akan mengarahkan pengguna ke halaman awal sesuai peran akun.

## BAB 3 PETUNJUK PENGGUNAAN PROGRAM

### 3.1 Halaman login

1. Buka alamat aplikasi yang diberikan administrator.
2. Masukkan username dan password akun.
3. Tekan **Masuk ke Sistem**.
4. Jika data salah atau akun tidak aktif, baca pesan yang muncul dan hubungi owner untuk memeriksa akun.
5. Gunakan tombol ikon mata untuk melihat atau menyembunyikan password saat mengetik. Tombol tema pada halaman login atau tombol `F9` dapat mengganti mode terang dan gelap.

![Tempat screenshot halaman login](images/manual/01-login.png)
*Gambar 1. Halaman login MANXMARKET. Tambahkan screenshot aplikasi yang sudah berjalan.*

Setelah login, menu yang terlihat bergantung pada peran. Gunakan menu **Keluar** setelah selesai, terutama pada komputer yang dipakai bersama.

### 3.2 Petunjuk untuk owner

Owner memiliki akses ke seluruh menu operasional yang tersedia, termasuk pengaturan akun dan toko.

#### 3.2.1 Melihat laporan

1. Buka menu **Laporan & Analitik**.
2. Tinjau ringkasan yang tersedia pada halaman.
3. Atur rentang tanggal atau filter yang tersedia bila diperlukan.
4. Gunakan fungsi ekspor CSV bila perlu mengolah atau menyimpan laporan.
5. Periksa periode dan angka sebelum menggunakan hasil ekspor.

![Tempat screenshot laporan owner](images/manual/02-owner-laporan.png)
*Gambar 2. Halaman laporan owner. Samarkan nilai atau nama toko bila diperlukan.*

#### 3.2.2 Mengelola transaksi dan void

1. Buka menu **Transaksi**.
2. Cari nota menggunakan kolom pencarian atau filter tanggal, status, dan metode pembayaran.
3. Buka detail untuk memeriksa item dan ringkasan pembayaran.
4. Gunakan cetak ulang struk jika salinan nota dibutuhkan.
5. Untuk membatalkan transaksi, pilih tindakan void pada nota yang sesuai.
6. Masukkan alasan pembatalan dan periksa kembali nomor nota sebelum mengonfirmasi.

Void hanya tersedia untuk owner. Aplikasi menyimpan status, alasan, pelaku, dan waktu pembatalan sebagai riwayat transaksi.

#### 3.2.3 Mengelola piutang

1. Buka menu **Piutang** atau **Buku Piutang**.
2. Cari pelanggan atau tagihan yang akan diperiksa.
3. Periksa nilai tagihan, pembayaran yang sudah masuk, sisa utang, dan tanggal jatuh tempo.
4. Saat menerima pembayaran, pilih tagihan yang benar, masukkan jumlah cicilan dan metode pembayaran.
5. Periksa kembali data sebelum menyimpan.
6. Gunakan riwayat pembayaran untuk mencocokkan cicilan yang tercatat.

![Tempat screenshot buku piutang](images/manual/06-buku-piutang.png)
*Gambar 6. Halaman buku piutang dengan data pelanggan contoh.*

#### 3.2.4 Mengelola inventaris

Owner dapat menggunakan menu inventaris untuk membuat dan mengubah data produk, kategori, stok, serta melihat riwayat mutasi. Ikuti langkah rinci pada [bagian gudang](#34-petunjuk-untuk-gudang).

#### 3.2.5 Mengelola akun

1. Buka menu **Manajemen Akun** atau **Pengguna & Peran**.
2. Gunakan pencarian atau filter peran untuk menemukan akun.
3. Pilih fungsi tambah akun dan isi nama, username, password awal, peran, serta nomor telepon bila diperlukan.
4. Untuk akun pembeli, isi limit kredit sesuai persetujuan toko. Akun pembeli yang dibuat kasir dari POS mendapat limit awal Rp0.
5. Simpan akun dan sampaikan informasi login secara privat.
6. Gunakan tindakan status aktif/nonaktif saat akses akun perlu dihentikan atau dipulihkan.

Owner tidak dapat menonaktifkan akunnya sendiri. Pastikan setidaknya satu akun owner aktif.

![Tempat screenshot manajemen akun](images/manual/10-manajemen-akun.png)
*Gambar 10. Halaman manajemen akun. Samarkan data pengguna.*

#### 3.2.6 Mengatur informasi toko

1. Buka menu **Pengaturan**.
2. Isi identitas toko dan informasi yang ditampilkan pada struk.
3. Periksa payload QRIS sebelum menyimpan. Pastikan data tersebut berasal dari kanal merchant toko.
4. Simpan perubahan dan periksa kembali tampilan struk atau QR pada transaksi uji.

![Tempat screenshot pengaturan toko](images/manual/11-pengaturan-toko.png)
*Gambar 11. Halaman pengaturan toko. Samarkan alamat, nomor telepon, dan payload QRIS.*

### 3.3 Petunjuk untuk kasir

#### 3.3.1 Mencatat transaksi di POS

1. Buka menu **POS**.
2. Cari produk berdasarkan nama, SKU, atau barcode, atau pilih melalui kategori.
3. Tambahkan produk ke keranjang dan sesuaikan kuantitas.
4. Periksa nama item, harga, diskon, dan total pada keranjang.
5. Pilih pelanggan bila transaksi perlu dikaitkan dengan akun pembeli.
6. Pilih metode pembayaran: tunai, QRIS, atau tempo sesuai pilihan yang tersedia dan prosedur toko.
7. Untuk pembayaran tunai, masukkan jumlah yang diterima dan periksa nilai kembalian.
8. Untuk QRIS, tampilkan kode sesuai alur pada aplikasi dan verifikasi dana melalui aplikasi atau kanal merchant sebelum menyerahkan barang.
9. Untuk tempo, pastikan pelanggan yang dipilih benar dan ketentuan kredit disetujui owner.
10. Konfirmasi checkout setelah seluruh data benar. Cetak struk bila diperlukan.

![Tempat screenshot POS](images/manual/03-kasir-pos.png)
*Gambar 3. Halaman POS dengan contoh produk pada keranjang.*

![Tempat screenshot pembayaran](images/manual/04-kasir-pembayaran.png)
*Gambar 4. Pilihan pembayaran pada POS. Gunakan transaksi uji dan QRIS nonaktif atau data yang telah disamarkan.*

#### 3.3.2 Mencari transaksi dan mencetak ulang struk

1. Buka menu **Transaksi**.
2. Cari nomor nota atau nama pelanggan, atau gunakan filter tanggal, status, dan metode pembayaran.
3. Pilih transaksi untuk melihat detail.
4. Gunakan tombol cetak ulang pada transaksi yang tepat.
5. Cocokkan kembali isi struk sebelum memberikannya kepada pelanggan.

Kasir dapat melihat transaksi, tetapi tidak memiliki hak untuk membatalkan nota.

![Tempat screenshot riwayat transaksi](images/manual/05-riwayat-transaksi.png)
*Gambar 5. Daftar transaksi dengan informasi pelanggan yang sudah disamarkan.*

#### 3.3.3 Mencatat pembayaran piutang

1. Buka **Piutang**.
2. Pilih pelanggan dan tagihan yang akan dibayar.
3. Periksa jumlah sisa utang.
4. Catat nominal cicilan dan metode pembayaran yang diterima.
5. Simpan transaksi pembayaran, lalu periksa sisa tagihan dan riwayatnya.

#### 3.3.4 Mendaftarkan pembeli dari POS

1. Buka fungsi tambah atau daftar pelanggan pada halaman POS.
2. Masukkan nama, username, password awal, dan nomor telepon bila tersedia.
3. Simpan data. Sistem membuat akun dengan peran pembeli dan limit kredit awal Rp0.
4. Berikan informasi akun secara privat kepada pelanggan.

Kasir tidak menetapkan limit kredit. Minta owner mengatur kelayakan kredit melalui proses yang berlaku di toko.

### 3.4 Petunjuk untuk gudang

#### 3.4.1 Menambahkan kategori

1. Buka menu **Inventaris**.
2. Pilih fungsi tambah kategori.
3. Masukkan nama kategori dan deskripsi bila diperlukan.
4. Simpan, lalu pastikan kategori muncul pada daftar pilihan.

#### 3.4.2 Menambahkan produk

1. Pada halaman inventaris, pilih **Tambah Produk**.
2. Isi SKU, nama produk, harga jual, harga modal bila dicatat, satuan, dan kategori.
3. Isi barcode, stok awal, dan batas minimum stok bila diperlukan.
4. Jika menambahkan gambar, gunakan format JPG, JPEG, PNG, atau WebP dengan ukuran maksimal 2 MB.
5. Untuk produk grosir, aktifkan aturan grosir dan isi jumlah minimum serta jenis/nilai harga atau potongannya.
6. Periksa kembali data, lalu simpan.

Saat mengedit produk, ubah jumlah persediaan melalui fungsi mutasi atau opname, bukan dengan mengubah kolom stok pada form edit.

![Tempat screenshot form produk](images/manual/08-form-produk.png)
*Gambar 8. Form produk dengan data contoh.*

#### 3.4.3 Mencatat barang masuk atau koreksi stok

1. Buka fungsi **Mutasi Stok** atau **Penyesuaian Stok**.
2. Pilih produk yang benar.
3. Pilih jenis perubahan, masukkan jumlah, dan tulis catatan yang menjelaskan sumber atau alasan perubahan.
4. Periksa jumlah sebelum dan sesudah perubahan bila ditampilkan.
5. Simpan, lalu pastikan mutasi muncul pada riwayat produk.

#### 3.4.4 Melakukan opname fisik

1. Hitung persediaan produk secara fisik.
2. Buka fungsi opname atau penyesuaian fisik.
3. Pilih produk dan masukkan jumlah hasil hitung.
4. Isi alasan atau catatan, misalnya hasil hitung stok atau barang rusak.
5. Periksa produk dan jumlah, kemudian simpan.
6. Buka riwayat stok untuk memastikan selisih tercatat.

![Tempat screenshot inventaris](images/manual/07-inventaris-produk.png)
*Gambar 7. Halaman katalog dan inventaris produk.*

![Tempat screenshot opname stok](images/manual/09-penyesuaian-stok.png)
*Gambar 9. Form mutasi atau opname stok.*

### 3.5 Petunjuk untuk pembeli

1. Masuk menggunakan username dan password yang diberikan oleh toko.
2. Buka halaman **Portal** untuk melihat informasi akun dan limit kredit.
3. Periksa daftar tagihan, sisa pembayaran, dan tanggal jatuh tempo.
4. Lihat riwayat transaksi atau pembayaran untuk memeriksa catatan belanja.
5. Gunakan katalog untuk melihat produk yang tersedia.
6. Hubungi kasir atau owner bila ada ketidaksesuaian data atau pembayaran.

Portal yang diperiksa menyediakan informasi dan katalog. Pembeli tidak melakukan checkout sendiri dari portal.

![Tempat screenshot portal pembeli](images/manual/12-portal-pembeli.png)
*Gambar 12. Portal pembeli dengan data akun contoh yang sudah disamarkan.*

### 3.6 Keluar dari aplikasi

Pilih menu **Keluar** setelah selesai. Jika menggunakan komputer bersama, jangan menyimpan password di browser dan pastikan halaman login tampil sebelum meninggalkan perangkat.

## BAB 4 PENUTUP

### 4.1 Kesimpulan

MANXMARKET menyediakan fungsi pencatatan penjualan, pengelolaan inventaris, pemantauan piutang, laporan owner, pengaturan toko, dan portal pembeli. Pembagian peran membantu setiap pengguna membuka fungsi sesuai tugasnya. Manual ini menerangkan alur dasar berdasarkan source code yang diperiksa.

### 4.2 Saran

- Owner sebaiknya memeriksa transaksi, stok, dan piutang secara berkala.
- Kasir perlu mencocokkan jumlah pembayaran dan nomor nota sebelum menyelesaikan transaksi.
- Petugas gudang perlu memberi catatan yang jelas pada mutasi dan opname.
- Administrator perlu melakukan backup database terjadwal dan menguji proses pemulihan.
- Administrator perlu menyesuaikan bagian hosting dengan konfigurasi server yang benar-benar dipakai.
- Pengelola perlu memperbarui manual dan screenshot setiap kali menu atau proses aplikasi berubah.

## DAFTAR PUSTAKA

1. MANXMARKET. `README.md`. Ringkasan aplikasi, fitur, dan teknologi proyek.
2. MANXMARKET. `composer.json` dan `composer.lock`. Batas versi PHP dan dependensi Laravel.
3. MANXMARKET. `package.json` dan `package-lock.json`. Script serta dependensi aset frontend.
4. MANXMARKET. `routes/web.php`. Daftar halaman, endpoint, dan pembatasan rute peran.
5. MANXMARKET. `app/Http/Controllers/`. Implementasi proses autentikasi, POS, inventaris, portal, piutang, laporan, transaksi, pengguna, dan pengaturan.
6. MANXMARKET. `resources/views/`. Tampilan halaman aplikasi.
7. MANXMARKET. `database/migrations/` dan `database/seeders/DatabaseSeeder.php`. Skema database dan akun awal.
8. MANXMARKET. `.env.example`. Contoh variabel konfigurasi lingkungan.

## LAMPIRAN A: PANDUAN SCREENSHOT

Gunakan browser desktop dengan ukuran jendela yang sama untuk semua gambar agar hasilnya konsisten. Ambil screenshot hanya setelah masuk dengan akun uji. Jangan sertakan password, data pribadi pelanggan, nomor rekening, QRIS aktif, token, atau informasi rahasia lain.

| Berkas | Cara membuka | Yang perlu terlihat | Catatan keamanan |
| --- | --- | --- | --- |
| `01-login.png` | Keluar dari aplikasi atau buka `/login` | Logo, kolom username/password, tombol masuk | Jangan tampilkan password di kolom |
| `02-owner-laporan.png` | Login owner, buka laporan | Judul halaman, filter periode, ringkasan | Gunakan angka contoh |
| `03-kasir-pos.png` | Login kasir, buka POS | Pencarian produk, daftar produk, keranjang | Gunakan produk contoh |
| `04-kasir-pembayaran.png` | POS, lanjut ke pilihan pembayaran | Metode pembayaran dan ringkasan transaksi | Hindari QRIS merchant aktif |
| `05-riwayat-transaksi.png` | Buka menu transaksi | Pencarian/filter dan daftar nota | Samarkan nomor nota dan nama |
| `06-buku-piutang.png` | Buka menu piutang | Daftar tagihan, status, sisa utang | Gunakan akun pelanggan contoh |
| `07-inventaris-produk.png` | Login gudang atau owner, buka inventaris | Ringkasan stok, pencarian, daftar produk | Gunakan katalog contoh |
| `08-form-produk.png` | Buka form tambah/edit produk | SKU, nama, harga, gambar, aturan grosir | Jangan tampilkan data dagang sensitif |
| `09-penyesuaian-stok.png` | Buka form mutasi/opname | Produk, jumlah, kolom catatan | Gunakan data stok uji |
| `10-manajemen-akun.png` | Login owner, buka manajemen akun | Daftar akun dan pilihan peran | Tutup atau samarkan username dan telepon |
| `11-pengaturan-toko.png` | Login owner, buka pengaturan | Nama/alamat toko dan footer struk | Samarkan alamat, nomor telepon, payload QRIS |
| `12-portal-pembeli.png` | Login pembeli, buka portal | Ringkasan akun, tagihan, riwayat/katalog | Samarkan nama dan nilai pribadi |

Simpan screenshot ke `docs/images/manual/`, lalu tambahkan gambar pada subbab yang sesuai dengan sintaks Markdown pada bagian Daftar Gambar. Setelah screenshot masuk, periksa kembali keterbacaan teks dan pastikan informasi pribadi sudah disamarkan.
