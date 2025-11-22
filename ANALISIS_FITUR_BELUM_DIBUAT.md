# Analisis Fitur yang Belum Dibuat

## 📋 Ringkasan

Dokumen ini berisi analisis lengkap tentang fitur-fitur yang disebutkan dalam dokumentasi, migration, atau sidebar, tetapi belum sepenuhnya diimplementasikan dalam sistem.

---

## 🔴 Fitur Utama yang Belum Dibuat

### 1. **Manajemen Supplier (Pemasok)** ❌

**Status:** Belum dibuat sama sekali

**Yang Sudah Ada:**
- ✅ Migration: `2025_11_14_001339_create_suppliers_table.php`
- ✅ Seeder: `SupplierSeeder.php`
- ✅ Tabel `suppliers` di database dengan kolom:
  - `id`
  - `name`
  - `phone` (nullable)
  - `address` (nullable)
  - `timestamps`

**Yang Belum Ada:**
- ❌ Model: `app/Models/Supplier.php`
- ❌ Controller: `app/Http/Controllers/SupplierController.php`
- ❌ Service: `app/Services/SupplierService.php`
- ❌ View: `resources/views/features/suppliers/`
- ❌ Route untuk CRUD supplier
- ❌ Link di sidebar untuk "Kelola Supplier"

**Dampak:**
- Migration `inbound` memiliki foreign key ke `suppliers`, tetapi tidak ada cara untuk mengelola data supplier
- Inbound transaction seharusnya terkait dengan supplier, tetapi saat ini tidak digunakan

**Prioritas:** 🔴 **TINGGI** (Karena diperlukan untuk integrasi dengan Inbound)

---

### 2. **Manajemen Customer (Pelanggan)** ❌

**Status:** Belum dibuat sama sekali

**Yang Sudah Ada:**
- ✅ Migration: `2025_11_14_001342_create_customers_table.php`
- ✅ Seeder: `CustomerSeeder.php`
- ✅ Tabel `customers` di database dengan kolom:
  - `id`
  - `name`
  - `phone` (nullable)
  - `address` (nullable)
  - `timestamps`

**Yang Belum Ada:**
- ❌ Model: `app/Models/Customer.php`
- ❌ Controller: `app/Http/Controllers/CustomerController.php`
- ❌ Service: `app/Services/CustomerService.php`
- ❌ View: `resources/views/features/customers/`
- ❌ Route untuk CRUD customer
- ❌ Link di sidebar untuk "Kelola Customer"

**Dampak:**
- Migration `outbound` memiliki foreign key ke `customers`, tetapi tidak ada cara untuk mengelola data customer
- Outbound transaction seharusnya terkait dengan customer, tetapi saat ini tidak digunakan

**Prioritas:** 🔴 **TINGGI** (Karena diperlukan untuk integrasi dengan Outbound)

---

### 3. **Integrasi Supplier ke Inbound Transaction** ❌

**Status:** Belum terintegrasi

**Yang Sudah Ada:**
- ✅ Migration `inbound` memiliki kolom `supplier_id` dengan foreign key
- ✅ Controller `InboundController` sudah ada

**Yang Belum Ada:**
- ❌ Form Inbound tidak memiliki field untuk memilih Supplier
- ❌ Controller tidak menyimpan `supplier_id` saat membuat inbound
- ❌ Validasi `supplier_id` tidak ada di form
- ❌ Tampilan daftar inbound tidak menampilkan nama supplier

**Dampak:**
- Data supplier_id di tabel `inbound` kemungkinan NULL atau tidak valid
- Tidak bisa melacak dari supplier mana barang masuk

**Prioritas:** 🔴 **TINGGI** (Karena struktur database sudah ada)

---

### 4. **Integrasi Customer ke Outbound Transaction** ❌

**Status:** Belum terintegrasi

**Yang Sudah Ada:**
- ✅ Migration `outbound` memiliki kolom `customer_id` dengan foreign key
- ✅ Controller `OutboundController` sudah ada

