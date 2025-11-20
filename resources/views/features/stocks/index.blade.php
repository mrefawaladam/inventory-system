@extends('layouts.app')

@section('title', 'Manajemen Stok')

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
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Manajemen Stok"
    :breadcrumb-title="'Manajemen Stok'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="filter-item" class="form-label">Filter Barang</label>
                <select id="filter-item" class="form-select">
                    <option value="">Semua Barang</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->sku }} - {{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="filter-location" class="form-label">Filter Lokasi</label>
                <select id="filter-location" class="form-select">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->code }} - {{ $location->warehouse->name ?? '' }}</option>
                    @endforeach
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

<!-- Stock Operations -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Operasi Stok</h5>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-success" id="btn-increase-stock">
                <i class="ti ti-plus me-1"></i> Tambah Stok
            </button>
            <button type="button" class="btn btn-warning" id="btn-decrease-stock">
                <i class="ti ti-minus me-1"></i> Kurangi Stok
            </button>
            <button type="button" class="btn btn-info" id="btn-transfer-stock">
                <i class="ti ti-arrow-right me-1"></i> Transfer Stok
            </button>
        </div>
    </div>
</div>

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Stok</h4>
                    <p class="card-subtitle mb-3">
                        Kelola stok barang di semua lokasi. Sistem menggunakan FEFO (First Expired First Out) untuk pengurangan stok.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-stock">
                    <i class="ti ti-plus me-1"></i> Tambah Stok Baru
                </button>
            </div>
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table id="stocks-table" class="table table-striped table-bordered align-middle" style="width: 100%; min-width: 1200px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Barang</th>
                            <th>SKU</th>
                            <th>Sekolah</th>
                            <th>Lokasi</th>
                            <th>Path</th>
                            <th>Quantity</th>
                            <th>Batch</th>
                            <th>Tanggal Pengiriman</th>
                            <th>Status Pengiriman</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Stock Modal -->
