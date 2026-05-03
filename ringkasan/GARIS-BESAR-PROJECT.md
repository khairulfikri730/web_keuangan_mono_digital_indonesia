# KasirPro — SaaS POS + Manajemen Keuangan

> **Stack:** Laravel 12 · PHP ^8.2 · SQLite · Tailwind CSS (CDN) · Alpine.js · Font Awesome 6  
> **Tema:** Dark Mode (`body #0f172a`, sidebar `#0B1120`)  
> **Lisensi:** Proprietary / Internal

---

## 1. Ikhtisar Sistem

KasirPro adalah aplikasi **Point of Sale (POS) + manajemen keuangan berbasis web** yang dibangun dengan Laravel 12. Sistem ini mencakup:

- **POS Kasir** dengan tampilan grid produk & checkout cepat
- **Manajemen Inventori** multi-jenis produk (6 kind: reguler, timbangan, unlimited, jasa, bundle, formula)
- **Sistem Shift** — pembukaan/penutupan sesi kasir dengan rekonsiliasi kas
- **Arus Kas (Cashflow)** — pemasukan & pengeluaran dengan filter periode & ekspor CSV
- **Laporan & Analitik** — penjualan, laba rugi, analisa shift
- **Manajemen Tim** — CRUD user dengan 2 role: `owner` & `operator`

---

## 2. Arsitektur

```
app/
├── Models/          10 model Eloquent
├── Http/
│   ├── Controllers/ 14 controller (200+ metode)
│   └── Middleware/
│       └── RoleMiddleware.php  (otentikasi per-role)
├── Events/
│   └── TransactionCreated.php  (event — handler via listener)
└── Providers/
    └── AppServiceProvider.php  (kosong)

database/
├── migrations/      15 file migrasi
└── seeders/
    └── DatabaseSeeder.php  (2 user, 5 kategori, 9 produk, 7 setting)

routes/
└── web.php          55+ rute (guest, auth, role:owner)

resources/views/     25 file blade (dark UI)
```

---

## 3. Model Database

### 3.1 Tabel Utama (10 tabel)

| Tabel | Kolom Kunci | Relasi |
|-------|-------------|--------|
| **users** | `name`, `email`, `phone`, `role` (`owner`/`operator`), `is_active`, `password` | 1:M ke shifts, transactions, cashflows, stock_mutations |
| **shifts** | `opened_by` FK, `closed_by` FK, `opening_cash`, `closing_cash`, `total_sales`, `total_transactions`, `status` (`open`/`closed`), `notes`, `opened_at`, `closed_at` | 1:M ke transactions, cashflows |
| **categories** | `name`, `slug` (unique), `color`, `description`, `is_active` | 1:M ke products |
| **products** | `category_id` FK, `name`, `sku`, `barcode`, `price`, `cost_price`, `stock`, `min_stock`, `unit`, `image`, `is_active`, `product_type` (finished/semi/raw), `product_kind` (regular/weight/unlimited/service/bundle/formula), `meta` JSON | 1:M ke transaction_items, stock_mutations; M:M ke pos_groups |
| **stock_mutations** | `product_id` FK, `user_id` FK, `type` (in/out/adjustment), `quantity`, `stock_before`, `stock_after`, `reference`, `notes` | Log historis stok (double-entry) |
| **transactions** | `invoice_number` (uniq, format `INV-YYYYmmdd-XXXX`), `shift_id` FK, `user_id` FK, `subtotal`, `discount`, `tax`, `total`, `paid_amount`, `change_amount`, `payment_method` (cash/transfer/qris/debit), `status` (completed/cancelled/refunded), `customer_name`, `customer_phone`, `discount_type` (nominal/percentage), `notes` | 1:M ke transaction_items |
| **transaction_items** | `transaction_id` FK, `product_id` FK (nullable, SET NULL on delete), `product_name` (snapshot), `price`, `cost_price`, `quantity`, `discount`, `subtotal` | Item dalam transaksi |
| **cashflows** | `user_id` FK, `shift_id` FK, `type` (income/expense), `category`, `description`, `amount`, `source` (manual/pos_cash/pos_bank/transfer/pos), `reference`, `reference_id` FK, `transaction_date` | Pemasukan & pengeluaran |
| **settings** | `key` (uniq), `value` | Key-value store (Setting::get('key')) |
| **pos_groups** | `name`, `color`, `position` | M:M ke products via `pos_group_product` (pivot: `position`) |