**Yang Belum Ada:**
- ❌ Form Outbound tidak memiliki field untuk memilih Customer
- ❌ Controller tidak menyimpan `customer_id` saat membuat outbound
- ❌ Validasi `customer_id` tidak ada di form
- ❌ Tampilan daftar outbound tidak menampilkan nama customer

**Dampak:**
- Data customer_id di tabel `outbound` kemungkinan NULL atau tidak valid
- Tidak bisa melacak ke customer mana barang keluar

**Prioritas:** 🔴 **TINGGI** (Karena struktur database sudah ada)

---

### 5. **Sistem Notifikasi** ❌

**Status:** Belum dibuat

**Yang Disebutkan di Dokumentasi:**
- Low Stock Alert: Peringatan untuk barang dengan stok rendah
- Expired Alert: Peringatan untuk barang yang akan atau sudah kadaluarsa

**Yang Belum Ada:**
- ❌ Model untuk menyimpan notifikasi
- ❌ Migration untuk tabel `notifications`
- ❌ Controller untuk mengelola notifikasi
- ❌ Service untuk generate notifikasi otomatis
- ❌ View untuk menampilkan notifikasi
- ❌ Real-time notification system (WebSocket/Pusher)
- ❌ Badge counter untuk jumlah notifikasi di header
- ❌ Notification dropdown di header

**Prioritas:** 🟡 **SEDANG** (Fitur tambahan yang meningkatkan UX)

---

### 6. **Import Data** ❌

**Status:** Belum dibuat

**Yang Disebutkan di Dokumentasi:**
- Import Ready: Struktur data siap untuk import dari sistem lain

**Yang Belum Ada:**
- ❌ Controller untuk handle import
- ❌ View untuk upload file import
- ❌ Service untuk parsing file (Excel, CSV)
- ❌ Validasi data import
- ❌ Preview data sebelum import
- ❌ Error handling untuk data yang tidak valid
- ❌ Batch import untuk multiple records

**Prioritas:** 🟡 **SEDANG** (Fitur tambahan untuk efisiensi)

---

### 7. **Chat Feature** ⚠️

**Status:** Route ada, tetapi implementasi tidak lengkap

**Yang Sudah Ada:**
- ✅ Route: `/chat` dengan view `chat.index`
- ✅ Link di sidebar (tidak terlihat di sidebar saat ini)

**Yang Belum Ada:**
- ❌ Controller untuk chat
- ❌ Model untuk menyimpan pesan chat
- ❌ Migration untuk tabel `messages` atau `chats`
- ❌ Real-time chat system (WebSocket/Pusher)
- ❌ View untuk interface chat
- ❌ Integration dengan user management

**Prioritas:** 🟢 **RENDAH** (Fitur tambahan, mungkin tidak diperlukan untuk inventory system)

---

## 🟡 Fitur Tambahan yang Bisa Ditingkatkan

### 8. **Edit/Update Inbound Transaction** ⚠️

**Status:** Method ada tetapi redirect ke index

**Yang Ada:**
- ✅ Route untuk `edit` dan `update`
- ❌ Method `edit()` hanya redirect ke index
- ❌ Method `update()` hanya redirect ke index
- ❌ View untuk edit inbound tidak ada

**Prioritas:** 🟡 **SEDANG** (Berguna untuk koreksi data)

---

### 9. **Edit/Update Outbound Transaction** ⚠️

**Status:** Method ada tetapi redirect ke index

**Yang Ada:**
- ✅ Route untuk `edit` dan `update`
- ❌ Method `edit()` hanya redirect ke index
- ❌ Method `update()` hanya redirect ke index
- ❌ View untuk edit outbound tidak ada

**Prioritas:** 🟡 **SEDANG** (Berguna untuk koreksi data)

---

### 10. **Detail/Show Inbound Transaction** ⚠️

**Status:** Method ada tetapi redirect ke index

**Yang Ada:**
- ✅ Route untuk `show`
- ❌ Method `show()` hanya redirect ke index
- ❌ View untuk detail inbound tidak ada

