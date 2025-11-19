<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nama</label>
        <p class="mb-0">{{ $warehouse->name }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Tanggal Dibuat</label>
        <p class="mb-0">{{ $warehouse->created_at->format('Y-m-d H:i:s') }}</p>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Alamat</label>
    <p class="mb-0">{{ $warehouse->address ?? '-' }}</p>
</div>

@if($warehouse->latitude && $warehouse->longitude)
<div class="mb-3">
    <label class="form-label fw-bold">Lokasi</label>
    <div class="mb-2">
        <strong>Latitude:</strong> {{ number_format($warehouse->latitude, 8) }}<br>
        <strong>Longitude:</strong> {{ number_format($warehouse->longitude, 8) }}
    </div>
    <div id="show-location-map" style="height: 300px; width: 100%; border-radius: 8px; border: 1px solid #ddd; background-color: #f5f5f5;"></div>
    <div id="show-location-map-data" 
         data-lat="{{ $warehouse->latitude }}" 
         data-lng="{{ $warehouse->longitude }}"
         data-name="{{ $warehouse->name }}"
         data-address="{{ $warehouse->address ?? '' }}"
         style="display: none;"></div>
</div>
@else
<div class="mb-3">
    <label class="form-label fw-bold">Lokasi</label>
    <p class="mb-0 text-muted">Lokasi belum diatur</p>
</div>
@endif

@if($warehouse->description)
<div class="mb-3">
    <label class="form-label fw-bold">Deskripsi</label>
    <p class="mb-0">{{ $warehouse->description }}</p>
</div>
@endif

