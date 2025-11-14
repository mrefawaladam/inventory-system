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
    .badge-zone {
        background-color: #0d6efd;
    }
    .badge-rack {
        background-color: #ffc107;
        color: #000;
    }
    .badge-slot {
        background-color: #198754;
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
                <label for="filter-warehouse" class="form-label">Filter Gudang</label>
                <select id="filter-warehouse" class="form-select">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="filter-type" class="form-label">Filter Tipe</label>
                <select id="filter-type" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="ZONE">Zone</option>
                    <option value="RACK">Rack</option>
                    <option value="SLOT">Slot</option>
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
                        Kelola struktur lokasi gudang (Zone → Rack → Slot). Anda dapat membuat, mengedit, dan menghapus lokasi dari halaman ini.
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
                            <th style="min-width: 100px;">Tipe</th>
                            <th style="min-width: 150px;">Gudang</th>
                            <th style="min-width: 120px;">Parent</th>
                            <th style="min-width: 200px;">Path Lengkap</th>
                            <th style="min-width: 100px;">Kapasitas</th>
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
$(document).ready(function() {
    // Initialize DataTable
    let locationsTable = $('#locations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('locations.index') }}",
            data: function(d) {
                d.warehouse_id = $('#filter-warehouse').val();
                d.type = $('#filter-type').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'code', name: 'code' },
            { 
                data: 'type_label', 
                name: 'type',
                render: function(data, type, row) {
                    const badgeClass = row.type === 'ZONE' ? 'badge-zone' : 
                                      row.type === 'RACK' ? 'badge-rack' : 'badge-slot';
                    return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                }
            },
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
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
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
            }
        }
    });

    // Filter handlers
    $('#filter-warehouse, #filter-type').on('change', function() {
        locationsTable.ajax.reload();
    });

    $('#btn-reset-filters').on('click', function() {
        $('#filter-warehouse').val('');
        $('#filter-type').val('');
        locationsTable.ajax.reload();
    });

    // Load create form
    $('#btn-create-location').on('click', function() {
        Modal.load('locationModal', "{{ route('locations.create') }}", 'Tambah Lokasi Baru');
        $('#btn-submit-form').show();
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
        Modal.load('locationModal', `/locations/${locationId}/edit`, 'Edit Lokasi');
        $('#btn-submit-form').show();
    });

    // Handle form submission
    $('#btn-submit-form').on('click', function() {
        Form.submit('#location-form', {
            success: function(response) {
                if (response.success) {
                    Modal.hide('locationModal');
                    Toast.success(response.message);
                    locationsTable.ajax.reload(null, false);
                }
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
});
</script>
@endpush
@endsection