<x-ui.modal
    id="stockModal"
    title="Tambah Stok"
    size="lg"
    content-id="stockModalBody"
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
    let stocksTable = $('#stocks-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('stocks.index') }}",
            data: function(d) {
                d.item_id = $('#filter-item').val();
                d.location_id = $('#filter-location').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'item_name', name: 'item.name' },
            { data: 'item_sku', name: 'item.sku' },
            { data: 'warehouse_name', name: 'location.warehouse.name' },
            { data: 'location_code', name: 'location.code' },
            { data: 'location_path', name: 'location_path', orderable: false },
            { data: 'quantity', name: 'quantity' },
            { data: 'batch', name: 'batch' },
            { data: 'expired_at', name: 'expired_at' },
            { data: 'expired_status', name: 'expired_status', orderable: false, searchable: false },
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
    $('#filter-item, #filter-location').on('change', function() {
        stocksTable.ajax.reload();
    });

    $('#btn-reset-filters').on('click', function() {
        $('#filter-item').val('');
        $('#filter-location').val('');
        stocksTable.ajax.reload();
    });

    // Load create form
    $('#btn-create-stock').on('click', function() {
        Modal.load('stockModal', "{{ route('stocks.create') }}", 'Tambah Stok');
        $('#btn-submit-form').show();
    });

    // Load increase stock form
    $(document).on('click', '#btn-increase-stock', function(e) {
        e.preventDefault();
        Modal.load('stockModal', "{{ route('stocks.create') }}", 'Tambah Stok');
        $('#btn-submit-form').show();
    });

    // Load decrease stock form
    $(document).on('click', '#btn-decrease-stock', function(e) {
        e.preventDefault();
        // Create decrease form dynamically
        const html = `
            <form id="decrease-stock-form">
                @csrf
                <div class="mb-3">
                    <label for="decrease_item_id" class="form-label">Barang <span class="text-danger">*</span></label>
                    <select class="form-select" id="decrease_item_id" name="item_id" required>
                        <option value="">Pilih Barang</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->sku }} - {{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="decrease_location_id" class="form-label">Lokasi <span class="text-danger">*</span></label>
                    <select class="form-select" id="decrease_location_id" name="location_id" required>
                        <option value="">Pilih Lokasi</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->code }} - {{ $location->warehouse->name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="decrease_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="decrease_quantity" name="quantity" min="1" required>
                    <small class="text-muted">Sistem akan menggunakan FEFO (First Expired First Out)</small>
                </div>
            </form>
        `;
        $('#stockModalBody').html(html);
        $('#stockModal').find('.modal-title').text('Kurangi Stok');
        $('#btn-submit-form').show();
        // Show modal using Bootstrap modal
        const modal = new bootstrap.Modal(document.getElementById('stockModal'));
        modal.show();
    });

    // Load transfer stock form
    $(document).on('click', '#btn-transfer-stock', function(e) {
        e.preventDefault();
        const html = `
            <form id="transfer-stock-form">
                @csrf
                <div class="mb-3">
                    <label for="transfer_item_id" class="form-label">Barang <span class="text-danger">*</span></label>
                    <select class="form-select" id="transfer_item_id" name="item_id" required>
                        <option value="">Pilih Barang</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->sku }} - {{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="transfer_from_location_id" class="form-label">Dari Lokasi <span class="text-danger">*</span></label>
                        <select class="form-select" id="transfer_from_location_id" name="from_location_id" required>
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->code }} - {{ $location->warehouse->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="transfer_to_location_id" class="form-label">Ke Lokasi <span class="text-danger">*</span></label>
                        <select class="form-select" id="transfer_to_location_id" name="to_location_id" required>
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->code }} - {{ $location->warehouse->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="transfer_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="transfer_quantity" name="quantity" min="1" required>
                </div>
            </form>
        `;
        $('#stockModalBody').html(html);
        $('#stockModal').find('.modal-title').text('Transfer Stok');
        $('#btn-submit-form').show();
        // Show modal using Bootstrap modal
        const modal = new bootstrap.Modal(document.getElementById('stockModal'));
        modal.show();
    });

    // Handle form submission (use delegated event to work with dynamically created forms)
    $(document).on('click', '#btn-submit-form', function() {
        // Find the active form
        let form = $('#stock-form');
        if (form.length === 0) {
            form = $('#decrease-stock-form');
        }
        if (form.length === 0) {
            form = $('#transfer-stock-form');
        }

        if (form.length === 0) {
            Toast.error('Form tidak ditemukan');
            return;
        }

        // Validate form
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        let url, method;
        const formId = form.attr('id');

        if (formId === 'decrease-stock-form') {
            url = "{{ route('stocks.decrease') }}";
            method = 'POST';
        } else if (formId === 'transfer-stock-form') {
            url = "{{ route('stocks.transfer') }}";
            method = 'POST';
        } else {
            url = form.attr('action');
            method = form.find('input[name="_method"]').val() || form.attr('method') || 'POST';
        }

        const formData = form.serialize();

        $.ajax({
            url: url,
            method: method === 'PUT' || method === 'PATCH' ? 'POST' : method,
            data: formData + (method === 'PUT' || method === 'PATCH' ? `&_method=${method}` : ''),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#stockModal').modal('hide');
                    Toast.success(response.message);
                    stocksTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    // Clear previous errors
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback').remove();

                    // Display validation errors
                    $.each(errors, function(key, value) {
                        const input = form.find(`[name="${key}"]`);
                        input.addClass('is-invalid');
                        let errorDiv = form.find(`#${key}-error`);
                        if (!errorDiv.length) {
                            errorDiv = $(`<div class="invalid-feedback" id="${key}-error"></div>`);
                            input.after(errorDiv);
                        }
                        errorDiv.removeClass('d-none').text(value[0]);
                    });
                    Toast.error(xhr.responseJSON.message || 'Validasi gagal.');
                } else {
                    Toast.error(xhr.responseJSON?.message || 'Terjadi kesalahan. Silakan coba lagi.');
                }
            }
        });
    });

    // Handle show/edit/delete buttons
    $(document).on('click', '.btn-show-stock', function(e) {
        e.preventDefault();
        const stockId = $(this).data('stock-id');
        Modal.load('stockModal', `/stocks/${stockId}`, 'Detail Stok');
        $('#btn-submit-form').hide();
    });

    $(document).on('click', '.btn-edit-stock', function(e) {
        e.preventDefault();
        const stockId = $(this).data('stock-id');
        Modal.load('stockModal', `/stocks/${stockId}/edit`, 'Edit Stok');
        $('#btn-submit-form').show();
    });

    $(document).on('click', '.btn-delete-stock', function(e) {
        e.preventDefault();
        const stockId = $(this).data('stock-id');

        if (!confirm('Apakah Anda yakin ingin menghapus stok ini?')) {
            return;
        }

        $.ajax({
            url: `/stocks/${stockId}`,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    stocksTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                Toast.error(xhr.responseJSON?.message || 'Gagal menghapus stok.');
            }
        });
    });

    // Reset form when modal is hidden
    $('#stockModal').on('hidden.bs.modal', function() {
        Modal.clear('stockModal');
        $('#btn-submit-form').show();
    });
});
</script>
@endpush
@endsection