**Prioritas:** 🟡 **SEDANG** (Berguna untuk melihat detail lengkap)

---

### 11. **Detail/Show Outbound Transaction** ⚠️

**Status:** Method ada tetapi redirect ke index

**Yang Ada:**
- ✅ Route untuk `show`
- ❌ Method `show()` hanya redirect ke index
- ❌ View untuk detail outbound tidak ada

**Prioritas:** 🟡 **SEDANG** (Berguna untuk melihat detail lengkap)

---

### 12. **Delete Inbound Transaction** ⚠️

**Status:** Method ada tetapi redirect ke index

**Yang Ada:**
- ✅ Route untuk `destroy`
- ❌ Method `destroy()` hanya redirect ke index
- ❌ Tidak ada validasi apakah transaksi bisa dihapus (apakah sudah mempengaruhi stock)

**Prioritas:** 🟡 **SEDANG** (Perlu validasi untuk mencegah penghapusan yang merusak data)

---

### 13. **Delete Outbound Transaction** ⚠️

**Status:** Method ada tetapi redirect ke index

**Yang Ada:**
- ✅ Route untuk `destroy`
- ❌ Method `destroy()` hanya redirect ke index
- ❌ Tidak ada validasi apakah transaksi bisa dihapus (apakah sudah mempengaruhi stock)

**Prioritas:** 🟡 **SEDANG** (Perlu validasi untuk mencegah penghapusan yang merusak data)

---

## 📊 Tabel Ringkasan Prioritas

| No | Fitur | Prioritas | Status | Estimasi Waktu |
|---|---|---|---|---|
| 1 | Manajemen Supplier | 🔴 TINGGI | ❌ Belum dibuat | 4-6 jam |
| 2 | Manajemen Customer | 🔴 TINGGI | ❌ Belum dibuat | 4-6 jam |
| 3 | Integrasi Supplier ke Inbound | 🔴 TINGGI | ❌ Belum terintegrasi | 2-3 jam |
| 4 | Integrasi Customer ke Outbound | 🔴 TINGGI | ❌ Belum terintegrasi | 2-3 jam |
| 5 | Sistem Notifikasi | 🟡 SEDANG | ❌ Belum dibuat | 8-12 jam |
| 6 | Import Data | 🟡 SEDANG | ❌ Belum dibuat | 6-8 jam |
| 7 | Chat Feature | 🟢 RENDAH | ⚠️ Tidak lengkap | 12-16 jam |
| 8 | Edit Inbound | 🟡 SEDANG | ⚠️ Tidak lengkap | 2-3 jam |
| 9 | Edit Outbound | 🟡 SEDANG | ⚠️ Tidak lengkap | 2-3 jam |
| 10 | Detail Inbound | 🟡 SEDANG | ⚠️ Tidak lengkap | 1-2 jam |
| 11 | Detail Outbound | 🟡 SEDANG | ⚠️ Tidak lengkap | 1-2 jam |
| 12 | Delete Inbound | 🟡 SEDANG | ⚠️ Tidak lengkap | 2-3 jam |
| 13 | Delete Outbound | 🟡 SEDANG | ⚠️ Tidak lengkap | 2-3 jam |

---

## 🎯 Rekomendasi Implementasi

### Fase 1: Prioritas Tinggi (Wajib)
1. ✅ Buat Model, Controller, Service, View untuk **Supplier**
2. ✅ Buat Model, Controller, Service, View untuk **Customer**
3. ✅ Integrasikan Supplier ke form dan controller **Inbound**
4. ✅ Integrasikan Customer ke form dan controller **Outbound**

**Total Estimasi:** 12-18 jam

### Fase 2: Prioritas Sedang (Penting)
5. ✅ Buat sistem **Notifikasi** (Low Stock, Expired Alert)
6. ✅ Buat fitur **Import Data** (Excel/CSV)
7. ✅ Implementasikan **Edit/Update** untuk Inbound dan Outbound
8. ✅ Implementasikan **Detail/Show** untuk Inbound dan Outbound
9. ✅ Implementasikan **Delete** dengan validasi untuk Inbound dan Outbound