### 3.2 Foreign Key Strategy

| Relasi | On Delete |
|--------|-----------|
| `transaction_items.transaction_id` | **CASCADE** |
| `transaction_items.product_id` | **SET NULL** — transaksi tetap utuh meski produk dihapus |
| `stock_mutations.product_id` | **CASCADE** |
| `shifts.opened_by` / `closed_by` | **RESTRICT** |
| `transactions.shift_id` / `user_id` | **RESTRICT** |
| `products.category_id` | **SET NULL** |
| `cashflows.reference_id` | **CASCADE** |

---

## 4. Role & Akses

### 4.1 Operator (`role: operator`)
| Modul | Akses |
|-------|-------|
| Dashboard | ✅ |
| POS Kasir | ✅ (butuh shift aktif) |
| Daftar Transaksi | ✅ |
| **Seluruh menu Manajemen, Keuangan, Laporan, Tim, Pengaturan** | ❌ |

### 4.2 Owner (`role: owner`)
| Semua menu | ✅ |
|-------------|-----|
| Sesi Shift | Buka/tutup, lihat detail |
| Katalog Produk | CRUD + ekspor |
| Kategori | CRUD (hapus diblokir jika masih ada produk) |
| Gudang Stok | Log mutasi + penyesuaian stok |
| Arus Kas | CRUD + ekspor CSV + filter periode |
| Analisa Penjualan | Grafik & metrik |
| Laporan Laba Rugi | Bulanan/tahunan |
| Laporan Shift | Ringkasan + discrepancy |
| Batalkan Transaksi | Kembalikan stok + catat refund |
| Manajemen Tim | CRUD user + toggle aktif |
| Pengaturan Toko | Nama, alamat, pajak, logo, footer, metode pembayaran |

---

## 5. Flow Bisnis Utama

### 5.1 POS Checkout
```
Operator buka POS
  → Pilih produk dari grid (filter kategori / search)
    → Tambah ke keranjang (qty, diskon)
      → Pilih metode pembayaran
        → Checkout:
          ① Lock produk (database transaction)
          ② Kurangi stok → buat StockMutation (type: out)
          ③ Buat Transaction + TransactionItem
          ④ Dispatch event TransactionCreated
          ⑤ Tampilkan struk (receipt)
```

### 5.2 Shift Management
```
Owner buka Shift → input opening_cash
  Operator bertransaksi selama shift terbuka
Owner tutup Shift → input closing_cash
  → Hitung total_sales & total_transactions
  → Status: closed, catat discrepancy
```

### 5.3 Pembatalan Transaksi
```
Owner batalkan transaksi (status=completed)
  → Kembalikan stok (increment)
  → Buat StockMutation (type: in)
  → Status transaksi → cancelled
  → Buat Cashflow pengembalian (type: expense)
```

### 5.4 Stok Masuk / Penyesuaian
```
Owner → Gudang Stok → Form adjustment
  → Pilih produk, type (in/out/adjustment), quantity, notes
  → Update stok, buat StockMutation
```

---

## 6. Jenis Produk (Product Kind)

| Kind | Deskripsi | Stok | Harga | Meta JSON |
|------|-----------|------|-------|-----------|
| **regular** | Produk standar | ✅ | Price + Cost | variants, discounts, packaging |
| **weight** | Timbangan (kg/g) | ✅ | Price + Cost | variants |
| **unlimited** | Tak terbatas (digital) | ❌ | Price | variants |
| **service** | Jasa | ❌ | Price | variants |
| **bundle** | Paket gabungan | ❌ (dihitung dari komponen) | Price | bundle_items (array produk+quantity) |
| **formula** | Produk rakitan | ❌ (bahan baku dikurangi) | Dihitung dari komponen cost | formula_components (array + cost breakdown) |

