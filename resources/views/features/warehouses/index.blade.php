@extends('layouts.app')

@section('title', 'Manajemen Gudang')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
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
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Manajemen Gudang"
    :breadcrumb-title="'Manajemen Gudang'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Map Overview -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Peta Lokasi Gudang</h4>
            <div class="position-relative" style="width: 400px; max-width: 100%;">
                <div class="input-group">
                    <input
                        type="text"
                        class="form-control"
                        id="map-location-search"
                        placeholder="Cari lokasi di peta (contoh: Jakarta, Bandung)..."
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
                    <h4 class="card-title">Daftar Gudang</h4>
                    <p class="card-subtitle mb-3">
                        Kelola gudang dan lokasinya. Anda dapat membuat, mengedit, dan menghapus gudang dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-warehouse">
                    <i class="ti ti-plus me-1"></i> Tambah Gudang Baru
                </button>
            </div>
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table id="warehouses-table" class="table table-striped table-bordered align-middle" style="width: 100%; min-width: 800px;">
                    <thead>
                        <tr>
                            <th style="min-width: 60px;">ID</th>
                            <th style="min-width: 150px;">Nama</th>
                            <th style="min-width: 200px;">Alamat</th>
                            <th style="min-width: 150px;">Koordinat</th>
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
    title="Tambah Gudang Baru"
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
        ajax: "{{ route('warehouses.index') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'address', name: 'address' },
            { data: 'coordinates', name: 'coordinates', orderable: false, searchable: false },
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
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            lengthMenu: "Show _MENU_ entries",
            zeroRecords: "No matching records found",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            search: "Search:",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Load create form
    $('#btn-create-warehouse').on('click', function() {
        Modal.load('warehouseModal', "{{ route('warehouses.create') }}", 'Tambah Gudang Baru');
        // Show submit button for create modal
        $('#btn-submit-form').show();
    });

    // Handle show button click (delegated event)
    $(document).on('click', '.btn-show-warehouse', function(e) {
        e.preventDefault();
        const warehouseId = $(this).data('warehouse-id');
        Modal.load('warehouseModal', `/warehouses/${warehouseId}`, 'Detail Gudang');
        // Hide submit button for show modal
        $('#btn-submit-form').hide();
    });

    // Handle edit button click (delegated event)
    $(document).on('click', '.btn-edit-warehouse', function(e) {
        e.preventDefault();
        const warehouseId = $(this).data('warehouse-id');
        Modal.load('warehouseModal', `/warehouses/${warehouseId}/edit`, 'Edit Gudang');
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

        if (!confirm(`Apakah Anda yakin ingin menghapus gudang "${warehouseName}"?`)) {
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
                Toast.error('Gagal menghapus gudang.');
            }
        });
    });

    // Reset form when modal is hidden
    $('#warehouseModal').on('hidden.bs.modal', function() {
        Modal.clear('warehouseModal');
        // Show submit button by default
        $('#btn-submit-form').show();
        // Clean up location map
        if (window.locationMap) {
            try {
                window.locationMap.remove();
            } catch(e) {}
            window.locationMap = null;
            window.locationMarker = null;
        }
    });

    // Initialize location map for warehouse form
    window.locationMap = null;
    window.locationMarker = null;

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
                        'User-Agent': 'Warehouse Management System'
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

    // Initialize map when modal is shown and content is loaded
    $('#warehouseModal').on('shown.bs.modal', function() {
        // Wait for content to be loaded via AJAX
        setTimeout(function() {
            if ($('#location-map').length && !window.locationMap) {
                initLocationMap();
            }
        }, 600);
    });

    // Also listen for when content is loaded via AJAX using MutationObserver
    let mapObserver = null;
    const modalBody = document.getElementById('warehouseModalBody');
    if (modalBody) {
        mapObserver = new MutationObserver(function(mutations) {
            // Check if location-map was added
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if ($(node).find('#location-map').length || $(node).is('#location-map')) {
                            setTimeout(function() {
                                if ($('#location-map').length && !window.locationMap) {
                                    initLocationMap();
                                }
                            }, 400);
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

