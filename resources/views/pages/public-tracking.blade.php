@extends('layouts.public')

@section('title', 'Tracking Pengiriman')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #tracking-map {
        height: 600px;
        width: 100%;
        border-radius: 8px;
        z-index: 1;
    }
    .filter-panel {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .route-legend {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        min-width: 200px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin-right: 10px;
    }
    .public-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 0;
        margin-bottom: 30px;
    }
    .back-to-home {
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        margin-bottom: 15px;
    }
    .back-to-home:hover {
        color: rgba(255,255,255,0.8);
    }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="public-header">
    <div class="container">
        <a href="{{ url('/') }}" class="back-to-home">
            <iconify-icon icon="solar:arrow-left-line-duotone" class="me-2"></iconify-icon>
            Kembali ke Beranda
        </a>
        <h1 class="mb-0">Tracking Pengiriman</h1>
        <p class="mb-0 opacity-75">Pantau pergerakan barang secara real-time</p>
    </div>
</div>

<div class="container">
    <!-- Filter Panel -->
    <div class="card mb-4">
        <div class="card-body filter-panel">
            <h5 class="card-title mb-4">Filter Transaksi</h5>
            <form id="filter-form" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipe Transaksi</label>
                    <select name="type" id="filter-type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="INBOUND">Inbound</option>
                        <option value="OUTBOUND">Outbound</option>
                        <option value="TRANSFER">Transfer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Item</label>
                    <select name="item_id" id="filter-item" class="form-select">
                        <option value="">Semua Item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ ($filters['item_id'] ?? '') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} @if($item->sku)({{ $item->sku }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sekolah</label>
                    <select name="warehouse_id" id="filter-warehouse" class="form-select">
                        <option value="">Semua Sekolah</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="date_from" id="filter-date-from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="date_to" id="filter-date-to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <iconify-icon icon="solar:magnifer-line-duotone" class="me-1"></iconify-icon>
                        Filter
                    </button>
                    <button type="button" id="btn-reset-filter" class="btn btn-secondary">
                        <iconify-icon icon="solar:refresh-line-duotone" class="me-1"></iconify-icon>
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Map Section -->
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Peta Tracking Transaksi</h5>
                        <div>
                            <button type="button" id="btn-refresh-map" class="btn btn-sm btn-outline-primary">
                                <iconify-icon icon="solar:refresh-line-duotone" class="me-1"></iconify-icon>
                                Refresh
                            </button>
                            <button type="button" id="btn-clear-routes" class="btn btn-sm btn-outline-secondary">
                                <iconify-icon icon="solar:close-circle-line-duotone" class="me-1"></iconify-icon>
                                Clear Routes
                            </button>
                        </div>
                    </div>
                    <div class="position-relative">
                        <div id="tracking-map"></div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <iconify-icon icon="solar:info-circle-line-duotone" class="me-1"></iconify-icon>
                            Menampilkan <strong id="route-count">{{ count($routes) }}</strong> route transaksi
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card route-legend">
                <div class="card-body">
                    <h5 class="card-title mb-3">Legenda</h5>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #28a745;"></div>
                        <span>Sudah Dikirim</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #dc3545;"></div>
                        <span>Belum Dikirim</span>
                    </div>
                    <hr>
                    <small class="text-muted">
                        <iconify-icon icon="solar:info-circle-line-duotone" class="me-1"></iconify-icon>
                        Klik marker untuk detail transaksi
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pass data to JavaScript
window.trackingData = {
    routes: @json($routes),
    warehouses: @json($warehousesMap),
    filters: @json($filters ?? [])
};
</script>
@endsection

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    let trackingMap;
    let routePolylines = [];
    let routeMarkers = [];
    let warehouseMarkers = [];

    // Initialize map
    function initMap() {
        if (typeof L === 'undefined') {
            console.error('Leaflet library not loaded');
            return;
        }

        // Default center (Jakarta)
        let defaultLat = -6.2088;
        let defaultLng = 106.8456;

        // Check if we have warehouses data
        if (window.trackingData && window.trackingData.warehouses && window.trackingData.warehouses.length > 0) {
            const firstWarehouse = window.trackingData.warehouses[0];
            defaultLat = firstWarehouse.latitude;
            defaultLng = firstWarehouse.longitude;
        }

        // Initialize map
        trackingMap = L.map('tracking-map').setView([defaultLat, defaultLng], 6);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(trackingMap);

        // Invalidate size
        setTimeout(function() {
            if (trackingMap) {
                trackingMap.invalidateSize();
            }
        }, 100);
    }

    // Add warehouse markers
    function addWarehouseMarkers() {
        if (!trackingMap || !window.trackingData || !window.trackingData.warehouses) return;

        // Clear existing warehouse markers
        warehouseMarkers.forEach(marker => trackingMap.removeLayer(marker));
        warehouseMarkers = [];

        window.trackingData.warehouses.forEach(function(warehouse) {
            const marker = L.marker([warehouse.latitude, warehouse.longitude], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34]
                })
            }).addTo(trackingMap);

            marker.bindPopup(`
                <b>${warehouse.name}</b><br>
                ${warehouse.address || ''}
            `);

            warehouseMarkers.push(marker);
        });
    }

    // Get color based on delivery status
    function getStatusColor(status) {
        return status === 'delivered' ? '#28a745' : '#dc3545';
    }

    function getMarkerIcon(status) {
        const color = status === 'delivered' ? 'green' : 'red';
        return L.icon({
            iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${color}.png`,
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });
    }

    // Add route to map
    function addRoute(route) {
        if (!trackingMap) return;

        const fromLat = route.from.latitude;
        const fromLng = route.from.longitude;
        const toLat = route.to.latitude;
        const toLng = route.to.longitude;

        const status = route.delivery_status || 'pending';
        const statusColor = getStatusColor(status);
        const markerIcon = getMarkerIcon(status);

        // Create polyline
        const polyline = L.polyline(
            [[fromLat, fromLng], [toLat, toLng]],
            {
                color: statusColor,
                weight: 3,
                opacity: 0.7
            }
        ).addTo(trackingMap);

        // Create origin marker
        const originMarker = L.marker([fromLat, fromLng], {
            icon: markerIcon
        }).addTo(trackingMap);

        // Create destination marker
        const destMarker = L.marker([toLat, toLng], {
            icon: markerIcon
        }).addTo(trackingMap);

        // Popup content
        const popupContent = `
            <div style="min-width: 200px;">
                <h6><strong>${route.transaction_code}</strong></h6>
                <hr>
                <p class="mb-1"><strong>Tipe:</strong> ${route.type_label}</p>
                <p class="mb-1"><strong>Item:</strong> ${route.item.name}</p>
                <p class="mb-1"><strong>Quantity:</strong> ${route.quantity.toLocaleString('id-ID')}</p>
                <p class="mb-1"><strong>Dari:</strong> ${route.from.warehouse_name || 'N/A'}</p>
                <p class="mb-1"><strong>Ke:</strong> ${route.to.warehouse_name || 'N/A'}</p>
                <p class="mb-1"><strong>Status:</strong> ${route.delivery_status_label || (status === 'delivered' ? 'Sudah Dikirim' : 'Belum Dikirim')}</p>
                <p class="mb-1"><strong>Jarak:</strong> ${route.distance_km} km</p>
                <p class="mb-1"><strong>Tanggal:</strong> ${route.created_at_formatted}</p>
                <p class="mb-0"><strong>User:</strong> ${route.user.name}</p>
                ${route.notes ? `<hr><p class="mb-0"><small><strong>Notes:</strong> ${route.notes}</small></p>` : ''}
            </div>
        `;

        originMarker.bindPopup(popupContent);
        destMarker.bindPopup(popupContent);
        polyline.bindPopup(popupContent);

        // Store references
        routePolylines.push(polyline);
        routeMarkers.push(originMarker);
        routeMarkers.push(destMarker);
    }

    // Add all routes to map
    function addAllRoutes() {
        if (!window.trackingData || !window.trackingData.routes) return;

        clearRoutes();

        window.trackingData.routes.forEach(function(route) {
            addRoute(route);
        });

        // Fit bounds to show all routes
        if (routePolylines.length > 0) {
            const group = new L.featureGroup(routePolylines);
            trackingMap.fitBounds(group.getBounds().pad(0.1));
        }

        // Update route count
        $('#route-count').text(window.trackingData.routes.length);
    }

    // Clear all routes
    function clearRoutes() {
        routePolylines.forEach(polyline => trackingMap.removeLayer(polyline));
        routeMarkers.forEach(marker => trackingMap.removeLayer(marker));
        routePolylines = [];
        routeMarkers = [];
    }

    // Load routes from API
    function loadRoutes(filters = {}) {
        $.ajax({
            url: '{{ route("public.tracking.routes") }}',
            method: 'GET',
            data: filters,
            success: function(response) {
                if (response.success) {
                    window.trackingData.routes = response.routes;
                    window.trackingData.warehouses = response.warehouses;
                    addAllRoutes();
                    addWarehouseMarkers();
                }
            },
            error: function() {
                console.error('Error loading routes');
            }
        });
    }

    // Initialize
    initMap();
    addWarehouseMarkers();
    addAllRoutes();

    // Filter form submit
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        window.location.href = '{{ route("public.tracking") }}?' + formData;
    });

    // Reset filter
    $('#btn-reset-filter').on('click', function() {
        window.location.href = '{{ route("public.tracking") }}';
    });

    // Refresh map
    $('#btn-refresh-map').on('click', function() {
        const filters = {
            type: $('#filter-type').val(),
            item_id: $('#filter-item').val(),
            warehouse_id: $('#filter-warehouse').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val()
        };
        loadRoutes(filters);
    });

    // Clear routes
    $('#btn-clear-routes').on('click', function() {
        clearRoutes();
        $('#route-count').text(0);
    });
});
</script>
@endpush

