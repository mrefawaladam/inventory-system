<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nama Supplier</label>
        <p class="mb-0">{{ $supplier->name }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Telepon</label>
        <p class="mb-0">{{ $supplier->phone ?? '-' }}</p>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Alamat</label>
    <p class="mb-0">{{ $supplier->address ?? '-' }}</p>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Tanggal Dibuat</label>
    <p class="mb-0">{{ $supplier->created_at->format('Y-m-d H:i:s') }}</p>
</div>

@if($supplier->inbounds->count() > 0)
<hr>
<div class="mb-3">
    <label class="form-label fw-bold">Jumlah Transaksi Inbound</label>
    <p class="mb-0">
        <span class="badge bg-primary">{{ $supplier->inbounds->count() }} transaksi</span>
    </p>
</div>
@endif

