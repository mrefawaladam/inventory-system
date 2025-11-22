<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nama Customer</label>
        <p class="mb-0">{{ $customer->name }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Telepon</label>
        <p class="mb-0">{{ $customer->phone ?? '-' }}</p>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Alamat</label>
    <p class="mb-0">{{ $customer->address ?? '-' }}</p>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Tanggal Dibuat</label>
    <p class="mb-0">{{ $customer->created_at->format('Y-m-d H:i:s') }}</p>
</div>

@if($customer->outbounds->count() > 0)
<hr>
<div class="mb-3">
    <label class="form-label fw-bold">Jumlah Transaksi Outbound</label>
    <p class="mb-0">
        <span class="badge bg-primary">{{ $customer->outbounds->count() }} transaksi</span>
    </p>
</div>
@endif

