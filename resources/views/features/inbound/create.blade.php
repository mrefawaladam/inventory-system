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
    .item-search-result {
        padding: 12px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .item-search-result:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd;
    }
    .item-search-result.selected {
        background-color: #e7f3ff;
        border-color: #0d6efd;
    }
    .item-search-result .item-name {
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }
    .item-search-result .item-details {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 4px;
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

                    <!-- Supplier Selection -->
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select class="form-select" id="supplier_id" name="supplier_id" required>
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-none" id="supplier_id-error"></div>
                    </div>

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

<!-- Modal for Manual Item Input -->
<x-ui.modal
    id="itemSearchModal"
    title="Cari Item"
    size="lg"
>
    <div class="mb-3">
        <label for="item-search-input" class="form-label">Cari berdasarkan Nama, SKU, atau Barcode</label>
        <input type="text" class="form-control" id="item-search-input" placeholder="Ketik untuk mencari item..." autocomplete="off">
        <small class="text-muted">Minimal 2 karakter</small>
    </div>
    <div id="item-search-results" style="max-height: 400px; overflow-y: auto;">
        <div class="text-center text-muted py-4">
            <i class="ti ti-search"></i> Mulai ketik untuk mencari item
        </div>
    </div>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    </x-slot>
</x-ui.modal>

<!-- Modal for Manual Location Input -->
<x-ui.modal
    id="locationSearchModal"
    title="Cari Lokasi"
    size="lg"
>
    <div class="mb-3">
        <label for="location-search-input" class="form-label">Cari berdasarkan Kode Lokasi, Path, atau Nama Sekolah</label>
        <input type="text" class="form-control" id="location-search-input" placeholder="Ketik untuk mencari lokasi..." autocomplete="off">
        <small class="text-muted">Minimal 2 karakter</small>
    </div>
    <div id="location-search-results" style="max-height: 400px; overflow-y: auto;">
        <div class="text-center text-muted py-4">
            <i class="ti ti-search"></i> Mulai ketik untuk mencari lokasi
        </div>
    </div>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    </x-slot>
</x-ui.modal>
@endsection

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="{{ asset('assets/js/helpers/toast.js') }}"></script>
<script src="{{ asset('assets/js/helpers/form.js') }}"></script>
<script src="{{ asset('assets/js/helpers/modal.js') }}"></script>
<script>
// Wait for Select2 to be available
(function() {
    var checkSelect2 = function(attempts) {
        attempts = attempts || 0;
        if (typeof $.fn.select2 !== 'undefined') {
            initInboundPage();
        } else if (attempts < 20) {
            setTimeout(function() {
                checkSelect2(attempts + 1);
            }, 100);
        } else {
            // Try to load Select2 manually as fallback
            var script = document.createElement('script');
            script.src = "{{ asset('assets/libs/select2/dist/js/select2.min.js') }}";
            script.onload = function() {
                initInboundPage();
            };
            document.head.appendChild(script);
        }
    };
    
    function initInboundPage() {
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

    // Manual input - show modal
    $('#btn-manual-input').on('click', function() {
        // Reset search
        $('#item-search-input').val('');
        $('#item-search-results').html('<div class="text-center text-muted py-4"><i class="ti ti-search"></i> Mulai ketik untuk mencari item</div>');
        // Show modal
        Modal.show('itemSearchModal');
        // Focus on input
        setTimeout(function() {
            $('#item-search-input').focus();
        }, 300);
    });

    // Item search with debounce
    let searchTimeout;
    $('#item-search-input').on('input', function() {
        const query = $(this).val().trim();
        const resultsContainer = $('#item-search-results');

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            resultsContainer.html('<div class="text-center text-muted py-4"><i class="ti ti-search"></i> Minimal 2 karakter untuk mencari</div>');
            return;
        }

        // Show loading
        resultsContainer.html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('inbound.searchItems') }}",
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    if (response.success && response.items && response.items.length > 0) {
                        let html = '';
                        response.items.forEach(function(item) {
                            html += `
                                <div class="item-search-result" data-item-id="${item.id}" data-item-name="${item.name}" data-item-sku="${item.sku}" data-item-barcode="${item.barcode}">
                                    <div class="item-name">${item.name}</div>
                                    <div class="item-details">
                                        SKU: ${item.sku} | Barcode: ${item.barcode}
                                    </div>
                                </div>
                            `;
                        });
                        resultsContainer.html(html);
                    } else {
                        resultsContainer.html('<div class="text-center text-muted py-4"><i class="ti ti-alert-circle"></i> Item tidak ditemukan</div>');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat mencari item';
                    resultsContainer.html(`<div class="alert alert-danger">${message}</div>`);
                }
            });
        }, 300);
    });

    // Handle item selection
    $(document).on('click', '.item-search-result', function() {
        const itemId = $(this).data('item-id');
        const itemName = $(this).data('item-name');
        const itemSku = $(this).data('item-sku');
        const itemBarcode = $(this).data('item-barcode');

        // Set form values
        $('#item_id').val(itemId);
        $('#item-name').text(itemName);
        $('#item-sku').text(itemSku);
        $('#item-barcode').text(itemBarcode);
        $('#item-info').show();

        // Close modal
        Modal.hide('itemSearchModal');
        Toast.success('Item berhasil dipilih!');
    });

    // Handle Enter key in search input
    $('#item-search-input').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstResult = $('.item-search-result').first();
            if (firstResult.length) {
                firstResult.click();
            }
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
                    
                    // Initialize or re-initialize Select2
                    if (typeof $.fn.select2 !== 'undefined') {
                        if (locationSelect.hasClass('select2-hidden-accessible')) {
                            locationSelect.select2('destroy');
                        }
                        locationSelect.select2({
                            placeholder: 'Pilih Lokasi',
                            allowClear: true,
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
            },
            error: function() {
                locationSelect.html('<option value="">Error memuat lokasi</option>');
                Toast.error('Gagal memuat lokasi');
            }
        });
    });

    // Initialize Select2 for location select on page load
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined' && $('#to_location_id').length && !$('#to_location_id').prop('disabled')) {
            $('#to_location_id').select2({
                placeholder: 'Pilih Lokasi',
                allowClear: true,
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
    });

    // Location search functionality
    let locationSearchTimeout;
    $('#location-search-input').on('input', function() {
        const query = $(this).val().trim();
        const resultsContainer = $('#location-search-results');

        clearTimeout(locationSearchTimeout);

        if (query.length < 2) {
            resultsContainer.html('<div class="text-center text-muted py-4"><i class="ti ti-search"></i> Minimal 2 karakter untuk mencari</div>');
            return;
        }

        // Show loading
        resultsContainer.html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        locationSearchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('inbound.searchLocations') }}",
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    if (response.success && response.locations && response.locations.length > 0) {
                        let html = '';
                        response.locations.forEach(function(location) {
                            html += `
                                <div class="location-search-result p-3 border-bottom" 
                                     data-location-id="${location.id}" 
                                     data-location-code="${location.code}" 
                                     data-location-path="${location.path}"
                                     data-location-warehouse="${location.warehouse_name}"
                                     style="cursor: pointer; transition: background-color 0.2s;"
                                     onmouseover="this.style.backgroundColor='#f8f9fa'"
                                     onmouseout="this.style.backgroundColor=''">
                                    <div class="fw-bold">${location.full_path}</div>
                                    <small class="text-muted">Kode: ${location.code}</small>
                                </div>
                            `;
                        });
                        resultsContainer.html(html);
                    } else {
                        resultsContainer.html('<div class="text-center text-muted py-4"><i class="ti ti-alert-circle"></i> Lokasi tidak ditemukan</div>');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat mencari lokasi';
                    resultsContainer.html(`<div class="alert alert-danger">${message}</div>`);
                }
            });
        }, 300);
    });

    // Handle location selection
    $(document).on('click', '#locationSearchModal .location-search-result', function() {
        const locationId = $(this).data('location-id');
        const locationCode = $(this).data('location-code');
        const locationPath = $(this).data('location-path');
        const warehouseName = $(this).data('location-warehouse');
        
        // Set location in select
        $('#to_location_id').val(locationId).trigger('change');
        
        // Hide modal
        Modal.hide('locationSearchModal');
        
        Toast.success('Lokasi berhasil dipilih!');
    });

    // Handle Enter key in location search input
    $('#location-search-input').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstResult = $('#locationSearchModal .location-search-result').first();
            if (firstResult.length) {
                firstResult.click();
            }
        }
    });

    // Add button to open location search modal next to location select
    $(document).ready(function() {
        if ($('#to_location_id').length && !$('#to_location_id').next('.location-search-btn').length) {
            const searchBtn = $('<button type="button" class="btn btn-sm btn-outline-secondary location-search-btn ms-2" title="Cari Lokasi"><i class="ti ti-search"></i></button>');
            $('#to_location_id').parent().append(searchBtn);
            
            searchBtn.on('click', function() {
                $('#location-search-input').val('');
                $('#location-search-results').html('<div class="text-center text-muted py-4"><i class="ti ti-search"></i> Mulai ketik untuk mencari lokasi</div>');
                Modal.show('locationSearchModal');
                setTimeout(function() {
                    $('#location-search-input').focus();
                }, 300);
            });
        }
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
    }); // end $(document).ready
    } // end initInboundPage
    
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

