# Dokumentasi Flow Sistem Inventory Sekolah

## 📋 Ringkasan Flow Sistem

Sistem ini mengelola inventori barang untuk sekolah dengan alur transaksi yang jelas. Status pengiriman ditentukan berdasarkan keberadaan stock di lokasi.

---

## 🔄 Flow Transaksi Lengkap

### 1. **Barang Masuk (Inbound / Barang Masuk)**

**Alur:**
```
Supplier → Barang Masuk → Lokasi (Area/Rak/Tempat) → Stock Bertambah → Status: "Sudah Dikirim"
```

**Detail:**
- User memilih: **Item**, **Lokasi Tujuan** (Area/Rak/Tempat), **Jumlah**, **Batch** (opsional), **Tanggal Expired** (opsional)
- Sistem melakukan:
  1. ✅ Validasi kapasitas lokasi
  2. ✅ Menambah stock di lokasi tujuan (`StockService::increaseStock()`)
  3. ✅ Membuat record transaksi (`Transaction` dengan type `INBOUND`)
  4. ✅ Status pengiriman: **"Sudah Dikirim"** (karena barang sudah ada di lokasi)

**Contoh:**
- Item: "Meja Belajar"
- Lokasi: "Sekolah A - Area 1 - Rak 2 - Tempat 5"
- Jumlah: 10 unit
- **Hasil:** Stock di lokasi tersebut bertambah 10 unit, status lokasi menjadi "Sudah Dikirim"

---

### 2. **Barang Keluar (Outbound / Barang Keluar)**

**Alur:**
```
Lokasi (Area/Rak/Tempat) → Barang Keluar → Customer → Stock Berkurang → Status: "Belum Dikirim"
```

**Detail:**
- User memilih: **Item**, **Lokasi Asal** (Area/Rak/Tempat), **Jumlah**
- Sistem melakukan:
  1. ✅ Validasi stock tersedia (cek apakah stock cukup)
  2. ✅ Mengurangi stock dari lokasi asal (`StockService::decreaseStock()`)
  3. ✅ Menggunakan metode **FEFO** (First Expired First Out) atau **FIFO** (First In First Out)
  4. ✅ Membuat record transaksi (`Transaction` dengan type `OUTBOUND`)
  5. ✅ Status pengiriman: **"Belum Dikirim"** (karena barang sudah keluar dari lokasi)

**Contoh:**
- Item: "Meja Belajar"
- Lokasi: "Sekolah A - Area 1 - Rak 2 - Tempat 5"
- Jumlah: 5 unit
- **Hasil:** Stock di lokasi tersebut berkurang 5 unit, jika stock habis (quantity = 0), status lokasi menjadi "Belum Dikirim"

---

### 3. **Pindah Barang (Transfer / Pindah Barang)**

**Alur:**
```
Lokasi Asal → Pindah → Lokasi Tujuan → Stock Berkurang di Asal, Bertambah di Tujuan
```

**Detail:**
- User memilih: **Item**, **Lokasi Asal**, **Lokasi Tujuan**, **Jumlah**
- Sistem melakukan:
  1. ✅ Validasi stock tersedia di lokasi asal
  2. ✅ Mengurangi stock dari lokasi asal (`StockService::decreaseStock()`)
  3. ✅ Menambah stock ke lokasi tujuan (`StockService::increaseStock()`)
  4. ✅ Membuat record transaksi (`Transaction` dengan type `TRANSFER`)
  5. ✅ Status pengiriman:
     - Lokasi asal: Jika stock habis → "Belum Dikirim"
     - Lokasi tujuan: **"Sudah Dikirim"** (karena barang sudah ada di lokasi)

**Contoh:**
- Item: "Meja Belajar"
- Lokasi Asal: "Sekolah A - Area 1 - Rak 2 - Tempat 5"
- Lokasi Tujuan: "Sekolah B - Area 2 - Rak 3 - Tempat 1"
- Jumlah: 3 unit
- **Hasil:** 
  - Stock di lokasi asal berkurang 3 unit
  - Stock di lokasi tujuan bertambah 3 unit
  - Status lokasi tujuan menjadi "Sudah Dikirim"

---

## 📦 Status Pengiriman (Delivery Status)

### Cara Kerja Status Pengiriman

Status pengiriman ditentukan berdasarkan **apakah lokasi memiliki stock dengan quantity > 0**.

**Rumus:**
```php
$hasDelivered = ($location->delivered_stock_quantity ?? 0) > 0;

if ($hasDelivered) {
    Status = "Sudah Dikirim" (Hijau)
} else {
    Status = "Belum Dikirim" (Merah)
}
```

**Penjelasan:**
- **"Sudah Dikirim"** = Lokasi memiliki stock (quantity > 0)
  - Artinya: Barang sudah ada di lokasi tersebut
  - Contoh: Setelah Inbound, atau setelah Transfer masuk ke lokasi
  
- **"Belum Dikirim"** = Lokasi tidak memiliki stock (quantity = 0)
  - Artinya: Barang belum ada di lokasi tersebut
  - Contoh: Lokasi baru dibuat, atau setelah Outbound yang menghabiskan stock

### Contoh Skenario Status Pengiriman

**Skenario 1: Lokasi Baru**
- Lokasi dibuat: "Sekolah A - Area 1 - Rak 1 - Tempat 1"
- Stock: 0 unit
- Status: **"Belum Dikirim"** ❌