**Total Estimasi:** 21-31 jam

### Fase 3: Prioritas Rendah (Opsional)
10. ✅ Lengkapi **Chat Feature** (jika diperlukan)
11. ✅ Fitur tambahan lainnya sesuai kebutuhan

**Total Estimasi:** 12-16 jam

---

## 📝 Catatan Tambahan

### Struktur Database yang Sudah Ada
- Tabel `suppliers` sudah ada di database
- Tabel `customers` sudah ada di database
- Foreign key `supplier_id` di tabel `inbound` sudah ada
- Foreign key `customer_id` di tabel `outbound` sudah ada

### Dampak Jika Tidak Diimplementasikan
1. **Data Integrity Issue:**
   - Foreign key constraint mungkin gagal jika data supplier/customer tidak ada
   - Data inbound/outbound mungkin memiliki supplier_id/customer_id yang NULL atau invalid

2. **Fungsionalitas Tidak Lengkap:**
   - Tidak bisa melacak dari supplier mana barang masuk
   - Tidak bisa melacak ke customer mana barang keluar
   - Laporan tidak bisa di-filter berdasarkan supplier/customer

3. **User Experience:**
   - User tidak bisa mengelola data supplier dan customer
   - Form transaksi tidak lengkap (tidak ada pilihan supplier/customer)

---

## 🔍 Checklist Implementasi

### Untuk Supplier Management:
- [ ] Buat Model `Supplier.php`
- [ ] Buat Migration (sudah ada, cek apakah perlu update)
- [ ] Buat Controller `SupplierController.php`
- [ ] Buat Service `SupplierService.php`
- [ ] Buat View: `index.blade.php`, `form.blade.php`, `show.blade.php`
- [ ] Tambahkan route di `web.php`
- [ ] Tambahkan link di sidebar
- [ ] Update Seeder jika perlu

### Untuk Customer Management:
- [ ] Buat Model `Customer.php`
- [ ] Buat Migration (sudah ada, cek apakah perlu update)
- [ ] Buat Controller `CustomerController.php`
- [ ] Buat Service `CustomerService.php`
- [ ] Buat View: `index.blade.php`, `form.blade.php`, `show.blade.php`
- [ ] Tambahkan route di `web.php`
- [ ] Tambahkan link di sidebar
- [ ] Update Seeder jika perlu

### Untuk Integrasi Supplier ke Inbound:
- [ ] Update form Inbound: tambahkan select dropdown untuk Supplier
- [ ] Update `InboundController::store()`: simpan `supplier_id`
- [ ] Update validasi: tambahkan `supplier_id` required
- [ ] Update view index Inbound: tampilkan nama supplier
- [ ] Update view create Inbound: tambahkan field supplier

### Untuk Integrasi Customer ke Outbound:
- [ ] Update form Outbound: tambahkan select dropdown untuk Customer
- [ ] Update `OutboundController::store()`: simpan `customer_id`
- [ ] Update validasi: tambahkan `customer_id` required
- [ ] Update view index Outbound: tampilkan nama customer
- [ ] Update view create Outbound: tambahkan field customer

---

## 📌 Kesimpulan

**Fitur yang paling penting dan harus segera dibuat:**
1. **Supplier Management** (CRUD lengkap)
2. **Customer Management** (CRUD lengkap)
3. **Integrasi Supplier ke Inbound Transaction**
4. **Integrasi Customer ke Outbound Transaction**

Keempat fitur ini adalah **prioritas tinggi** karena:
- Struktur database sudah ada
- Foreign key constraint sudah terpasang
- Tanpa fitur ini, sistem tidak bisa berfungsi dengan baik
- Data integrity bisa terganggu

Setelah keempat fitur ini selesai, sistem akan lebih lengkap dan siap untuk digunakan dalam production.

