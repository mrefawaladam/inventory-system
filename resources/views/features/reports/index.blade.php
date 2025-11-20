@extends('layouts.app')

@section('title', 'Laporan Transaksi')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .filter-card {
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Laporan Transaksi"
    :breadcrumb-title="'Laporan Transaksi'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Filters -->
<div class="card filter-card">
    <div class="card-body">
        <h5 class="card-title mb-3">Filter Laporan</h5>
        <form id="filter-form" class="row g-3">
            <div class="col-md-3">
                <label for="type" class="form-label">Jenis Transaksi</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Semua</option>
                    <option value="INBOUND">Inbound</option>
                    <option value="OUTBOUND">Outbound</option>
                    <option value="TRANSFER">Transfer</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="start_date" name="start_date">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="end_date" name="end_date">
            </div>
            <div class="col-md-3">
                <label for="item_id" class="form-label">Item</label>
                <select class="form-select" id="item_id" name="item_id">
                    <option value="">Semua Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->sku }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 d-flex align-items-end">
                <button type="button" class="btn btn-primary me-2" id="apply-filter">
                    <i class="ti ti-filter"></i> Terapkan Filter
                </button>
                <button type="button" class="btn btn-secondary" id="reset-filter">
                    <i class="ti ti-refresh"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Daftar Transaksi</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="export-excel-btn">
                            <i class="ti ti-file-excel"></i> Export Excel (CSV)
                        </a></li>
                        <li><a class="dropdown-item" href="#" id="export-pdf-btn">
                            <i class="ti ti-file-pdf"></i> Export PDF
                        </a></li>
                    </ul>
                </div>
            </div>
            <div class="table-responsive">
                <table id="reports-table" class="table table-striped table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Jenis</th>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Lokasi Sumber</th>
                            <th>Lokasi Tujuan</th>
                            <th>Jumlah</th>
                            <th>Batch</th>
                            <th>User</th>
                            <th>Tanggal Pengiriman</th>
                            <th>Tanggal Terima</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/helpers/toast.js') }}"></script>
<script>
$(document).ready(function() {
    let table;

    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#reports-table')) {
            $('#reports-table').DataTable().destroy();
        }

        table = $('#reports-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.index') }}",
                type: 'GET',
                data: function(d) {
                    d.type = $('#type').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.item_id = $('#item_id').val();
                }
            },
            columns: [
                { data: 'transaction_code', name: 'transaction_code' },
                { data: 'type_label', name: 'type', orderable: false },
                { data: 'item_name', name: 'item_name', orderable: false },
                { data: 'item_sku', name: 'item_sku', orderable: false },
                { data: 'from_location', name: 'from_location', orderable: false },
                { data: 'to_location', name: 'to_location', orderable: false },
                { data: 'quantity', name: 'quantity' },
                { data: 'batch', name: 'batch' },
                { data: 'user_name', name: 'user_name', orderable: false },
                { data: 'shipped_at', name: 'shipped_at' },
                { data: 'received_at', name: 'received_at' },
            ],
            order: [[9, 'desc']],
            scrollX: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
        });
    }

    // Initialize DataTable
    initDataTable();

    // Apply filter
    $('#apply-filter').on('click', function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#reset-filter').on('click', function() {
        $('#filter-form')[0].reset();
        table.ajax.reload();
    });

    // Export Excel
    $('#export-excel-btn').on('click', function(e) {
        e.preventDefault();
        const params = new URLSearchParams();
        
        if ($('#type').val()) params.append('type', $('#type').val());
        if ($('#start_date').val()) params.append('start_date', $('#start_date').val());
        if ($('#end_date').val()) params.append('end_date', $('#end_date').val());
        if ($('#item_id').val()) params.append('item_id', $('#item_id').val());
        
        const url = "{{ route('reports.exportExcel') }}" + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    });

    // Export PDF
    $('#export-pdf-btn').on('click', function(e) {
        e.preventDefault();
        const params = new URLSearchParams();
        
        if ($('#type').val()) params.append('type', $('#type').val());
        if ($('#start_date').val()) params.append('start_date', $('#start_date').val());
        if ($('#end_date').val()) params.append('end_date', $('#end_date').val());
        if ($('#item_id').val()) params.append('item_id', $('#item_id').val());
        
        const url = "{{ route('reports.exportPdf') }}" + (params.toString() ? '?' + params.toString() : '');
        window.open(url, '_blank');
    });
});
</script>
@endpush

