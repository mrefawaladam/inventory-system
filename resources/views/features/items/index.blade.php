@extends('layouts.app')

@section('title', 'Manajemen Barang')

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
    title="Manajemen Barang"
    :breadcrumb-title="'Manajemen Barang'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Barang</h4>
                    <p class="card-subtitle mb-3">
                        Kelola data barang (SKU, Barcode, Gambar). Anda dapat membuat, mengedit, dan menghapus barang dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-item">
                    <i class="ti ti-plus me-1"></i> Tambah Barang Baru
                </button>
            </div>
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table id="items-table" class="table table-striped table-bordered align-middle" style="width: 100%; min-width: 1200px;">
                    <thead>
                        <tr>
                            <th style="min-width: 60px;">ID</th>
                            <th style="min-width: 80px;">Gambar</th>
                            <th style="min-width: 120px;">SKU</th>
                            <th style="min-width: 200px;">Nama</th>
                            <th style="min-width: 150px;">Barcode</th>
                            <th style="min-width: 100px;">Barcode Image</th>
                            <th style="min-width: 100px;">Unit</th>
                            <th style="min-width: 100px;">Total Stok</th>
                            <th style="min-width: 100px;">Status</th>
                            <th style="min-width: 120px;">Tanggal Dibuat</th>
                            <th style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Item Modal -->
<x-ui.modal
    id="itemModal"
    title="Tambah Barang Baru"
    size="lg"
    content-id="itemModalBody"
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
    const itemsTable = $('#items-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('items.index') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'image_preview', name: 'image_preview', orderable: false, searchable: false },
            { data: 'sku', name: 'sku' },
            { data: 'name', name: 'name' },
            { data: 'barcode', name: 'barcode' },
            { data: 'barcode_image', name: 'barcode_image', orderable: false, searchable: false },
            { data: 'unit', name: 'unit' },
            { data: 'total_stock', name: 'total_stock', orderable: false },
            { data: 'stock_status', name: 'stock_status', orderable: false, searchable: false },
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

    // Load create form
    $('#btn-create-item').on('click', function() {
        Modal.load('itemModal', "{{ route('items.create') }}", 'Tambah Barang Baru');
        $('#btn-submit-form').show();
    });

    // Handle show button click (delegated event)
    $(document).on('click', '.btn-show-item', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id');
        Modal.load('itemModal', `/items/${itemId}`, 'Detail Barang');
        $('#btn-submit-form').hide();
    });

    // Handle edit button click (delegated event)
    $(document).on('click', '.btn-edit-item', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id');
        Modal.load('itemModal', `/items/${itemId}/edit`, 'Edit Barang');
        $('#btn-submit-form').show();
    });

    // Handle form submission
    $('#btn-submit-form').on('click', function() {
        Form.submit('#item-form', {
            success: function(response) {
                if (response.success) {
                    Modal.hide('itemModal');
                    Toast.success(response.message);
                    itemsTable.ajax.reload(null, false);
                }
            }
        });
    });

    // Handle delete button click (delegated event)
    $(document).on('click', '.btn-delete-item', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id');
        const itemName = $(this).data('item-name');

        if (!confirm(`Apakah Anda yakin ingin menghapus barang "${itemName}"?`)) {
            return;
        }

        $.ajax({
            url: `/items/${itemId}`,
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
                    itemsTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Gagal menghapus barang.';
                Toast.error(message);
            }
        });
    });

    // Reset form when modal is hidden
    $('#itemModal').on('hidden.bs.modal', function() {
        Modal.clear('itemModal');
        $('#btn-submit-form').show();
    });
});
</script>
@endpush
@endsection