### 6.1 Tipe Produk
| Type | Deskripsi |
|------|-----------|
| `finished` | Produk jadi (siap jual) |
| `semi_finished` | Setengah jadi |
| `raw_material` | Bahan baku |

---

## 7. Rute API / JSON

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/pos/products` | GET | Search produk untuk POS (filter category, search name/barcode/sku) |
| `/pos/checkout` | POST | Checkout transaksi POS |
| `/pos/groups` | POST/PUT/DELETE | CRUD grup POS |
| `/pos/groups/{id}/sync` | POST | Sinkronisasi produk dalam grup |
| `/pos/groups/sync-all` | POST | Sinkronisasi massal semua grup |
| `/cashflow/data` | GET | Data arus kas AJAX (summary, chart, table partial) |
| `/cashflow/export` | GET | Ekspor CSV arus kas |

---

## 8. Tampilan (Views)

```
resources/views/
├── layouts/
│   └── app.blade.php          → Layout utama dark theme
├── auth/login.blade.php       → Halaman login
├── dashboard.blade.php         → Dashboard overview (statistik hari ini)
├── pos/
│   ├── index.blade.php         → Layar POS kasir (grid produk, keranjang, multi-worksheet, drag-drop editor)
│   ├── _product_card.blade.php → Partial kartu produk (dengan visual UNLIMITED badge)
│   └── receipt.blade.php       → Struk cetak
├── products/
│   ├── index.blade.php         → Katalog produk (tab finished/semi/raw)
│   ├── create.blade.php        → Form tambah (kompleks, per-kind fields, 6 jenis)
│   └── edit.blade.php          → Form edit (+ selector Jenis Produk 6 pilihan)
├── categories/index.blade.php  → Daftar kategori (modal CRUD)
├── stock/index.blade.php       → Log mutasi + form penyesuaian
├── transactions/
│   ├── index.blade.php         → Daftar transaksi + filter
│   └── show.blade.php          → Detail transaksi
├── cashflow/
│   ├── index.blade.php         → Dashboard arus kas + chart
│   └── _transactions.blade.php → Partial tabel (AJAX)
├── sales/index.blade.php       → Analisa penjualan (5 metrik kunci)
├── reports/
│   ├── sales.blade.php         → Laporan penjualan + chart
│   ├── financial.blade.php     → Laporan laba rugi
│   └── shifts.blade.php        → Laporan shift + summary cards
├── shifts/
│   ├── index.blade.php         → Daftar shift (buka/tutup)
│   └── show.blade.php          → Detail shift + transaksi
├── team/index.blade.php        → Manajemen tim (CRUD modal)
├── settings/index.blade.php    → Pengaturan toko
└── welcome.blade.php           → Default Laravel
```

---

## 9. Data Seeder

| Entitas | Data |
|---------|------|
| **User** | `owner@kasirpro.com` (owner) / `operator@kasirpro.com` (operator) — password: `password` |
| **Kategori** | Makanan (#f97316), Minuman (#06b6d4), Snack (#8b5cf6), Sembako (#10b981), Elektronik (#3b82f6) |
| **Produk** | 9 produk: Nasi Goreng, Mie Ayam, Es Teh, Jus Alpukat, Air Mineral, Keripik Singkong, Choco Wafer, Beras Premium 5kg, Minyak Goreng 1L |
| **Setting** | store_name, store_address, store_phone, store_email, store_footer, currency (IDR), tax_rate (0) |

---

## 10. Event & Listener

| Event | Dipicu Oleh | Listener | Status |
|-------|------------|----------|--------|
| `TransactionCreated` | `PosController@checkout` | — | **Belum ada listener** (placeholder untuk auto-cashflow) |

---

## 11. Middleware Kustom

### RoleMiddleware (`app/Http/Middleware/RoleMiddleware.php`)
- Alias: `role` (terdaftar di `bootstrap/app.php`)
- Mengecek apakah role user termasuk dalam daftar yang diizinkan
- Jika user tidak aktif (is_active=false), otomatis logout

---

## 12. Dependensi

### Composer (Production)
- `laravel/framework: ^12.0`
- `laravel/tinker: ^2.10.1`

### Composer (Dev)
- `fakerphp/faker`, `laravel/pint`, `laravel/sail`, `phpunit/phpunit`, `laravel/pail`, `mockery/mockery`, `nunomaduro/collision`

### NPM (Dev)
- `tailwindcss: ^4.0.0` (via Vite)
- `vite: ^7.0.7`
- `axios: ^1.11.0`
- `@tailwindcss/vite: ^4.0.0`
- `laravel-vite-plugin: ^2.0.0`

### CDN (frontend)
- **Tailwind CSS CDN** (dengan kustomisasi warna `sidebar: #0B1120`)
- **Alpine.js 3.x** (CDN)
- **Font Awesome 6.5** (CDN)
- **Inter font** (Google Fonts)