**Skenario 2: Setelah Inbound**
- Inbound: 10 unit "Meja Belajar" ke lokasi
- Stock: 10 unit
- Status: **"Sudah Dikirim"** ✅

**Skenario 3: Setelah Outbound Sebagian**
- Outbound: 5 unit dari lokasi
- Stock: 5 unit (masih ada sisa)
- Status: **"Sudah Dikirim"** ✅ (masih ada stock)

**Skenario 4: Setelah Outbound Habis**
- Outbound: 10 unit dari lokasi (stock habis)
- Stock: 0 unit
- Status: **"Belum Dikirim"** ❌

**Skenario 5: Transfer Masuk**
- Transfer: 5 unit masuk ke lokasi
- Stock: 5 unit
- Status: **"Sudah Dikirim"** ✅

**Skenario 6: Transfer Keluar**
- Transfer: 5 unit keluar dari lokasi (stock habis)
- Stock: 0 unit
- Status: **"Belum Dikirim"** ❌

---

## 🗂️ Struktur Data

### 1. **Stock** (Tabel: `stocks`)
Menyimpan jumlah barang di setiap lokasi.

```php
Stock {
    item_id: ID barang
    location_id: ID lokasi (Area/Rak/Tempat)
    quantity: Jumlah barang
    batch: Nomor batch (opsional)
    expired_at: Tanggal expired (opsional)
}
```

### 2. **Transaction** (Tabel: `transactions`)
Mencatat semua transaksi (Inbound, Outbound, Transfer).

```php
Transaction {
    transaction_code: "IN-20251120-0001" / "OUT-20251120-0001" / "TRF-20251120-0001"
    type: INBOUND / OUTBOUND / TRANSFER
    item_id: ID barang
    from_location_id: ID lokasi asal (null untuk INBOUND)
    to_location_id: ID lokasi tujuan (null untuk OUTBOUND)
    quantity: Jumlah barang
    batch: Nomor batch
    user_id: ID user yang membuat transaksi
    notes: Catatan
}
```

### 3. **Location** (Tabel: `locations`)
Menyimpan struktur lokasi (Area → Rak → Tempat).

```php
Location {
    warehouse_id: ID sekolah
    parent_id: ID parent (null untuk Area, ID Area untuk Rak, ID Rak untuk Tempat)
    code: "Z01" / "R01" / "R01-S01"
    type: ZONE (Area) / RACK (Rak) / SLOT (Tempat)
    capacity: Jumlah siswa penerima
    description: Deskripsi
}
```

---

## 🔍 Query Status Pengiriman

Status pengiriman dihitung dengan query:

```php
// Di LocationController
->withSum(['stocks as delivered_stock_quantity' => function ($stockQuery) {
    $stockQuery->where('quantity', '>', 0);
}], 'quantity')

// Kemudian di view
$hasDelivered = ($location->delivered_stock_quantity ?? 0) > 0;
```

**Penjelasan:**
- Query menghitung total `quantity` dari semua stock di lokasi yang memiliki `quantity > 0`
- Jika hasil > 0 → Status "Sudah Dikirim"
- Jika hasil = 0 → Status "Belum Dikirim"

---

## 📊 Diagram Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    FLOW SISTEM INVENTORY                      │
└─────────────────────────────────────────────────────────────┘

1. BARANG MASUK (INBOUND)
   ┌──────────┐      ┌──────────┐      ┌──────────┐
   │ Supplier │ ───> │  Lokasi  │ ───> │  Stock   │
   └──────────┘      └──────────┘      └──────────┘
                            │
                            ▼
                    Status: "Sudah Dikirim" ✅

2. BARANG KELUAR (OUTBOUND)
   ┌──────────┐      ┌──────────┐      ┌──────────┐
   │  Lokasi  │ ───> │ Customer │      │  Stock   │
   └──────────┘      └──────────┘      └──────────┘
        │                                    │
        └────────── Stock Berkurang ────────┘
                            │
                            ▼
                    Status: "Belum Dikirim" ❌
                    (jika stock habis)

3. PINDAH BARANG (TRANSFER)
   ┌──────────┐      ┌──────────┐      ┌──────────┐
   │ Lokasi   │ ───> │ Lokasi   │      │  Stock   │
   │  Asal    │      │ Tujuan   │      └──────────┘
   └──────────┘      └──────────┘            │
        │                 │                  │
        └─── Stock ───────┴─── Stock ───────┘
        Berkurang         Bertambah
            │                 │
            ▼                 ▼
    Status: "Belum"    Status: "Sudah"
    (jika habis)       Dikirim ✅
```

---

## 🎯 Kesimpulan

**Status Pengiriman = Indikator keberadaan stock di lokasi**

- ✅ **"Sudah Dikirim"** = Barang sudah ada di lokasi (stock > 0)
- ❌ **"Belum Dikirim"** = Barang belum ada di lokasi (stock = 0)

**Flow:**
1. **Inbound** → Stock bertambah → Status "Sudah Dikirim"
2. **Outbound** → Stock berkurang → Status bisa berubah menjadi "Belum Dikirim" jika stock habis
3. **Transfer** → Stock pindah → Status lokasi tujuan "Sudah Dikirim", lokasi asal bisa "Belum Dikirim" jika stock habis

