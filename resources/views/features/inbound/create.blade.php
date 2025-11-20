@extends('layouts.app')

@section('title', 'Transaksi Inbound Baru')

@push('styles')
<style>
    #scanner-container {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    #reader {
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
    }
    .scanner-status {
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    .scanner-status.active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .scanner-status.inactive {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Transaksi Inbound Baru"
    :breadcrumb-title="'Transaksi Inbound Baru'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Form Transaksi Inbound</h5>

                <form id="inbound-form" action="{{ route('inbound.store') }}" method="POST">
                    @csrf

                    <!-- Barcode Scanner -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Scan Barcode Item</label>
                        <div id="scanner-container">
                            <div id="reader"></div>
                            <div id="scanner-status" class="scanner-status inactive text-center">
                                <i class="ti ti-camera"></i> Klik tombol di bawah untuk memulai scanner
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-primary" id="btn-start-scanner">
                                    <i class="ti ti-camera"></i> Mulai Scanner
                                </button>
                                <button type="button" class="btn btn-secondary" id="btn-stop-scanner" style="display: none;">
                                    <i class="ti ti-camera-off"></i> Stop Scanner
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-manual-input">
                                    <i class="ti ti-keyboard"></i> Input Manual
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item Info (auto-filled after scan) -->
                    <div id="item-info" class="alert alert-info" style="display: none;">
                        <h6>Item Ditemukan:</h6>
                        <p class="mb-0">
                            <strong>Nama:</strong> <span id="item-name"></span><br>
                            <strong>SKU:</strong> <span id="item-sku"></span><br>
                            <strong>Barcode:</strong> <span id="item-barcode"></span>
                        </p>
                    </div>

                    <input type="hidden" id="item_id" name="item_id" required>

                    <!-- Warehouse Selection -->
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">Sekolah <span class="text-danger">*</span></label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                            <option value="">Pilih Sekolah</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-none" id="warehouse_id-error"></div>
                    </div>

                    <!-- Location Selection -->
                    <div class="mb-3">
                        <label for="to_location_id" class="form-label">Alamat Jalan / Kelurahan <span class="text-danger">*</span></label>
                        <select class="form-select" id="to_location_id" name="to_location_id" required disabled>
                            <option value="">Pilih Sekolah terlebih dahulu</option>
                        </select>
                        <div class="invalid-feedback d-none" id="to_location_id-error"></div>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        <div class="invalid-feedback d-none" id="quantity-error"></div>
                    </div>

                    <!-- Batch -->
                    <div class="mb-3">
                        <label for="batch" class="form-label">Batch</label>
                        <input type="text" class="form-control" id="batch" name="batch" placeholder="Contoh: BATCH-20250101-ABC">
                    </div>

                    <!-- Expired Date -->
                    <div class="mb-3">
                        <label for="expired_at" class="form-label">Tanggal Kadaluarsa</label>
                        <input type="date" class="form-control" id="expired_at" name="expired_at">
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check"></i> Simpan Transaksi
                        </button>
                        <a href="{{ route('inbound.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x"></i> Batal
                        </a>
                    </div>
                </form>
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
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="{{ asset('assets/js/helpers/toast.js') }}"></script>
<script src="{{ asset('assets/js/helpers/form.js') }}"></script>
<script>
$(document).ready(function() {
    let html5QrcodeScanner = null;
    let isScanning = false;

    // Initialize scanner
    function initScanner() {
        if (html5QrcodeScanner) {
            return;
        }

        html5QrcodeScanner = new Html5Qrcode("reader");
    }

    // Start scanner
    $('#btn-start-scanner').on('click', function() {
        if (isScanning) return;

        initScanner();

        html5QrcodeScanner.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            function(decodedText, decodedResult) {
                // Stop scanner after successful scan
                stopScanner();

                // Process barcode
                processBarcode(decodedText);
            },
            function(errorMessage) {
                // Ignore scanning errors
            }
        ).then(() => {
            isScanning = true;
            $('#btn-start-scanner').hide();
            $('#btn-stop-scanner').show();
            $('#scanner-status').removeClass('inactive').addClass('active')
                .html('<i class="ti ti-camera"></i> Scanner aktif - Arahkan kamera ke barcode');
        }).catch((err) => {
            console.error('Error starting scanner:', err);
            Toast.error('Gagal memulai scanner. Pastikan izin kamera diberikan.');
        });
    });

    // Stop scanner
    $('#btn-stop-scanner').on('click', function() {
        stopScanner();
    });

    function stopScanner() {
        if (html5QrcodeScanner && isScanning) {
            html5QrcodeScanner.stop().then(() => {
                isScanning = false;
                $('#btn-start-scanner').show();
                $('#btn-stop-scanner').hide();
                $('#scanner-status').removeClass('active').addClass('inactive')
                    .html('<i class="ti ti-camera"></i> Scanner dihentikan');
            }).catch((err) => {
                console.error('Error stopping scanner:', err);
            });
        }
    }

    // Manual input
    $('#btn-manual-input').on('click', function() {
        const barcode = prompt('Masukkan barcode item:');
        if (barcode && barcode.trim()) {
            processBarcode(barcode.trim());
        }
    });

    // Process barcode
    function processBarcode(barcode) {
        $.ajax({
            url: "{{ route('inbound.getItemByBarcode') }}",
            method: 'GET',
            data: { barcode: barcode },
            success: function(response) {
                if (response.success) {
                    $('#item_id').val(response.item.id);
                    $('#item-name').text(response.item.name);
                    $('#item-sku').text(response.item.sku);
                    $('#item-barcode').text(response.item.barcode);
                    $('#item-info').show();
                    Toast.success('Item berhasil ditemukan!');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Item tidak ditemukan';
                Toast.error(message);
            }
        });
    }

    // Load locations when warehouse is selected
    $('#warehouse_id').on('change', function() {
        const warehouseId = $(this).val();
        const locationSelect = $('#to_location_id');

        locationSelect.prop('disabled', true).html('<option value="">Memuat lokasi...</option>');

        if (!warehouseId) {
            locationSelect.html('<option value="">Pilih Sekolah terlebih dahulu</option>');
            return;
        }

        $.ajax({
            url: "{{ route('inbound.getLocationsByWarehouse') }}",
            method: 'GET',
            data: { warehouse_id: warehouseId },
            success: function(response) {
                if (response.success) {
                    locationSelect.html('<option value="">Pilih Lokasi</option>');
                    response.locations.forEach(function(location) {
                        locationSelect.append(
                            $('<option></option>')
                                .attr('value', location.id)
                                .text(location.path)
                        );
                    });
                    locationSelect.prop('disabled', false);
                }
            },
            error: function() {
                locationSelect.html('<option value="">Error memuat lokasi</option>');
                Toast.error('Gagal memuat lokasi');
            }
        });
    });

    // Form submission
    $('#inbound-form').on('submit', function(e) {
        e.preventDefault();

        // Basic validation
        const form = $(this);
        let isValid = true;

        // Check required fields
        form.find('[required]').each(function() {
            const field = $(this);
            if (!field.val() || field.val().trim() === '') {
                field.addClass('is-invalid');
                isValid = false;
            } else {
                field.removeClass('is-invalid');
            }
        });

        // Check if item is selected
        if (!$('#item_id').val()) {
            Toast.error('Silakan scan atau pilih item terlebih dahulu');
            isValid = false;
        }

        if (!isValid) {
            Toast.error('Mohon lengkapi semua field yang wajib diisi');
            return;
        }

        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').addClass('d-none');

        const formData = form.serialize();

        // Disable submit button
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="ti ti-loader"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message || 'Transaksi berhasil disimpan');
                    setTimeout(function() {
                        window.location.href = "{{ route('inbound.index') }}";
                    }, 1500);
                } else {
                    Toast.error(response.message || 'Gagal menyimpan transaksi');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};

                    // Display validation errors
                    $.each(errors, function(key, value) {
                        const input = form.find(`[name="${key}"]`);
                        input.addClass('is-invalid');

                        let errorDiv = form.find(`#${key}-error`);
                        if (!errorDiv.length) {
                            errorDiv = $(`<div class="invalid-feedback" id="${key}-error"></div>`);
                            input.after(errorDiv);
                        }
                        errorDiv.removeClass('d-none').text(Array.isArray(value) ? value[0] : value);
                    });

                    Toast.error(xhr.responseJSON?.message || 'Validasi gagal');
                } else {
                    Toast.error(xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan transaksi');
                }
            }
        });
    });

    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        stopScanner();
    });
});
</script>
@endpush

