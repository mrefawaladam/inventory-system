@extends('layouts.app')

@section('title', 'Manajemen Lokasi')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<style>
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
    #locations-table tbody {
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
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Manajemen Lokasi"
    :breadcrumb-title="'Manajemen Lokasi'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="filter-warehouse" class="form-label">Filter Sekolah</label>
                <select id="filter-warehouse" class="form-select">
                    <option value="">Semua Sekolah</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="filter-delivery-status" class="form-label">Status Pengiriman</label>
                <select id="filter-delivery-status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="delivered">Sudah Dikirim</option>
                    <option value="pending">Belum Dikirim</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-secondary w-100" id="btn-reset-filters">
                    <i class="ti ti-refresh me-1"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Lokasi</h4>
                    <p class="card-subtitle mb-3">
                        Kelola struktur lokasi gudang (Area → Rak → Tempat). Anda dapat membuat, mengedit, dan menghapus lokasi dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-location">
                    <i class="ti ti-plus me-1"></i> Tambah Lokasi Baru
                </button>
            </div>
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table id="locations-table" class="table table-striped table-bordered align-middle" style="width: 100%; min-width: 1000px;">
                    <thead>
                        <tr>
                            <th style="min-width: 60px;">ID</th>
                            <th style="min-width: 120px;">Kode</th>
                            <th style="min-width: 140px;">Status Pengiriman</th>
                            <th style="min-width: 150px;">Sekolah</th>
                            <th style="min-width: 120px;">Parent</th>
                            <th style="min-width: 200px;">Path Lengkap</th>
                            <th style="min-width: 100px;">Jumlah Siswa yang Menerima</th>
                            <th style="min-width: 120px;">Tanggal Dibuat</th>
                            <th style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Location Modal -->
