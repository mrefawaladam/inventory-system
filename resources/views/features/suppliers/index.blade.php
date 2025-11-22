@extends('layouts.app')

@section('title', 'Manajemen Supplier')

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
    
    #suppliers-table tbody {
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
    title="Manajemen Supplier"
    :breadcrumb-title="'Manajemen Supplier'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Supplier</h4>
                    <p class="card-subtitle mb-3">
                        Kelola data supplier (pemasok). Anda dapat membuat, mengedit, dan menghapus supplier dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-supplier">
                    <i class="ti ti-plus me-1"></i> Tambah Supplier Baru
                </button>
            </div>
            <div class="table-responsive">
                <table id="suppliers-table" class="table table-striped table-bordered align-middle" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Jumlah Inbound</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Supplier Modal -->
<x-ui.modal
    id="supplierModal"
    title="Tambah Supplier Baru"
    size="lg"
    content-id="supplierModalBody"
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
    const suppliersTable = $('#suppliers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('suppliers.index') }}",
            dataSrc: function(json) {
                $('#suppliers-table tbody').find('.table-loading-overlay').remove();
                return json.data;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'phone', name: 'phone' },
            { data: 'address', name: 'address' },
            { data: 'inbound_count', name: 'inbound_count', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; border-width: 0.3em;"></div><div class="mt-3" style="font-size: 16px; font-weight: 500;">Memuat data...</div>',
            lengthMenu: "Tampilkan _MENU_ entri",
            zeroRecords: "Tidak ada data yang ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
            infoFiltered: "(difilter dari _MAX_ total entri)",
            search: "Cari:",
            loadingRecords: "Memuat data...",
            emptyTable: "Tidak ada data yang tersedia",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });
    
    // Add loading overlay to table body when loading starts
    suppliersTable.on('processing.dt', function(e, settings, processing) {
        if (processing) {
            const tbody = $('#suppliers-table tbody');
            if (tbody.find('.table-loading-overlay').length === 0) {
                tbody.append(`
                    <tr class="table-loading-overlay-row">
                        <td colspan="7" style="position: relative; height: 300px; padding: 0;">
                            <div class="table-loading-overlay">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="loading-text">Memuat data supplier...</div>
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            $('#suppliers-table tbody').find('.table-loading-overlay-row').remove();
        }
    });

    // Load create form
    $('#btn-create-supplier').on('click', function() {
        $.get("{{ route('suppliers.create') }}", function(html) {
            $('#supplierModalBody').html(html);
            $('#supplierModal').find('.modal-title').text('Tambah Supplier Baru');
            $('#supplierModal').modal('show');
            $('#btn-submit-form').show();
        });
    });

    // Handle show button click
    $(document).on('click', '.btn-show-supplier', function(e) {
        e.preventDefault();
        const supplierId = $(this).data('supplier-id');
        $.get(`/suppliers/${supplierId}`, function(html) {
            $('#supplierModalBody').html(html);
            $('#supplierModal').find('.modal-title').text('Detail Supplier');
            $('#supplierModal').modal('show');
            $('#btn-submit-form').hide();
        });
    });

    // Handle edit button click
    $(document).on('click', '.btn-edit-supplier', function(e) {
        e.preventDefault();
        const supplierId = $(this).data('supplier-id');
        $.get(`/suppliers/${supplierId}/edit`, function(html) {
            $('#supplierModalBody').html(html);
            $('#supplierModal').find('.modal-title').text('Edit Supplier');
            $('#supplierModal').modal('show');
            $('#btn-submit-form').show();
        });
    });

    // Handle delete button click
    $(document).on('click', '.btn-delete-supplier', function(e) {
        e.preventDefault();
        const supplierId = $(this).data('supplier-id');
        const supplierName = $(this).data('supplier-name');
        
        if (confirm(`Apakah Anda yakin ingin menghapus supplier "${supplierName}"?`)) {
            $.ajax({
                url: `/suppliers/${supplierId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        suppliersTable.ajax.reload();
                        showToast('success', response.message);
                    } else {
                        showToast('error', response.message || 'Terjadi kesalahan saat menghapus supplier.');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus supplier.';
                    showToast('error', message);
                }
            });
        }
    });

    // Handle form submit
    $('#btn-submit-form').on('click', function() {
        const form = $('#supplier-form');
        const formData = form.serialize();
        const formAction = form.attr('action');
        const formMethod = form.find('input[name="_method"]').val() || 'POST';

        $.ajax({
            url: formAction,
            type: formMethod,
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#supplierModal').modal('hide');
                    suppliersTable.ajax.reload();
                    showToast('success', response.message);
                } else {
                    showToast('error', response.message || 'Terjadi kesalahan saat menyimpan supplier.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMessage = xhr.responseJSON?.message || 'Validasi gagal.';
                    
                    // Display validation errors
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback').addClass('d-none');
                    
                    $.each(errors, function(key, value) {
                        const field = form.find(`[name="${key}"]`);
                        field.addClass('is-invalid');
                        const errorDiv = form.find(`#${key}-error`);
                        if (errorDiv.length) {
                            errorDiv.removeClass('d-none').text(Array.isArray(value) ? value[0] : value);
                        }
                    });
                    
                    showToast('error', errorMessage);
                } else {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan supplier.';
                    showToast('error', message);
                }
            }
        });
    });

    // Reset form when modal is closed
    $('#supplierModal').on('hidden.bs.modal', function() {
        $('#supplierModalBody').html('');
    });
});
</script>
@endpush
@endsection

