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
    <div id="show-location-map" style="height: 300px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
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

@if($warehouse->latitude && $warehouse->longitude)
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    const lat = {{ $warehouse->latitude }};
    const lng = {{ $warehouse->longitude }};
    
    // Initialize map for showing location
    const showMap = L.map('show-location-map').setView([lat, lng], 15);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(showMap);
    
    // Add marker
    L.marker([lat, lng])
        .addTo(showMap)
        .bindPopup('<b>{{ $warehouse->name }}</b><br>{{ $warehouse->address }}')
        .openPopup();
    
    // Invalidate size when modal is shown
    $('#warehouseModal').on('shown.bs.modal', function() {
        setTimeout(function() {
            showMap.invalidateSize();
        }, 300);
    });
});
</script>
@endpush
@endif