---

## 13. Fitur yang Belum Diimplementasikan

| Fitur | Status |
|-------|--------|
| Ekspor Produk (PDF/Excel) | Placeholder — "sedang dalam pengembangan" |
| Ekspor Penjualan/Transaksi | Belum ada |
| Listener `TransactionCreated` untuk auto-cashflow | Event sudah dibuat, listener belum |
| Notifikasi stok menipis | Belum ada |
| Multi-toko / multi-cabang | Belum ada |
| Integrasi payment gateway | Belum ada |
| Queue/Job processing | Konfigurasi ada (database driver), tapi belum digunakan |
| Print struk via printer thermal | Hanya tampilan HTML |

---

## 17a. Fitur POS Kasir (Detail)

### Multi-Worksheet
- POS mendukung **banyak worksheet sekaligus** (tab paralel)
- Setiap worksheet menyimpan cart, pelanggan, catatan, dan diskon secara independen
- Operator bisa berpindah antar worksheet tanpa kehilangan data keranjang

### Drag-and-Drop Layout Editor
- Tombol **"Groupkan Item"** di header POS membuka modal editor layout
- Produk dapat diseret antar grup (drag & drop HTML5 native)
- Grup memiliki nama + warna kustom
- Menyimpan layout via `POST /pos/groups/sync-all` → memperbarui `pos_groups` & pivot `pos_group_product`

### Visual Kartu Produk (POS Grid)
| Elemen | Produk Biasa | Produk Unlimited |
|---|---|---|
| Background | Putih | Gradient ungu `#f5f3ff → #ede9fe` |
| Border | Warna kategori / abu | Indigo `#6366f1` |
| Placeholder gambar | Warna kategori + inisial | Gradient indigo-purple + ikon ∞ |
| Badge stok (kanan atas) | `Stok: N` (hijau/merah) | Ikon ∞ indigo |
| Badge jenis (kiri atas) | — | **∞ UNLIMITED** (gradient indigo→ungu) |
| Teks harga | Hijau emerald | Ungu indigo |
| Efek hover | Glow kategori | Shimmer kilap |

### Logika Stok Unlimited di Cart
- `product.is_stockless` di-cast `!!` (boolean) untuk menangani integer `1` dari JSON
- Produk `unlimited` / `service` **tidak pernah diblokir** oleh pengecekan stok
- Stok fisik **tidak berkurang** saat checkout untuk produk unlimited

---

## 14. Cara Menjalankan

```bash
# 1. Clone & install
git clone <repo>
composer install
cp .env.example .env

# 2. Generate key & migrasi
php artisan key:generate
php artisan migrate --seed

# 3. Link storage (untuk gambar produk)
php artisan storage:link

# 4. Jalankan
php artisan serve

# 5. Login
# Owner  : owner@kasirpro.com / password
# Operator: operator@kasirpro.com / password
```

---

## 15. Default `.env`

