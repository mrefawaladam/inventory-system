<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Kode</label>
        <p class="mb-0">{{ $location->code }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Tipe</label>
        <p class="mb-0">
            @if($location->type->value === 'ZONE')
                <span class="badge" style="background-color: #0d6efd; color: #fff;">Zone</span>
            @elseif($location->type->value === 'RACK')
                <span class="badge" style="background-color: #ffc107; color: #000;">Rack</span>
            @else
                <span class="badge" style="background-color: #198754; color: #fff;">Slot</span>
            @endif
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Sekolah</label>
        <p class="mb-0">{{ $location->warehouse->name }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Parent</label>
        <p class="mb-0">{{ $location->parent->code ?? '-' }}</p>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Path Lengkap</label>
    <p class="mb-0">{{ $location->full_path }}</p>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Jumlah Siswa yang Menerima</label>
        <p class="mb-0">{{ number_format($location->capacity, 0, ',', '.') }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Tanggal Dibuat</label>
        <p class="mb-0">{{ $location->created_at->format('Y-m-d H:i:s') }}</p>
    </div>
</div>

@if($location->description)
<div class="mb-3">
    <label class="form-label fw-bold">Deskripsi</label>
    <p class="mb-0">{{ $location->description }}</p>
</div>
@endif

@if($location->children()->count() > 0)
<div class="mb-3">
    <label class="form-label fw-bold">Lokasi Anak</label>
    <p class="mb-0 text-muted">{{ $location->children()->count() }} lokasi</p>
</div>
@endif

