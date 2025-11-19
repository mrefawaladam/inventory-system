@extends('layouts.app')

@section('title', 'Transaksi Transfer')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Transaksi Transfer"
    :breadcrumb-title="'Transaksi Transfer'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Daftar Transaksi Transfer</h5>
                <a href="{{ route('transfer.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Transaksi Baru
                </a>
            </div>
            <div class="table-responsive">
                <table id="transfer-table" class="table table-striped table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Dari Lokasi</th>
                            <th>Ke Lokasi</th>
                            <th>Jumlah</th>
                            <th>User</th>
                            <th>Tanggal</th>
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
    if ($.fn.DataTable.isDataTable('#transfer-table')) {
        $('#transfer-table').DataTable().destroy();
    }

    $('#transfer-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('transfer.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'transaction_code', name: 'transaction_code' },
            { data: 'item_name', name: 'item_name', orderable: false },
            { data: 'item_sku', name: 'item_sku', orderable: false },
            { data: 'from_location_name', name: 'from_location_name', orderable: false },
            { data: 'to_location_name', name: 'to_location_name', orderable: false },
            { data: 'quantity', name: 'quantity' },
            { data: 'user_name', name: 'user_name', orderable: false },
            { data: 'created_at', name: 'created_at' },
        ],
        order: [[7, 'desc']],
        scrollX: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});
</script>
@endpush