<x-ui.modal
    id="locationModal"
    title="Tambah Lokasi Baru"
    size="lg"
    content-id="locationModalBody"
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
<script>
// Wait for Select2 to be available
(function() {
    var checkSelect2 = function(attempts) {
        attempts = attempts || 0;
        if (typeof $.fn.select2 !== 'undefined') {
            initLocationsPage();
        } else if (attempts < 20) {
            setTimeout(function() {
                checkSelect2(attempts + 1);
            }, 100);
        } else {
            // Try to load Select2 manually as fallback
            var script = document.createElement('script');
            script.src = "{{ asset('assets/libs/select2/dist/js/select2.min.js') }}";
            script.onload = function() {
                initLocationsPage();
            };
            document.head.appendChild(script);
        }
    };
    
    function initLocationsPage() {
$(document).ready(function() {
    // Initialize DataTable
    let locationsTable = $('#locations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('locations.index') }}",
            type: 'GET',
            data: function(d) {
                // Ambil nilai filter
                var warehouseId = $('#filter-warehouse').val();
                var deliveryStatus = $('#filter-delivery-status').val();
                
                // Set parameter ke object d
                d.warehouse_id = warehouseId || '';
                d.delivery_status = deliveryStatus || '';
                
                // Debug logging (hapus setelah fix)
                console.log('DataTables AJAX Request:', {
                    warehouse_id: d.warehouse_id,
                    delivery_status: d.delivery_status,
                    full_data: d
                });
            },
            dataSrc: function(json) {
                // Remove loading overlay when data is loaded
                $('#locations-table tbody').find('.table-loading-overlay').remove();
                return json.data;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'code', name: 'code' },
            { data: 'delivery_status', name: 'delivery_status', orderable: false, searchable: false },
            { data: 'warehouse_name', name: 'warehouse.name' },
            { data: 'parent_code', name: 'parent.code' },
            { data: 'full_path', name: 'full_path', orderable: false },
            { data: 'capacity', name: 'capacity' },
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
    locationsTable.on('processing.dt', function(e, settings, processing) {
        if (processing) {
            // Add loading overlay to tbody
            const tbody = $('#locations-table tbody');
            if (tbody.find('.table-loading-overlay').length === 0) {
                tbody.append(`
                    <tr class="table-loading-overlay-row">
                        <td colspan="9" style="position: relative; height: 300px; padding: 0;">
                            <div class="table-loading-overlay">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="loading-text">Memuat data lokasi...</div>
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            // Remove loading overlay when done
            $('#locations-table tbody').find('.table-loading-overlay-row').remove();
        }
    });

    // Filter handlers
    $('#filter-warehouse, #filter-delivery-status').on('change', function() {
        locationsTable.ajax.reload();
    });

    $('#btn-reset-filters').on('click', function() {
        $('#filter-warehouse').val('');
        $('#filter-delivery-status').val('');
        locationsTable.ajax.reload();
    });

    // Load create form
    $('#btn-create-location').on('click', function() {
        Modal.load('locationModal', "{{ route('locations.create') }}", 'Tambah Lokasi Baru').then(function() {
        $('#btn-submit-form').show();
            // Initialize form handlers after create form is loaded
            setTimeout(function() {
                initLocationFormHandlers();
            }, 200);
        });
    });

    // Handle show button click (delegated event)
    $(document).on('click', '.btn-show-location', function(e) {
        e.preventDefault();
        const locationId = $(this).data('location-id');
        Modal.load('locationModal', `/locations/${locationId}`, 'Detail Lokasi');
        $('#btn-submit-form').hide();
    });

    // Handle edit button click (delegated event)
    $(document).on('click', '.btn-edit-location', function(e) {
        e.preventDefault();
        const locationId = $(this).data('location-id');
        Modal.load('locationModal', `/locations/${locationId}/edit`, 'Edit Lokasi').then(function() {
        $('#btn-submit-form').show();
            // Initialize form handlers after edit form is loaded
            setTimeout(function() {
                initLocationFormHandlers();
            }, 200);
        });
    });

    // Handle form submission
    $(document).on('click', '#btn-submit-form', function() {
        // Ensure parent_id is set correctly before submit
        const type = $('#type').val();
        const parentZoneId = $('#parent_zone_id').val();
        const parentRackId = $('#parent_rack_id').val();
        const parentId = $('#parent_id').val();
        
        // Validate and set parent_id based on type
        if (type === 'RACK') {
            if (!parentZoneId) {
                Toast.error('Silakan pilih Area terlebih dahulu sebelum menyimpan Rak.');
                $('#parent_zone_id').addClass('is-invalid');
                $('#parent_zone_id').focus();
                return false;
            }
            $('#parent_id').val(parentZoneId);
            $('#parent_zone_id').removeClass('is-invalid');
        } else if (type === 'SLOT') {
            if (!parentZoneId) {
                Toast.error('Silakan pilih Area terlebih dahulu sebelum menyimpan Tempat.');
                $('#parent_zone_id').addClass('is-invalid');
                $('#parent_zone_id').focus();
                return false;
            }
            if (!parentRackId) {
                Toast.error('Silakan pilih Rak terlebih dahulu sebelum menyimpan Tempat.');
                $('#parent_rack_id').addClass('is-invalid');
                $('#parent_rack_id').focus();
                return false;
            }
            $('#parent_id').val(parentRackId);
            $('#parent_zone_id').removeClass('is-invalid');
            $('#parent_rack_id').removeClass('is-invalid');
        }
        
        // Final check: ensure parent_id is set for RACK and SLOT
        if ((type === 'RACK' || type === 'SLOT') && !$('#parent_id').val()) {
            Toast.error('Parent harus dipilih untuk tipe lokasi ini.');
            return false;
        }
        
        Form.submit('#location-form', {
            success: function(response) {
                if (response.success) {
                    Modal.hide('locationModal');
                    Toast.success(response.message);
                    locationsTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan lokasi.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Handle validation errors
                    const errors = xhr.responseJSON.errors;
                    const firstError = Object.values(errors)[0];
                    if (Array.isArray(firstError) && firstError.length > 0) {
                        errorMessage = firstError[0];
                    }
                }
                
                Toast.error(errorMessage);
            }
        });
    });

    // Handle delete button click (delegated event)
    $(document).on('click', '.btn-delete-location', function(e) {
        e.preventDefault();
        const locationId = $(this).data('location-id');
        const locationCode = $(this).data('location-code');

        if (!confirm(`Apakah Anda yakin ingin menghapus lokasi "${locationCode}"?`)) {
            return;
        }

        $.ajax({
            url: `/locations/${locationId}`,
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
                    locationsTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Gagal menghapus lokasi.';
                Toast.error(message);
            }
        });
    });

    // Reset form when modal is hidden
    $('#locationModal').on('hidden.bs.modal', function() {
        Modal.clear('locationModal');
        $('#btn-submit-form').show();
    });

    // Location form handlers (using event delegation for dynamically loaded content)
    const locationsGetByWarehouseUrl = "{{ route('locations.get-by-warehouse') }}";

    // Function to load zones
    function loadZones(warehouseId, selectedId = null) {
        const parentZoneSelect = $('#parent_zone_id');
        if (!warehouseId) {
            parentZoneSelect.html('<option value="">Pilih Area</option>');
            if (parentZoneSelect.hasClass('select2-hidden-accessible')) {
                parentZoneSelect.select2('destroy');
            }
            return;
        }

        $.ajax({
            url: locationsGetByWarehouseUrl,
            method: 'GET',
            data: {
                warehouse_id: warehouseId,
                type: 'ZONE'
            },
            success: function(zones) {
                let html = '<option value="">Pilih Area</option>';
                zones.forEach(function(zone) {
                    const selected = selectedId == zone.id ? 'selected' : '';
                    html += `<option value="${zone.id}" ${selected}>${zone.code} - ${zone.description || ''}</option>`;
                });
                parentZoneSelect.html(html);
                
                // Re-initialize Select2 if it exists
                if (typeof $.fn.select2 !== 'undefined') {
                    if (parentZoneSelect.hasClass('select2-hidden-accessible')) {
                        parentZoneSelect.select2('destroy');
                    }
                    parentZoneSelect.select2({
                        placeholder: 'Pilih Area',
                        allowClear: true,
                        dropdownParent: $('#locationModal'),
                        width: '100%',
                        language: {
                            noResults: function() {
                                return "Tidak ada hasil";
                            },
                            searching: function() {
                                return "Mencari...";
                            }
                        }
                    });
                    if (selectedId) {
                        parentZoneSelect.val(selectedId).trigger('change');
                    }
                }
            }
        });
    }

    // Function to load racks
    function loadRacks(zoneId, selectedId = null) {
        const parentRackSelect = $('#parent_rack_id');
        if (!zoneId) {
            parentRackSelect.html('<option value="">Pilih Rak</option>');
            if (parentRackSelect.hasClass('select2-hidden-accessible')) {
                parentRackSelect.select2('destroy');
            }
            return;
        }

        $.ajax({
            url: locationsGetByWarehouseUrl,
            method: 'GET',
            data: {
                parent_id: zoneId,
                type: 'RACK'
            },
            success: function(racks) {
                let html = '<option value="">Pilih Rak</option>';
                racks.forEach(function(rack) {
                    const selected = selectedId == rack.id ? 'selected' : '';
                    html += `<option value="${rack.id}" ${selected}>${rack.code} - ${rack.description || ''}</option>`;
                });
                parentRackSelect.html(html);
                
                // Re-initialize Select2 if it exists
                if (typeof $.fn.select2 !== 'undefined') {
                    if (parentRackSelect.hasClass('select2-hidden-accessible')) {
                        parentRackSelect.select2('destroy');
                    }
                    parentRackSelect.select2({
                        placeholder: 'Pilih Rak',
                        allowClear: true,
                        dropdownParent: $('#locationModal'),
                        width: '100%',
                        language: {
                            noResults: function() {
                                return "Tidak ada hasil";
                            },
                            searching: function() {
                                return "Mencari...";
                            }
                        }
                    });
                    if (selectedId) {
                        parentRackSelect.val(selectedId).trigger('change');
                    }
                }
            }
        });
    }

    // Initialize location form handlers after modal content is loaded
    function initLocationFormHandlers() {
        const warehouseSelect = $('#warehouse_id');
        const typeSelect = $('#type');
        const parentZoneContainer = $('#parent-zone-container');
        const parentRackContainer = $('#parent-rack-container');
        const parentZoneSelect = $('#parent_zone_id');
        const parentRackSelect = $('#parent_rack_id');

        // Remove existing handlers to prevent duplicates
        warehouseSelect.off('change.locationForm');
        typeSelect.off('change.locationForm');
        parentZoneSelect.off('change.locationForm');
        parentRackSelect.off('change.locationForm');

        // Handle type change
        typeSelect.on('change.locationForm', function() {
            const type = $(this).val();
            const warehouseId = warehouseSelect.val();

            // Hide all parent containers
            parentZoneContainer.hide();
            parentRackContainer.hide();

            // Remove required attributes
            parentZoneSelect.removeAttr('required');
            parentRackSelect.removeAttr('required');
            $('#parent_id').removeAttr('required');

        // Show appropriate parent container and set required
        if (type === 'RACK') {
            parentZoneContainer.show();
            parentZoneSelect.attr('required', 'required');
            $('#parent_id').attr('required', 'required');
            if (warehouseId) {
                loadZones(warehouseId);
            } else {
                // Initialize Select2 even if no warehouse selected
                if (typeof $.fn.select2 !== 'undefined' && !parentZoneSelect.hasClass('select2-hidden-accessible')) {
                    parentZoneSelect.select2({
                        placeholder: 'Pilih Area',
                        allowClear: true,
                        dropdownParent: $('#locationModal'),
                        width: '100%',
                        language: {
                            noResults: function() {
                                return "Tidak ada hasil";
                            },
                            searching: function() {
                                return "Mencari...";
                            }
                        }
                    });
                }
            }
        } else if (type === 'SLOT') {
            parentZoneContainer.show();
            parentRackContainer.show();
            parentZoneSelect.attr('required', 'required');
            parentRackSelect.attr('required', 'required');
            $('#parent_id').attr('required', 'required');
            if (warehouseId) {
                loadZones(warehouseId);
            } else {
                // Initialize Select2 even if no warehouse selected
                if (typeof $.fn.select2 !== 'undefined') {
                    if (!parentZoneSelect.hasClass('select2-hidden-accessible')) {
                        parentZoneSelect.select2({
                            placeholder: 'Pilih Area',
                            allowClear: true,
                            dropdownParent: $('#locationModal'),
                            width: '100%',
                            language: {
                                noResults: function() {
                                    return "Tidak ada hasil";
                                },
                                searching: function() {
                                    return "Mencari...";
                                }
                            }
                        });
                    }
                    if (!parentRackSelect.hasClass('select2-hidden-accessible')) {
                        parentRackSelect.select2({
                            placeholder: 'Pilih Rak',
                            allowClear: true,
                            dropdownParent: $('#locationModal'),
                            width: '100%',
                            language: {
                                noResults: function() {
                                    return "Tidak ada hasil";
                                },
                                searching: function() {
                                    return "Mencari...";
                                }
                            }
                        });
                    }
                }
            }
        }

            // Clear parent selects when type changes
            if (type === 'ZONE') {
                parentZoneSelect.val('');
                parentRackSelect.val('');
                $('#parent_id').val('');
            } else if (type === 'RACK') {
                parentRackSelect.val('');
                $('#parent_id').val('');
            }
        });

        // Handle warehouse change
        warehouseSelect.on('change.locationForm', function() {
            const warehouseId = $(this).val();
            const type = typeSelect.val();

            if (type === 'RACK' || type === 'SLOT') {
                loadZones(warehouseId);
            }
        });

        // Handle zone change (for slots)
        parentZoneSelect.on('change.locationForm', function() {
            const zoneId = $(this).val();
            const type = typeSelect.val();

            // Update hidden parent_id field
            if (type === 'RACK') {
                $('#parent_id').val(zoneId || '');
                // Validate parent_id is set
                if (zoneId) {
                    $('#parent_id').removeClass('is-invalid');
                    $('#parent_id-error').addClass('d-none');
                } else {
                    $('#parent_id').addClass('is-invalid');
                    $('#parent_id-error').removeClass('d-none').text('Area harus dipilih untuk Rak');
                }
            }

            if (type === 'SLOT' && zoneId) {
                loadRacks(zoneId);
            } else {
                parentRackSelect.html('<option value="">Pilih Rak</option>');
                if (type === 'SLOT') {
                    $('#parent_id').val('');
                }
            }
        });

        // Handle rack change (for slots)
        parentRackSelect.on('change.locationForm', function() {
            const rackId = $(this).val();
            $('#parent_id').val(rackId || '');
            // Validate parent_id is set
            if (rackId) {
                $('#parent_id').removeClass('is-invalid');
                $('#parent_id-error').addClass('d-none');
            } else {
                $('#parent_id').addClass('is-invalid');
                $('#parent_id-error').removeClass('d-none').text('Rak harus dipilih untuk Tempat');
            }
        });

        // Initialize based on current form state
        const type = typeSelect.val();
        const warehouseId = warehouseSelect.val();
        const currentParentId = $('#parent_id').val();

        // Set required attributes based on type
        if (type === 'RACK') {
            parentZoneContainer.show();
            parentZoneSelect.attr('required', 'required');
            $('#parent_id').attr('required', 'required');
            if (warehouseId) {
                loadZones(warehouseId, currentParentId || null);
                if (currentParentId) {
                    // Set after zones are loaded
                    setTimeout(function() {
                        parentZoneSelect.val(currentParentId);
                    }, 300);
                }
            }
        } else if (type === 'SLOT') {
            parentZoneContainer.show();
            parentRackContainer.show();
            parentZoneSelect.attr('required', 'required');
            parentRackSelect.attr('required', 'required');
            $('#parent_id').attr('required', 'required');
            if (warehouseId && currentParentId) {
                // For SLOT, parent_id is a RACK, so we need to get the RACK's parent (ZONE)
                // Load zones first
                loadZones(warehouseId);
                
                // Then get the rack's parent zone
                $.ajax({
                    url: `/locations/${currentParentId}`,
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.location && response.location.parent_id) {
                            const zoneId = response.location.parent_id;
                            // Set zone and load racks
                            setTimeout(function() {
                                parentZoneSelect.val(zoneId);
                                loadRacks(zoneId, currentParentId);
                            }, 300);
                        }
                    },
                    error: function() {
                        // If we can't get rack info, just load zones
                    }
                });
            } else if (warehouseId) {
                loadZones(warehouseId);
            }
        }
    }

    // Initialize form handlers when modal is shown
    $('#locationModal').on('shown.bs.modal', function() {
        // Small delay to ensure form is fully loaded
        setTimeout(function() {
            initLocationFormHandlers();
        }, 100);
    });
    }); // end $(document).ready
    } // end initLocationsPage
    
    // Start checking for Select2
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            checkSelect2();
        });
    } else {
        checkSelect2();
    }
})();
</script>
@endpush
@endsection

