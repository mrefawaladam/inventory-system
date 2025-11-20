<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">SKU</label>
        <p class="mb-0">{{ $item->sku }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Barcode</label>
        <p class="mb-0">{{ $item->barcode }}</p>
        @if($item->barcode)
            <div class="mt-2">
                <img src="{{ route('items.barcode', $item->id) }}" alt="Barcode" style="height: 60px;">
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nama Barang</label>
        <p class="mb-0">{{ $item->name }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Unit</label>
        <p class="mb-0">{{ $item->unit ?? '-' }}</p>
    </div>
</div>

@if($item->image)
<div class="mb-3">
    <label class="form-label fw-bold">Gambar Produk</label>
    <div>
        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 1px solid #ddd;">
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Total Stok</label>
        <p class="mb-0">
            <span class="badge {{ $item->isLowStock() ? 'bg-danger' : 'bg-success' }}">
                {{ number_format($item->total_stock, 0, ',', '.') }} {{ $item->unit ?? '' }}
            </span>
        </p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Minimum Stok</label>
        <p class="mb-0">{{ number_format($item->minimum_stock, 0, ',', '.') }} {{ $item->unit ?? '' }}</p>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Tanggal Dibuat</label>
    <p class="mb-0">{{ $item->created_at->format('Y-m-d H:i:s') }}</p>
</div>

<hr>

<!-- Stock by Warehouse -->
<div class="mb-4">
    <h5 class="mb-3">Stok per Sekolah</h5>
    @if($stockByWarehouse->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Sekolah</th>
                        <th class="text-end">Total Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockByWarehouse as $stock)
                        <tr>
                            <td>{{ $stock->name }}</td>
                            <td class="text-end">
                                <strong>{{ number_format($stock->total_quantity, 0, ',', '.') }} {{ $item->unit ?? '' }}</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted">Belum ada stok di gudang manapun.</p>
    @endif
</div>

<!-- Stock by Location -->
<div class="mb-3">
    <h5 class="mb-3">Detail Stok per Lokasi</h5>
    @if($stockByLocation->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Sekolah</th>
                        <th>Lokasi</th>
                        <th>Path Lengkap</th>
                        <th class="text-end">Quantity</th>
                        <th>Batch</th>
                        <th>Expired At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockByLocation as $stock)
                        <tr>
                            <td>{{ $stock->location->warehouse->name ?? '-' }}</td>
                            <td>{{ $stock->location->code ?? '-' }}</td>
                            <td>{{ $stock->location->full_path ?? '-' }}</td>
                            <td class="text-end">
                                <strong>{{ number_format($stock->quantity, 0, ',', '.') }} {{ $item->unit ?? '' }}</strong>
                            </td>
                            <td>{{ $stock->batch ?? '-' }}</td>
                            <td>{{ $stock->expired_at ? $stock->expired_at->format('Y-m-d') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted">Belum ada stok di lokasi manapun.</p>
    @endif
</div>

