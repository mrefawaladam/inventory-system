@extends('layouts.app')

@section('title', 'Manajemen Sekolah')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/libs/select2/dist/css/select2.min.css') }}" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #warehouse-map {
        height: 500px;
        width: 100%;
        border-radius: 8px;
        z-index: 1;
    }
    .map-container {
        position: relative;
    }
    /* Fix responsive table */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
    }
    .table-responsive table {
        min-width: 100%;
        width: 100%;
    }
    /* DataTable scroll container */
    .dataTables_wrapper .dataTables_scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .dataTables_wrapper .dataTables_scrollBody {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 768px) {
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
        }
        .table-responsive table {
            width: 100%;
            margin-bottom: 0;
        }
        .table-responsive thead,
        .table-responsive tbody,
        .table-responsive th,
        .table-responsive td,
        .table-responsive tr {
            display: block;
        }
        .table-responsive thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }
        .table-responsive tr {
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }
        .table-responsive td {
            border: none;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 50% !important;
        }
        .table-responsive td:before {
            content: attr(data-label);
            position: absolute;
            left: 6px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            font-weight: bold;
        }
    }
    /* Ensure table is scrollable on smaller screens */
    @media (max-width: 991px) {
        .table-responsive {
            overflow-x: scroll;
            -webkit-overflow-scrolling: touch;
        }
    }
    
    /* DataTable Processing/Loading Indicator */
    .dataTables_processing {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 15px;
        font-size: 16px;
        font-weight: 500;
        color: #333;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .dataTables_processing .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.3em;
    }
    
    .dataTables_wrapper {
        position: relative;
    }
    
    /* Loading overlay for table body */
    #warehouses-table tbody {
        position: relative;
    }
    
    .table-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 15px;
        z-index: 10;
        border-radius: 4px;
    }
    
    .table-loading-overlay .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.3em;
    }
    
    .table-loading-overlay .loading-text {
        font-size: 16px;
        font-weight: 500;
        color: #333;
    }
    /* Search results styling */
    #search-results {
        background: white;
        border: 1px solid #ddd;
    }
    #search-results .list-group-item {
        cursor: pointer;
        border-left: none;
        border-right: none;
    }
    #search-results .list-group-item:first-child {
        border-top: none;
    }
    #search-results .list-group-item:hover {
        background-color: #f8f9fa;
    }
    #search-results .list-group-item:last-child {
        border-bottom: none;
    }
    /* Map search results styling */
    #map-search-results {
        background: white;
        border: 1px solid #ddd;
    }
    #map-search-results .list-group-item {
        cursor: pointer;
        border-left: none;
        border-right: none;
    }
    #map-search-results .list-group-item:first-child {
        border-top: none;
    }
    #map-search-results .list-group-item:hover {
        background-color: #f8f9fa;
    }
    #map-search-results .list-group-item:last-child {
        border-bottom: none;
    }
    @media (max-width: 768px) {
        .d-flex.justify-content-between.align-items-center.mb-3 {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .d-flex.justify-content-between.align-items-center.mb-3 > div {
            width: 100% !important;
            margin-top: 10px;
        }
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Manajemen Sekolah"
    :breadcrumb-title="'Manajemen Sekolah'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Map Overview -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Peta Titik Lokasi Sekolah</h4>
            <div class="position-relative" style="width: 400px; max-width: 100%;">
                <div class="input-group">
                    <input
                        type="text"
                        class="form-control"
                        id="map-location-search"
                        placeholder="Cari alamat sekolah (contoh: Jln Merdeka, Kelurahan X)..."
                    >
                    <button class="btn btn-outline-primary" type="button" id="btn-search-map-location">
                        <i class="ti ti-search"></i> Cari
                    </button>
                </div>
                <div id="map-search-results" class="list-group position-absolute w-100 mt-1" style="max-height: 200px; overflow-y: auto; display: none; z-index: 1050; box-shadow: 0 2px 8px rgba(0,0,0,0.15); border-radius: 4px; background: white; border: 1px solid #ddd;"></div>
            </div>
        </div>
        <div class="map-container">
            <div id="warehouse-map"></div>
        </div>
    </div>
</div>

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Sekolah</h4>
                    <p class="card-subtitle mb-3">
                        Kelola sekolah penerima beserta titik lokasinya (alamat jalan). Anda dapat membuat, mengedit, dan menghapus sekolah dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-warehouse">
                    <i class="ti ti-plus me-1"></i> Tambah Sekolah
                </button>
            </div>
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table id="warehouses-table" class="table table-striped table-bordered align-middle" style="width: 100%; min-width: 800px;">
                    <thead>
                        <tr>
                            <th style="min-width: 60px;">ID</th>
                            <th style="min-width: 200px;">Nama Sekolah Rakyat</th>
                            <th style="min-width: 150px;">Penerima</th>
                            <th style="min-width: 250px;">Alamat Jalan</th>
                            <th style="min-width: 180px;">Kota/Kab & Provinsi</th>
                            <th style="min-width: 120px;">Tanggal Dibuat</th>
                            <th style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Warehouse Modal -->
<x-ui.modal
    id="warehouseModal"
    title="Tambah Sekolah"
    size="lg"
    content-id="warehouseModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-submit-form">Simpan</button>
    </x-slot>
</x-ui.modal>

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/select2/dist/js/select2.min.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    // Initialize map
    let warehouseMap;
    let warehouseMarkers = [];

    // Check if map container exists
    if ($('#warehouse-map').length > 0) {
        // Get warehouses data
        const warehousesData = @json($warehouses);

        // Set default coordinates
        let defaultLat = -6.2088; // Jakarta
        let defaultLng = 106.8456;

        if (warehousesData && warehousesData.length > 0 && warehousesData[0].latitude && warehousesData[0].longitude) {
            defaultLat = parseFloat(warehousesData[0].latitude);
            defaultLng = parseFloat(warehousesData[0].longitude);
        }

        try {
            // Initialize map
            warehouseMap = L.map('warehouse-map').setView([defaultLat, defaultLng], warehousesData && warehousesData.length > 1 ? 6 : 13);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(warehouseMap);

            // Invalidate size to ensure map renders correctly
            setTimeout(function() {
                if (warehouseMap) {
                    warehouseMap.invalidateSize();
                }
            }, 100);

            // Add markers for all warehouses
            if (warehousesData && warehousesData.length > 0) {
                warehousesData.forEach(function(warehouse) {
                    if (warehouse.latitude && warehouse.longitude) {
                        const lat = parseFloat(warehouse.latitude);
                        const lng = parseFloat(warehouse.longitude);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            const name = (warehouse.name || '').replace(/['"]/g, '');
                            const address = (warehouse.address || '').replace(/['"]/g, '');

                            const popupContent = '<b>' + name + '</b>' + (address ? '<br>' + address : '');

                            const marker = L.marker([lat, lng])
                                .addTo(warehouseMap)
                                .bindPopup(popupContent);
                            warehouseMarkers.push(marker);
                        }
                    }
                });
            }

            // Fit bounds to show all markers
            if (warehouseMarkers.length > 1) {
                const group = new L.featureGroup(warehouseMarkers);
                warehouseMap.fitBounds(group.getBounds().pad(0.1));
            } else if (warehouseMarkers.length === 1) {
                warehouseMap.setView([warehouseMarkers[0].getLatLng().lat, warehouseMarkers[0].getLatLng().lng], 13);
            }

            // Search location on map overview
            let searchMarker = null;
            const mapSearchInput = $('#map-location-search');
            const mapSearchResults = $('#map-search-results');
            let mapSearchTimeout;

            function searchMapLocation(query) {
                if (!query || query.length < 3) {
                    mapSearchResults.hide();
                    return;
                }

                mapSearchResults.html('<div class="list-group-item"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Mencari lokasi...</div>');
                mapSearchResults.show();

                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search',
                    method: 'GET',
                    data: {
                        q: query,
                        format: 'json',
                        addressdetails: 1,
                        limit: 5,
                        'accept-language': 'id',
                        countrycodes: 'id' // Limit to Indonesia
                    },
                    headers: {
                        'User-Agent': 'School Distribution System'
                    },
                    success: function(data) {
                        mapSearchResults.empty();
                        if (data && data.length > 0) {
                            data.forEach(function(place) {
                                const item = $('<a href="#" class="list-group-item list-group-item-action"></a>');
                                item.html('<strong>' + place.display_name + '</strong><br><small class="text-muted">Lat: ' + place.lat + ', Lng: ' + place.lon + '</small>');
                                item.on('click', function(e) {
                                    e.preventDefault();
                                    const lat = parseFloat(place.lat);
                                    const lng = parseFloat(place.lon);

                                    // Remove previous search marker
                                    if (searchMarker) {
                                        warehouseMap.removeLayer(searchMarker);
                                    }

                                    // Add new search marker with different color (red)
                                    searchMarker = L.marker([lat, lng], {
                                        icon: L.icon({
                                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                            iconSize: [25, 41],
                                            iconAnchor: [12, 41],
                                            popupAnchor: [1, -34],
                                            shadowSize: [41, 41]
                                        })
                                    }).addTo(warehouseMap);

                                    searchMarker.bindPopup('<b>' + place.display_name + '</b>').openPopup();

                                    // Zoom to location
                                    warehouseMap.setView([lat, lng], 13);

                                    mapSearchResults.hide();
                                    mapSearchInput.val('');
                                });
                                mapSearchResults.append(item);
                            });
                        } else {
                            mapSearchResults.html('<div class="list-group-item text-muted">Tidak ada hasil ditemukan</div>');
                        }
                    },
                    error: function() {
                        mapSearchResults.html('<div class="list-group-item text-danger">Error saat mencari lokasi</div>');
                    }
                });
            }

            // Search on button click
            $('#btn-search-map-location').on('click', function() {
                const query = mapSearchInput.val().trim();
                if (query) {
                    searchMapLocation(query);
                }
            });

            // Search on Enter key
            mapSearchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const query = $(this).val().trim();
                    if (query) {
                        searchMapLocation(query);
                    }
                }
            });

            // Search as you type (with debounce)
            mapSearchInput.on('input', function() {
                clearTimeout(mapSearchTimeout);
                const query = $(this).val().trim();
                if (query.length >= 3) {
                    mapSearchTimeout = setTimeout(function() {
                        searchMapLocation(query);
                    }, 500);
                } else {
                    mapSearchResults.hide();
                }
            });

            // Hide search results when clicking outside
            $(document).on('click.mapSearch', function(e) {
                if (!$(e.target).closest('#map-location-search, #map-search-results, #btn-search-map-location').length) {
                    mapSearchResults.hide();
                }
            });
        } catch (error) {
            console.error('Error initializing map:', error);
        }
    }

    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#warehouses-table')) {
        $('#warehouses-table').DataTable().destroy();
    }

    // Initialize DataTable
    const warehousesTable = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('warehouses.index') }}",
            dataSrc: function(json) {
                $('#warehouses-table tbody').find('.table-loading-overlay').remove();
                return json.data;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'recipient', name: 'recipient' },
            { data: 'address', name: 'address' },
            { data: 'location_info', name: 'location_info', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        scrollX: true,
        autoWidth: false,
        order: [[0, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; border-width: 0.3em;"></div><div class="mt-3" style="font-size: 16px; font-weight: 500;">Memuat data...</div>',
            lengthMenu: "Tampilkan _MENU_ entri",
            zeroRecords: "Tidak ada data yang ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
            infoFiltered: "(difilter dari _MAX_ total entri)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            loadingRecords: "Memuat data...",
            emptyTable: "Tidak ada data yang tersedia"
        }
    });
    
    // Add loading overlay to table body when loading starts
    warehousesTable.on('processing.dt', function(e, settings, processing) {
        if (processing) {
            const tbody = $('#warehouses-table tbody');
            if (tbody.find('.table-loading-overlay').length === 0) {
                tbody.append(`
                    <tr class="table-loading-overlay-row">
                        <td colspan="7" style="position: relative; height: 300px; padding: 0;">
                            <div class="table-loading-overlay">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="loading-text">Memuat data sekolah...</div>
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            $('#warehouses-table tbody').find('.table-loading-overlay-row').remove();
        }
    });

    // Load create form
    $('#btn-create-warehouse').on('click', function() {
        Modal.load('warehouseModal', "{{ route('warehouses.create') }}", 'Tambah Sekolah');
        // Show submit button for create modal
        $('#btn-submit-form').show();
    });

    // Handle show button click (delegated event)
    $(document).on('click', '.btn-show-warehouse', function(e) {
        e.preventDefault();
        const warehouseId = $(this).data('warehouse-id');
        Modal.load('warehouseModal', `/warehouses/${warehouseId}`, 'Detail Sekolah');
        // Hide submit button for show modal
        $('#btn-submit-form').hide();
    });

    // Handle edit button click (delegated event)
    $(document).on('click', '.btn-edit-warehouse', function(e) {
        e.preventDefault();
        const warehouseId = $(this).data('warehouse-id');
        Modal.load('warehouseModal', `/warehouses/${warehouseId}/edit`, 'Edit Sekolah');
        // Show submit button for edit modal
        $('#btn-submit-form').show();
    });

    // Handle form submission
    $('#btn-submit-form').on('click', function() {
        Form.submit('#warehouse-form', {
            success: function(response) {
                if (response.success) {
                    Modal.hide('warehouseModal');
                    Toast.success(response.message);
                    warehousesTable.ajax.reload(null, false);
                    // Reload page to update map
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            }
        });
    });

    // Handle delete button click (delegated event)
    $(document).on('click', '.btn-delete-warehouse', function(e) {
        e.preventDefault();
        const warehouseId = $(this).data('warehouse-id');
        const warehouseName = $(this).data('warehouse-name');

        if (!confirm(`Apakah Anda yakin ingin menghapus sekolah "${warehouseName}"?`)) {
            return;
        }

        $.ajax({
            url: `/warehouses/${warehouseId}`,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    warehousesTable.ajax.reload(null, false);
                    // Reload page to update map
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            },
            error: function() {
                Toast.error('Gagal menghapus sekolah.');
            }
        });
    });

    // Reset form when modal is hidden
    $('#warehouseModal').on('hidden.bs.modal', function() {
        Modal.clear('warehouseModal');
        // Show submit button by default
        $('#btn-submit-form').show();
        // Clean up location maps
        if (window.locationMap) {
            try {
                window.locationMap.remove();
            } catch(e) {}
            window.locationMap = null;
            window.locationMarker = null;
        }
        if (window.showLocationMap) {
            try {
                window.showLocationMap.remove();
            } catch(e) {}
            window.showLocationMap = null;
        }
    });

    // Initialize location map for warehouse form
    window.locationMap = null;
    window.locationMarker = null;

    // Initialize show location map
    window.showLocationMap = null;

    function initShowLocationMap(retryCount = 0) {
        // Check if map container exists
        if (!$('#show-location-map').length) {
            return false;
        }

        // Check if Leaflet is loaded, retry if not
        if (typeof L === 'undefined' || typeof L.map !== 'function') {
            if (retryCount < 20) { // Retry up to 2 seconds
                setTimeout(function() {
                    initShowLocationMap(retryCount + 1);
                }, 100);
                return false;
            } else {
                console.error('Leaflet library failed to load');
                return false;
            }
        }

        // Destroy existing map if any
        if (window.showLocationMap) {
            try {
                window.showLocationMap.remove();
            } catch(e) {}
            window.showLocationMap = null;
        }

        const dataEl = $('#show-location-map-data');
        if (!dataEl.length || !dataEl.data('lat') || !dataEl.data('lng')) {
            return false;
        }

        const lat = parseFloat(dataEl.data('lat'));
        const lng = parseFloat(dataEl.data('lng'));
        const name = dataEl.data('name') || '';
        const address = dataEl.data('address') || '';

        if (isNaN(lat) || isNaN(lng)) {
            return false;
        }

        try {
            // Initialize map
            window.showLocationMap = L.map('show-location-map', {
                zoomControl: true
            }).setView([lat, lng], 15);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(window.showLocationMap);

            // Add marker
            const marker = L.marker([lat, lng]).addTo(window.showLocationMap);
            // Escape HTML untuk popup content
            const safeName = (name || '').replace(/['"]/g, '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const safeAddress = (address || '').replace(/['"]/g, '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const popupContent = '<b>' + safeName + '</b>' + (safeAddress ? '<br>' + safeAddress : '');
            marker.bindPopup(popupContent).openPopup();

            // Invalidate size to ensure map renders correctly
            setTimeout(function() {
                if (window.showLocationMap) {
                    window.showLocationMap.invalidateSize();
                }
            }, 300);

            return true;
        } catch (error) {
            console.error('Error initializing show location map:', error);
            return false;
        }
    }

    function initLocationMap(retryCount = 0) {
        // Check if map container exists
        if (!$('#location-map').length) {
            return false;
        }

        // Check if Leaflet is loaded, retry if not
        if (typeof L === 'undefined' || typeof L.map !== 'function') {
            if (retryCount < 20) { // Retry up to 2 seconds
                setTimeout(function() {
                    initLocationMap(retryCount + 1);
                }, 100);
                return false;
            } else {
                console.error('Leaflet library failed to load');
                return false;
            }
        }

        // Destroy existing map if any
        if (window.locationMap) {
            try {
                window.locationMap.remove();
            } catch(e) {}
            window.locationMap = null;
            window.locationMarker = null;
        }

        const mapContainer = $('#location-map');
        const dataEl = $('#location-map-data');
        const latInput = $('#latitude');
        const lngInput = $('#longitude');

        // Get coordinates from data attribute or input
        let lat = -6.2088;
        let lng = 106.8456;

        if (dataEl.length && dataEl.data('lat') && dataEl.data('lng')) {
            lat = parseFloat(dataEl.data('lat'));
            lng = parseFloat(dataEl.data('lng'));
        } else if (latInput.length && lngInput.length) {
            lat = parseFloat(latInput.val()) || lat;
            lng = parseFloat(lngInput.val()) || lng;
        }

        try {
            // Initialize map
            window.locationMap = L.map('location-map', {
                zoomControl: true
            }).setView([lat, lng], 13);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(window.locationMap);

            // Add initial marker
            window.locationMarker = L.marker([lat, lng], {
                draggable: true
            }).addTo(window.locationMap);

            // Function to get address from coordinates (reverse geocoding)
            function getAddressFromCoordinates(lat, lng) {
                const addressInput = $('#address');
                if (!addressInput.length) return;

                // Show loading
                addressInput.prop('disabled', true);
                addressInput.val('Mengambil alamat...');

                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/reverse',
                    method: 'GET',
                    data: {
                        lat: lat,
                        lon: lng,
                        format: 'json',
                        addressdetails: 1,
                        'accept-language': 'id'
                    },
                    headers: {
                        'User-Agent': 'School Distribution System'
                    },
                    success: function(data) {
                        if (data && data.display_name) {
                            addressInput.val(data.display_name);
                        } else {
                            addressInput.val('');
                        }
                        addressInput.prop('disabled', false);
                    },
                    error: function() {
                        addressInput.val('');
                        addressInput.prop('disabled', false);
                    }
                });
            }

            // Update coordinates and get address when marker is dragged
            window.locationMarker.on('dragend', function(e) {
                const position = window.locationMarker.getLatLng();
                if (latInput.length) latInput.val(position.lat.toFixed(8));
                if (lngInput.length) lngInput.val(position.lng.toFixed(8));
                getAddressFromCoordinates(position.lat, position.lng);
            });

            // Update marker position and get address when map is clicked
            window.locationMap.on('click', function(e) {
                const latlng = e.latlng;
                window.locationMarker.setLatLng(latlng);
                if (latInput.length) latInput.val(latlng.lat.toFixed(8));
                if (lngInput.length) lngInput.val(latlng.lng.toFixed(8));
                getAddressFromCoordinates(latlng.lat, latlng.lng);
            });

            // Search location functionality
            const searchInput = $('#location-search');
            const searchResults = $('#search-results');
            let searchTimeout;

            function searchLocation(query) {
                if (!query || query.length < 3) {
                    searchResults.hide();
                    return;
                }

                searchResults.html('<div class="list-group-item"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Mencari lokasi...</div>');
                searchResults.show();

                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search',
                    method: 'GET',
                    data: {
                        q: query,
                        format: 'json',
                        addressdetails: 1,
                        limit: 5,
                        'accept-language': 'id',
                        countrycodes: 'id' // Limit to Indonesia
                    },
                    headers: {
                        'User-Agent': 'Warehouse Management System'
                    },
                    success: function(data) {
                        searchResults.empty();
                        if (data && data.length > 0) {
                            data.forEach(function(place) {
                                const item = $('<a href="#" class="list-group-item list-group-item-action"></a>');
                                item.html('<strong>' + place.display_name + '</strong><br><small class="text-muted">Lat: ' + place.lat + ', Lng: ' + place.lon + '</small>');
                                item.on('click', function(e) {
                                    e.preventDefault();
                                    const lat = parseFloat(place.lat);
                                    const lng = parseFloat(place.lon);

                                    window.locationMap.setView([lat, lng], 15);
                                    window.locationMarker.setLatLng([lat, lng]);
                                    if (latInput.length) latInput.val(lat.toFixed(8));
                                    if (lngInput.length) lngInput.val(lng.toFixed(8));

                                    const addressInput = $('#address');
                                    if (addressInput.length) {
                                        addressInput.val(place.display_name);
                                    }

                                    searchResults.hide();
                                    searchInput.val('');
                                });
                                searchResults.append(item);
                            });
                        } else {
                            searchResults.html('<div class="list-group-item text-muted">Tidak ada hasil ditemukan</div>');
                        }
                    },
                    error: function() {
                        searchResults.html('<div class="list-group-item text-danger">Error saat mencari lokasi</div>');
                    }
                });
            }

            // Setup search event handlers (using delegated events for AJAX loaded content)
            $(document).off('click', '#btn-search-location').on('click', '#btn-search-location', function() {
                const query = searchInput.val().trim();
                if (query) {
                    searchLocation(query);
                }
            });

            // Search on Enter key
            searchInput.off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const query = $(this).val().trim();
                    if (query) {
                        searchLocation(query);
                    }
                }
            });

            // Search as you type (with debounce)
            searchInput.off('input').on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();
                if (query.length >= 3) {
                    searchTimeout = setTimeout(function() {
                        searchLocation(query);
                    }, 500);
                } else {
                    searchResults.hide();
                }
            });

            // Hide search results when clicking outside
            $(document).off('click.searchLocation').on('click.searchLocation', function(e) {
                if (!$(e.target).closest('#location-search, #search-results, #btn-search-location').length) {
                    searchResults.hide();
                }
            });

            // Invalidate size to ensure map renders correctly
            setTimeout(function() {
                if (window.locationMap) {
                    window.locationMap.invalidateSize();
                }
            }, 300);

            // Get initial address if coordinates exist and address is empty
            const addressInput = $('#address');
            if (lat && lng && addressInput.length && (!addressInput.val() || addressInput.val().trim() === '')) {
                setTimeout(function() {
                    getAddressFromCoordinates(lat, lng);
                }, 500);
            }

            return true;
        } catch (error) {
            console.error('Error initializing location map:', error);
            return false;
        }
    }

    // Function to initialize location selects (moved from form.blade.php)
    function initializeLocationSelects() {
        console.log('🔵 INITIALIZE LOCATION SELECTS CALLED');
        
        if (window.locationSelectsInitialized) {
            console.log('⚠ Already initialized, skipping...');
            return;
        }
        
        const provinceSelect = $('#province_select');
        const citySelect = $('#city_select');
        const districtSelect = $('#district_select');
        const villageSelect = $('#village_select');
        const provinceInput = $('#province');
        const cityInput = $('#city');
        const districtInput = $('#district');
        const villageInput = $('#village');
        
        if (provinceSelect.length === 0) {
            console.log('⚠ Province select not found, will retry...');
            return false;
        }
        
        console.log('✓ Form elements found, initializing...');
        console.log('Province options count:', provinceSelect.find('option').length);
        console.log('Province options:', provinceSelect.find('option').map(function() { return $(this).text(); }).get());
        
        const hasSelect2 = typeof $.fn.select2 !== 'undefined';
        console.log('Select2 available:', hasSelect2);
        
        // Initialize Select2 if available
        if (hasSelect2) {
            try {
                // Destroy existing Select2 instances if any
                if (provinceSelect.hasClass('select2-hidden-accessible')) {
                    provinceSelect.select2('destroy');
                }
                if (citySelect.hasClass('select2-hidden-accessible')) {
                    citySelect.select2('destroy');
                }
                if (districtSelect.hasClass('select2-hidden-accessible')) {
                    districtSelect.select2('destroy');
                }
                if (villageSelect.hasClass('select2-hidden-accessible')) {
                    villageSelect.select2('destroy');
                }
                
                // Initialize Select2
                provinceSelect.select2({ 
                    placeholder: '-- Pilih Provinsi --', 
                    allowClear: false,
                    dropdownParent: $('#warehouseModal'), // Important: attach to modal
                    language: {
                        noResults: function() { return "Tidak ada hasil"; },
                        searching: function() { return "Mencari..."; }
                    }
                });
                citySelect.select2({ 
                    placeholder: '-- Pilih Kota/Kabupaten --', 
                    allowClear: false,
                    dropdownParent: $('#warehouseModal'),
                    language: {
                        noResults: function() { return "Tidak ada hasil"; },
                        searching: function() { return "Mencari..."; }
                    }
                });
                districtSelect.select2({ 
                    placeholder: '-- Pilih Kecamatan (Opsional) --', 
                    allowClear: true,
                    dropdownParent: $('#warehouseModal'),
                    language: {
                        noResults: function() { return "Tidak ada hasil"; },
                        searching: function() { return "Mencari..."; }
                    }
                });
                villageSelect.select2({ 
                    placeholder: '-- Pilih Desa/Kelurahan (Opsional) --', 
                    allowClear: true,
                    dropdownParent: $('#warehouseModal'),
                    language: {
                        noResults: function() { return "Tidak ada hasil"; },
                        searching: function() { return "Mencari..."; }
                    }
                });
                console.log('✓ Select2 initialized with dropdownParent');
            } catch (e) {
                console.error('Error initializing Select2:', e);
            }
        }
        
        // Load cities function
        function loadCities(provinceId) {
            console.log('🚀 LOAD CITIES:', provinceId);
            if (!provinceId) return;
            
            citySelect.prop('disabled', true);
            if (hasSelect2) {
                citySelect.select2('destroy').empty().append('<option value="">Memuat...</option>');
                citySelect.select2({ 
                    placeholder: 'Memuat...',
                    dropdownParent: $('#warehouseModal')
                });
            } else {
                citySelect.empty().append('<option value="">Memuat...</option>');
            }
            
            $.ajax({
                url: '{{ route("api.cities") }}',
                method: 'GET',
                data: { province_id: provinceId },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Cities loaded:', response);
                    let cities = Array.isArray(response) ? response : (response.data || []);
                    
                    if (hasSelect2) citySelect.select2('destroy');
                    citySelect.empty().append('<option value="">-- Pilih Kota/Kabupaten --</option>');
                    
                    cities.forEach(function(city) {
                        if (city && city.id && city.name) {
                            citySelect.append($('<option>').val(city.id).attr('data-name', city.name).text(city.name));
                        }
                    });
                    
                    citySelect.prop('disabled', false);
                    if (hasSelect2) {
                        citySelect.select2({ 
                            placeholder: '-- Pilih Kota/Kabupaten --', 
                            allowClear: false,
                            dropdownParent: $('#warehouseModal'),
                            language: {
                                noResults: function() { return "Tidak ada hasil"; },
                                searching: function() { return "Mencari..."; }
                            }
                        });
                    }
                    console.log('✓ City dropdown populated');
                    
                    // If edit mode and city exists, select it and load districts
                    const currentCityName = cityInput.val();
                    if (currentCityName) {
                        console.log('🔵 Edit mode: Looking for city:', currentCityName);
                        const matchingCity = cities.find(function(city) {
                            return city.name === currentCityName || city.name.trim() === currentCityName.trim();
                        });
                        if (matchingCity) {
                            console.log('✅ Found matching city:', matchingCity);
                            citySelect.val(matchingCity.id);
                            if (hasSelect2) {
                                citySelect.trigger('change.select2');
                            }
                            // Trigger change to load districts
                            setTimeout(function() {
                                citySelect.trigger('change.location');
                            }, 100);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error loading cities:', error, xhr);
                    citySelect.empty().append('<option value="">Error memuat kota</option>');
                }
            });
        }
        
        // Load districts by city
        function loadDistricts(cityId) {
            console.log('🚀 LOAD DISTRICTS:', cityId);
            if (!cityId) {
                districtSelect.empty().append('<option value="">-- Pilih Kota/Kabupaten terlebih dahulu --</option>');
                districtSelect.prop('disabled', true);
                if (hasSelect2) {
                    districtSelect.select2('destroy');
                    districtSelect.select2({
                        placeholder: '-- Pilih Kota/Kabupaten terlebih dahulu --',
                        allowClear: true,
                        dropdownParent: $('#warehouseModal')
                    });
                }
                villageSelect.empty().append('<option value="">-- Pilih Kecamatan terlebih dahulu --</option>');
                villageSelect.prop('disabled', true);
                if (hasSelect2) {
                    villageSelect.select2('destroy');
                    villageSelect.select2({
                        placeholder: '-- Pilih Kecamatan terlebih dahulu --',
                        allowClear: true,
                        dropdownParent: $('#warehouseModal')
                    });
                }
                return;
            }
            
            districtSelect.prop('disabled', true);
            if (hasSelect2) {
                districtSelect.select2('destroy').empty().append('<option value="">Memuat...</option>');
                districtSelect.select2({
                    placeholder: 'Memuat kecamatan...',
                    allowClear: true,
                    dropdownParent: $('#warehouseModal')
                });
            } else {
                districtSelect.empty().append('<option value="">Memuat...</option>');
            }
            
            $.ajax({
                url: '{{ route("api.districts") }}',
                method: 'GET',
                data: { city_id: cityId },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Districts loaded:', response);
                    let districts = Array.isArray(response) ? response : (response.data || []);
                    
                    if (hasSelect2) districtSelect.select2('destroy');
                    districtSelect.empty().append('<option value="">-- Pilih Kecamatan (Opsional) --</option>');
                    
                    districts.forEach(function(district) {
                        if (district && district.id && district.name) {
                            districtSelect.append($('<option>').val(district.id).attr('data-name', district.name).text(district.name));
                        }
                    });
                    
                    districtSelect.prop('disabled', false);
                    if (hasSelect2) {
                        districtSelect.select2({
                            placeholder: '-- Pilih Kecamatan (Opsional) --',
                            allowClear: true,
                            dropdownParent: $('#warehouseModal'),
                            language: {
                                noResults: function() { return "Tidak ada hasil"; },
                                searching: function() { return "Mencari..."; }
                            }
                        });
                    }
                    console.log('✓ District dropdown populated');
                    
                    // If edit mode and district exists, select it and load villages
                    const currentDistrictName = districtInput.val();
                    if (currentDistrictName) {
                        console.log('🔵 Edit mode: Looking for district:', currentDistrictName);
                        const matchingDistrict = districts.find(function(district) {
                            return district.name === currentDistrictName || district.name.trim() === currentDistrictName.trim();
                        });
                        if (matchingDistrict) {
                            console.log('✅ Found matching district:', matchingDistrict);
                            districtSelect.val(matchingDistrict.id);
                            if (hasSelect2) {
                                districtSelect.trigger('change.select2');
                            }
                            // Trigger change to load villages
                            setTimeout(function() {
                                districtSelect.trigger('change.location');
                            }, 100);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error loading districts:', error, xhr);
                    districtSelect.empty().append('<option value="">Error memuat kecamatan</option>');
                    districtSelect.prop('disabled', true);
                }
            });
        }
        
        // Load villages by district
        function loadVillages(districtId) {
            console.log('🚀 LOAD VILLAGES:', districtId);
            if (!districtId) {
                villageSelect.empty().append('<option value="">-- Pilih Kecamatan terlebih dahulu --</option>');
                villageSelect.prop('disabled', true);
                if (hasSelect2) {
                    villageSelect.select2('destroy');
                    villageSelect.select2({
                        placeholder: '-- Pilih Kecamatan terlebih dahulu --',
                        allowClear: true,
                        dropdownParent: $('#warehouseModal')
                    });
                }
                return;
            }
            
            villageSelect.prop('disabled', true);
            if (hasSelect2) {
                villageSelect.select2('destroy').empty().append('<option value="">Memuat...</option>');
                villageSelect.select2({
                    placeholder: 'Memuat desa...',
                    allowClear: true,
                    dropdownParent: $('#warehouseModal')
                });
            } else {
                villageSelect.empty().append('<option value="">Memuat...</option>');
            }
            
            $.ajax({
                url: '{{ route("api.villages") }}',
                method: 'GET',
                data: { district_id: districtId },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Villages loaded:', response);
                    let villages = Array.isArray(response) ? response : (response.data || []);
                    
                    if (hasSelect2) villageSelect.select2('destroy');
                    villageSelect.empty().append('<option value="">-- Pilih Desa/Kelurahan (Opsional) --</option>');
                    
                    villages.forEach(function(village) {
                        if (village && village.id && village.name) {
                            villageSelect.append($('<option>').val(village.id).attr('data-name', village.name).text(village.name));
                        }
                    });
                    
                    villageSelect.prop('disabled', false);
                    if (hasSelect2) {
                        villageSelect.select2({
                            placeholder: '-- Pilih Desa/Kelurahan (Opsional) --',
                            allowClear: true,
                            dropdownParent: $('#warehouseModal'),
                            language: {
                                noResults: function() { return "Tidak ada hasil"; },
                                searching: function() { return "Mencari..."; }
                            }
                        });
                    }
                    console.log('✓ Village dropdown populated');
                    
                    // If edit mode and village exists, select it
                    const currentVillageName = villageInput.val();
                    if (currentVillageName) {
                        console.log('🔵 Edit mode: Looking for village:', currentVillageName);
                        const matchingVillage = villages.find(function(village) {
                            return village.name === currentVillageName || village.name.trim() === currentVillageName.trim();
                        });
                        if (matchingVillage) {
                            console.log('✅ Found matching village:', matchingVillage);
                            villageSelect.val(matchingVillage.id);
                            if (hasSelect2) {
                                villageSelect.trigger('change.select2');
                            }
                            villageSelect.trigger('change.location');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error loading villages:', error, xhr);
                    villageSelect.empty().append('<option value="">Error memuat desa</option>');
                    villageSelect.prop('disabled', true);
                }
            });
        }
        
        // Event handlers - Standard change event (works with or without Select2)
        provinceSelect.off('change.location').on('change.location', function() {
            const provinceId = $(this).val();
            console.log('🔵 PROVINCE CHANGED:', provinceId);
            if (provinceId) {
                const selectedOption = $(this).find('option:selected');
                const provinceName = selectedOption.data('name') || selectedOption.text().trim();
                console.log('Province Name:', provinceName);
                provinceInput.val(provinceName);
                
                // If Select2 is active, ensure it displays the selected value
                if (hasSelect2) {
                    // Small delay to ensure Select2 processes the change
                    setTimeout(function() {
                        provinceSelect.trigger('change.select2');
                    }, 10);
                }
                
                loadCities(provinceId);
            } else {
                provinceInput.val('');
            }
        });
        
        if (hasSelect2) {
            provinceSelect.off('select2:select.location').on('select2:select.location', function(e) {
                const provinceId = e.params.data.id;
                console.log('🔵 PROVINCE SELECT2 SELECTED:', provinceId);
                
                // Get province name from option element
                const optionElement = $(e.params.data.element);
                const provinceName = optionElement.data('name') || optionElement.text().trim() || e.params.data.text.trim();
                
                console.log('Province Name from Select2:', provinceName);
                provinceInput.val(provinceName);
                
                // Ensure the select value is set
                provinceSelect.val(provinceId);
                
                // Force Select2 to update its display - use multiple methods to ensure it works
                setTimeout(function() {
                    // Method 1: Trigger change.select2
                    provinceSelect.trigger('change.select2');
                    
                    // Method 2: Re-initialize Select2 if needed (only if display is still wrong)
                    const select2Container = provinceSelect.next('.select2-container');
                    if (select2Container.length > 0) {
                        const selectedText = provinceSelect.find('option:selected').text().trim();
                        const select2Selection = select2Container.find('.select2-selection__rendered');
                        if (select2Selection.length > 0 && select2Selection.text().trim() !== selectedText) {
                            console.log('⚠ Select2 display mismatch, forcing update...');
                            provinceSelect.select2('destroy');
                            provinceSelect.select2({ 
                                placeholder: '-- Pilih Provinsi --', 
                                allowClear: false,
                                dropdownParent: $('#warehouseModal'),
                                language: {
                                    noResults: function() { return "Tidak ada hasil"; },
                                    searching: function() { return "Mencari..."; }
                                }
                            });
                            provinceSelect.val(provinceId).trigger('change.select2');
                        }
                    }
                    
                    console.log('✓ Select2 display updated for province:', provinceName);
                }, 50);
                
                loadCities(provinceId);
            });
        }
        
        // City/Kabupaten change handler
        citySelect.off('change.location').on('change.location', function() {
            const cityId = $(this).val();
            console.log('🔵 CITY CHANGED:', cityId);
            if (cityId) {
                const selectedOption = $(this).find('option:selected');
                const cityName = selectedOption.data('name') || selectedOption.text().trim();
                console.log('City Name:', cityName);
                cityInput.val(cityName);
                
                if (hasSelect2) {
                    setTimeout(function() {
                        citySelect.trigger('change.select2');
                    }, 10);
                }
                
                loadDistricts(cityId);
            } else {
                cityInput.val('');
                loadDistricts(null);
            }
        });
        
        if (hasSelect2) {
            citySelect.off('select2:select.location').on('select2:select.location', function(e) {
                const cityId = e.params.data.id;
                console.log('🔵 CITY SELECT2 SELECTED:', cityId);
                
                const optionElement = $(e.params.data.element);
                const cityName = optionElement.data('name') || optionElement.text().trim() || e.params.data.text.trim();
                
                console.log('City Name from Select2:', cityName);
                cityInput.val(cityName);
                
                citySelect.val(cityId);
                
                setTimeout(function() {
                    citySelect.trigger('change.select2');
                }, 10);
                
                loadDistricts(cityId);
            });
        }
        
        // District change handler
        districtSelect.off('change.location').on('change.location', function() {
            const districtId = $(this).val();
            console.log('🔵 DISTRICT CHANGED:', districtId);
            if (districtId) {
                const selectedOption = $(this).find('option:selected');
                const districtName = selectedOption.data('name') || selectedOption.text().trim();
                districtInput.val(districtName);
                
                if (hasSelect2) {
                    setTimeout(function() {
                        districtSelect.trigger('change.select2');
                    }, 10);
                }
                
                loadVillages(districtId);
            } else {
                districtInput.val('');
                loadVillages(null);
            }
        });
        
        if (hasSelect2) {
            districtSelect.off('select2:select.location').on('select2:select.location', function(e) {
                const districtId = e.params.data.id;
                console.log('🔵 DISTRICT SELECT2 SELECTED:', districtId);
                
                const optionElement = $(e.params.data.element);
                const districtName = optionElement.data('name') || optionElement.text().trim() || e.params.data.text.trim();
                
                districtInput.val(districtName);
                districtSelect.val(districtId);
                
                setTimeout(function() {
                    districtSelect.trigger('change.select2');
                }, 10);
                
                loadVillages(districtId);
            });
        }
        
        // Village change handler
        villageSelect.off('change.location').on('change.location', function() {
            const selectedOption = $(this).find('option:selected');
            const villageName = selectedOption.data('name') || selectedOption.text().trim();
            villageInput.val(villageName || '');
            console.log('🔵 VILLAGE CHANGED:', villageName);
        });
        
        if (hasSelect2) {
            villageSelect.off('select2:select.location').on('select2:select.location', function(e) {
                const optionElement = $(e.params.data.element);
                const villageName = optionElement.data('name') || optionElement.text().trim() || e.params.data.text.trim();
                villageInput.val(villageName);
                console.log('🔵 VILLAGE SELECT2 SELECTED:', villageName);
            });
        }
        
        // Check if province already selected (edit mode)
        const currentProvinceId = provinceSelect.val();
        const currentProvinceName = provinceInput.val();
        if (currentProvinceId && currentProvinceName) {
            console.log('→ Edit mode: Province already selected:', currentProvinceId, currentProvinceName);
            if (hasSelect2) {
                // Force Select2 to update display
                setTimeout(function() {
                    provinceSelect.trigger('change.select2');
                }, 100);
            }
            // Load cities and then select the matching city
            loadCities(currentProvinceId);
        }
        
        window.locationSelectsInitialized = true;
        console.log('✅ Location selects initialized');
        return true;
    }
    
    // Initialize when modal is shown
    $('#warehouseModal').on('shown.bs.modal', function() {
        console.log('🔵 MODAL SHOWN');
        window.locationSelectsInitialized = false; // Reset flag
        
        setTimeout(function() {
            console.log('🔵 Checking for form...');
            console.log('Province select count:', $('#province_select').length);
            if ($('#province_select').length > 0) {
                console.log('🔵 Form found, initializing...');
                initializeLocationSelects();
            } else {
                console.log('⚠ Form not found yet');
            }
            
            // Initialize maps
            if ($('#location-map').length && !window.locationMap) {
                console.log('🔵 Initializing location map...');
                initLocationMap();
            }
            if ($('#show-location-map').length && !window.showLocationMap) {
                console.log('🔵 Initializing show location map...');
                initShowLocationMap();
            }
        }, 600);
    });
    
    // Also listen for content changes via MutationObserver
    $(document).ready(function() {
        const modalBody = document.getElementById('warehouseModalBody');
        if (modalBody) {
            console.log('🔵 Setting up MutationObserver for modal body');
            const contentObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        console.log('🔵 Content added to modal');
                        setTimeout(function() {
                            if ($('#province_select').length > 0 && !window.locationSelectsInitialized) {
                                console.log('🔵 Form detected via MutationObserver, initializing...');
                                initializeLocationSelects();
                            }
                        }, 200);
                    }
                });
            });
            contentObserver.observe(modalBody, { childList: true, subtree: true });
            console.log('✓ MutationObserver set up');
        } else {
            console.log('⚠ Modal body not found');
        }
    });

    // Also listen for when content is loaded via AJAX using MutationObserver
    let mapObserver = null;
    const modalBody = document.getElementById('warehouseModalBody');
    if (modalBody) {
        mapObserver = new MutationObserver(function(mutations) {
            // Check if location-map or show-location-map was added
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const $node = $(node);
                        // Check for form location map
                        if ($node.find('#location-map').length || $node.is('#location-map')) {
                            setTimeout(function() {
                                if ($('#location-map').length && !window.locationMap) {
                                    initLocationMap();
                                }
                            }, 400);
                        }
                        // Check for show location map
                        if ($node.find('#show-location-map').length || $node.is('#show-location-map')) {
                            setTimeout(function() {
                                if ($('#show-location-map').length && !window.showLocationMap) {
                                    initShowLocationMap();
                                }
                            }, 500);
                        }
                        // Also check for show-location-map-data to ensure data is loaded
                        if ($node.find('#show-location-map-data').length || $node.is('#show-location-map-data')) {
                            setTimeout(function() {
                                if ($('#show-location-map').length && $('#show-location-map-data').length && !window.showLocationMap) {
                                    initShowLocationMap();
                                }
                            }, 500);
                        }
                    }
                });
            });
        });

        // Start observing when modal is shown
        $('#warehouseModal').on('show.bs.modal', function() {
            mapObserver.observe(modalBody, {
                childList: true,
                subtree: true
            });
        });

        // Stop observing when modal is hidden
        $('#warehouseModal').on('hidden.bs.modal', function() {
            if (mapObserver) {
                mapObserver.disconnect();
            }
        });
    }
});
</script>
@endpush
@endsection

