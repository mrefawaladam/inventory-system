@extends('layouts.app')

@section('title', 'Transaksi Outbound')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
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
    
    #outbound-table tbody {
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
    title="Transaksi Outbound"
    :breadcrumb-title="'Transaksi Outbound'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Daftar Transaksi Outbound</h5>
                <a href="{{ route('outbound.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Transaksi Baru
                </a>
            </div>
            <div class="table-responsive">
                <table id="outbound-table" class="table table-striped table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Customer</th>
                            <th>Lokasi Sumber</th>
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
    if ($.fn.DataTable.isDataTable('#outbound-table')) {
        $('#outbound-table').DataTable().destroy();
    }

    const outboundTable = $('#outbound-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('outbound.index') }}",
            type: 'GET',
            dataSrc: function(json) {
                $('#outbound-table tbody').find('.table-loading-overlay').remove();
                return json.data;
            }
        },
        columns: [
            { data: 'transaction_code', name: 'transaction_code' },
            { data: 'item_name', name: 'item_name', orderable: false },
            { data: 'item_sku', name: 'item_sku', orderable: false },
            { data: 'customer_name', name: 'customer_name', orderable: false },
            { data: 'location_name', name: 'location_name', orderable: false },
            { data: 'quantity', name: 'quantity' },
            { data: 'user_name', name: 'user_name', orderable: false },
            { data: 'created_at', name: 'created_at' },
        ],
        order: [[7, 'desc']],
        scrollX: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; border-width: 0.3em;"></div><div class="mt-3" style="font-size: 16px; font-weight: 500;">Memuat data...</div>',
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
    
    // Add loading overlay to table body when loading starts
    outboundTable.on('processing.dt', function(e, settings, processing) {
        if (processing) {
            const tbody = $('#outbound-table tbody');
            if (tbody.find('.table-loading-overlay').length === 0) {
                tbody.append(`
                    <tr class="table-loading-overlay-row">
                        <td colspan="8" style="position: relative; height: 300px; padding: 0;">
                            <div class="table-loading-overlay">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="loading-text">Memuat data transaksi outbound...</div>
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            $('#outbound-table tbody').find('.table-loading-overlay-row').remove();
        }
    });
});
</script>
@endpush