| Key | Value | Keterangan |
|-----|-------|------------|
| `DB_CONNECTION` | `sqlite` | Database file-based |
| `SESSION_DRIVER` | `database` | Session disimpan di DB |
| `CACHE_STORE` | `database` | Cache di DB |
| `QUEUE_CONNECTION` | `database` | Queue di DB |
| `MAIL_MAILER` | `log` | Email hanya dicatat |
| `FILESYSTEM_DISK` | `local` | Upload gambar ke storage lokal |

---

## 16. Skema UI

```
┌─────────────────────────────────────────────────────────┐
│ ┌──────────┐ ┌───────────────────────────────────────┐  │
│ │  SIDEBAR │ │  TOPBAR          Jam Digital           │  │
│ │  #0B1120 │ ├───────────────────────────────────────┤  │
│ │          │ │  Alert (success/error)                │  │
│ │ Logo     │ ├───────────────────────────────────────┤  │
│ │ ────     │ │                                       │  │
│ │ Dashboard│ │  Content Area                         │  │
│ │ ────     │ │  (dark theme #0f172a)                 │  │
│ │ Transaksi│ │                                       │  │
│ │  POS     │ │                                       │  │
│ │  Daftar  │ │                                       │  │
│ │ ────     │ │                                       │  │
│ │ Manajemen│ │                                       │  │
│ │  Shift   │ │                                       │  │
│ │  Produk  │ │                                       │  │
│ │  Kategori│ │                                       │  │
│ │  Stok    │ │                                       │  │
│ │ ────     │ │                                       │  │
│ │ Keuangan │ │                                       │  │
│ │  Arus Kas│ │                                       │  │
│ │  Analisa │ │                                       │  │
│ │  LabaRugi│ │                                       │  │
│ │  LapShift│ │                                       │  │
│ │ ────     │ │                                       │  │
│ │ Lainnya  │ │                                       │  │
│ │  Tim     │ │                                       │  │
│ │  Setting │ │                                       │  │
│ │ ────     │ │                                       │  │
│ │ User info│ │                                       │  │
│ │ Logout   │ │                                       │  │
│ └──────────┘ └───────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

---

## 17. Changelog / Perubahan Terbaru

| Tanggal | Perubahan |
|---------|-----------|
| 2026-05-02 | **Fix:** Produk `unlimited`/`service` tidak bisa ditambah ke cart (bug `is_stockless` tidak dikonversi ke boolean). Diperbaiki dengan `!!product.is_stockless` |
| 2026-05-02 | **Feature:** Selector **Jenis Produk** (Biasa/Timbangan/Unlimited/Jasa/Bundle/Formula) ditambahkan ke halaman **Edit Produk** — sebelumnya hanya ada di halaman Tambah Produk |
| 2026-05-02 | **UI:** Kartu produk POS dirancang ulang — produk `unlimited` kini tampil dengan tema indigo/ungu berbeda dari produk biasa; badge gradient `∞ UNLIMITED`, shimmer hover, ikon ∞ pada placeholder |
| 2026-05-02 | **Feature:** `PosController@getProducts` mengembalikan `is_stockless` via `Product::$appends` sehingga frontend dapat membedakan produk unlimited |
| 2026-05-02 | `transaction_items.product_id` FK diubah dari `ON DELETE RESTRICT` → `ON DELETE SET NULL`. Produk yang sudah dipakai transaksi **tetap bisa dihapus**, riwayat transaksi utuh (pakai `product_name` snapshot) |
| 2026-05-01 | **Feature:** Drag-and-Drop Layout Editor POS — groupkan produk ke dalam grup berwarna via modal editor |
| 2026-05-01 | **Feature:** Multi-Worksheet POS — setiap tab worksheet punya cart independen |
| 2026-05-01 | Migrasi tambahan: `pos_groups` (grup produk POS), `source` + `reference_id` di `cashflows` |
| 2026-04-30 | Migrasi tambahan: `customer_phone`, `discount_type` di `transactions`; `product_type`, `product_kind`, `meta` di `products` |
| 2026-04-30 | Migrasi dasar: users, shifts, categories, products, stock_mutations, transactions, transaction_items, cashflows, settings |
