<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Barang</label>
        <p class="mb-0">{{ $stock->item->sku }} - {{ $stock->item->name }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Quantity</label>
        <p class="mb-0">
            <span class="badge bg-primary">{{ number_format($stock->quantity, 0, ',', '.') }} {{ $stock->item->unit ?? '' }}</span>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Sekolah</label>
        <p class="mb-0">{{ $stock->location->warehouse->name ?? '-' }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Lokasi</label>
        <p class="mb-0">{{ $stock->location->code }} ({{ $stock->location->type->label() }})</p>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Path Lengkap</label>
    <p class="mb-0">{{ $stock->location->full_path }}</p>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Batch</label>
        <p class="mb-0">{{ $stock->batch ?? '-' }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Tanggal Pengiriman</label>
        <p class="mb-0">
            @if($stock->expired_at)
                {{ $stock->expired_at->format('Y-m-d') }}
                @if($stock->expired_at->isPast())
                    <span class="badge bg-success ms-2">Sudah Dikirim</span>
                @else
                    <span class="badge bg-danger ms-2">Belum Dikirim</span>
                @endif
            @else
                - <span class="badge bg-danger ms-2">Belum Dikirim</span>
            @endif
        </p>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Tanggal Dibuat</label>
    <p class="mb-0">{{ $stock->created_at->format('Y-m-d H:i:s') }}</p>
</div>

@if(isset($summary))
<hr>
<div class="mb-3">
    <h6>Ringkasan Stok di Lokasi Ini</h6>
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4>{{ number_format($summary['total_quantity'], 0, ',', '.') }}</h4>
                    <p class="mb-0 text-muted">Total Quantity</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4>{{ $summary['batches'] }}</h4>
                    <p class="mb-0 text-muted">Batches</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-success">{{ number_format($summary['delivered_quantity'], 0, ',', '.') }}</h4>
                    <p class="mb-0 text-muted">Sudah Dikirim</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-danger">{{ number_format($summary['pending_quantity'], 0, ',', '.') }}</h4>
                    <p class="mb-0 text-muted">Belum Dikirim</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

