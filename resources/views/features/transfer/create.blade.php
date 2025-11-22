@extends('layouts.app')

@section('title', 'Transaksi Transfer Baru')

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
    .location-info {
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
    }
    .location-info.success {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
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
    title="Transaksi Transfer Baru"
    :breadcrumb-title="'Transaksi Transfer Baru'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Form Transaksi Transfer</h5>

                <form id="transfer-form" action="{{ route('transfer.store') }}" method="POST">
                    @csrf

                    <!-- Item Barcode Scanner -->
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

                    <!-- From Location Scanner -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Scan Lokasi Asal</label>
                        <div id="from-scanner-container">
                            <div id="from-reader"></div>
                            <div id="from-scanner-status" class="scanner-status inactive text-center">
                                <i class="ti ti-camera"></i> Scan barcode lokasi asal
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-primary" id="btn-start-from-scanner" disabled>
                                    <i class="ti ti-camera"></i> Scan Lokasi Asal
                                </button>
                                <button type="button" class="btn btn-secondary" id="btn-stop-from-scanner" style="display: none;">
                                    <i class="ti ti-camera-off"></i> Stop
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-manual-from-input" disabled>
                                    <i class="ti ti-keyboard"></i> Input Manual
                                </button>
                            </div>
                        </div>
                        <div id="from-location-info" class="location-info success" style="display: none;">
                            <strong>Lokasi Asal:</strong> <span id="from-location-path"></span>
                        </div>
                        <input type="hidden" id="from_location_id" name="from_location_id" required>
                    </div>

                    <!-- To Location Scanner -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Scan Sekolah Tujuan</label>
                        <div id="to-scanner-container">
                            <div id="to-reader"></div>
                            <div id="to-scanner-status" class="scanner-status inactive text-center">
                            <i class="ti ti-camera"></i> Scan barcode sekolah tujuan
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-primary" id="btn-start-to-scanner" disabled>
                                    <i class="ti ti-camera"></i> Scan Sekolah Tujuan
                                </button>
                                <button type="button" class="btn btn-secondary" id="btn-stop-to-scanner" style="display: none;">
                                    <i class="ti ti-camera-off"></i> Stop
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-manual-to-input" disabled>
                                    <i class="ti ti-keyboard"></i> Input Manual
                                </button>
                            </div>
                        </div>
                        <div id="to-location-info" class="location-info success" style="display: none;">
                            <strong>Sekolah Tujuan:</strong> <span id="to-location-path"></span>
                        </div>
                        <input type="hidden" id="to_location_id" name="to_location_id" required>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        <div class="invalid-feedback d-none" id="quantity-error"></div>
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
                        <a href="{{ route('transfer.index') }}" class="btn btn-secondary">
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

<!-- Modal for Manual Location Input (From) -->
<x-ui.modal
    id="fromLocationSearchModal"
    title="Cari Lokasi Asal"
    size="lg"
>
    <div class="mb-3">
        <label for="from-location-search-input" class="form-label">Cari berdasarkan Kode Lokasi, Path, atau Nama Sekolah</label>
        <input type="text" class="form-control" id="from-location-search-input" placeholder="Ketik untuk mencari lokasi..." autocomplete="off">
        <small class="text-muted">Minimal 2 karakter</small>
    </div>
    <div id="from-location-search-results" style="max-height: 400px; overflow-y: auto;">
        <div class="text-center text-muted py-4">
            <i class="ti ti-search"></i> Mulai ketik untuk mencari lokasi
        </div>
    </div>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    </x-slot>
</x-ui.modal>

<!-- Modal for Manual Location Input (To) -->
<x-ui.modal
    id="toLocationSearchModal"
    title="Cari Sekolah Tujuan"
    size="lg"
>
    <div class="mb-3">
        <label for="to-location-search-input" class="form-label">Cari berdasarkan Kode Lokasi, Path, atau Nama Sekolah</label>
        <input type="text" class="form-control" id="to-location-search-input" placeholder="Ketik untuk mencari lokasi..." autocomplete="off">
        <small class="text-muted">Minimal 2 karakter</small>
    </div>
    <div id="to-location-search-results" style="max-height: 400px; overflow-y: auto;">
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
            initTransferPage();
        } else if (attempts < 20) {
            setTimeout(function() {
                checkSelect2(attempts + 1);
            }, 100);
        } else {
            // Try to load Select2 manually as fallback
            var script = document.createElement('script');
            script.src = "{{ asset('assets/libs/select2/dist/js/select2.min.js') }}";
            script.onload = function() {
                initTransferPage();
            };
            document.head.appendChild(script);
        }
    };
    
    function initTransferPage() {
        $(document).ready(function() {
    let itemScanner = null;
    let fromLocationScanner = null;
    let toLocationScanner = null;
    let isItemScanning = false;
    let isFromScanning = false;
    let isToScanning = false;

    // Initialize scanners
    function initItemScanner() {
        if (!itemScanner) {
            itemScanner = new Html5Qrcode("reader");
        }
    }

    function initFromLocationScanner() {
        if (!fromLocationScanner) {
            fromLocationScanner = new Html5Qrcode("from-reader");
        }
    }

    function initToLocationScanner() {
        if (!toLocationScanner) {
            toLocationScanner = new Html5Qrcode("to-reader");
        }
    }

    // Item Scanner
    $('#btn-start-scanner').on('click', function() {
        if (isItemScanning) return;
        initItemScanner();

        itemScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            function(decodedText) {
                stopItemScanner();
                processItemBarcode(decodedText);
            },
            function() {}
        ).then(() => {
            isItemScanning = true;
            $('#btn-start-scanner').hide();
            $('#btn-stop-scanner').show();
            $('#scanner-status').removeClass('inactive').addClass('active')
                .html('<i class="ti ti-camera"></i> Scanner aktif - Arahkan kamera ke barcode item');
        }).catch((err) => {
            Toast.error('Gagal memulai scanner item');
        });
    });

    $('#btn-stop-scanner').on('click', stopItemScanner);
    function stopItemScanner() {
        if (itemScanner && isItemScanning) {
            itemScanner.stop().then(() => {
                isItemScanning = false;
                $('#btn-start-scanner').show();
                $('#btn-stop-scanner').hide();
                $('#scanner-status').removeClass('active').addClass('inactive')
                    .html('<i class="ti ti-camera"></i> Scanner dihentikan');
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
                url: "{{ route('transfer.searchItems') }}",
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
        $('#btn-start-from-scanner, #btn-manual-from-input').prop('disabled', false);

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

    function processItemBarcode(barcode) {
        $.ajax({
            url: "{{ route('transfer.getItemByBarcode') }}",
            method: 'GET',
            data: { barcode: barcode },
            success: function(response) {
                if (response.success) {
                    $('#item_id').val(response.item.id);
                    $('#item-name').text(response.item.name);
                    $('#item-sku').text(response.item.sku);
                    $('#item-barcode').text(response.item.barcode);
                    $('#item-info').show();
                    $('#btn-start-from-scanner, #btn-manual-from-input').prop('disabled', false);
                    Toast.success('Item berhasil ditemukan!');
                }
            },
            error: function(xhr) {
                Toast.error(xhr.responseJSON?.message || 'Item tidak ditemukan');
            }
        });
    }

    // From Location Scanner
    $('#btn-start-from-scanner').on('click', function() {
        if (isFromScanning) return;
        initFromLocationScanner();

        fromLocationScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            function(decodedText) {
                stopFromLocationScanner();
                processLocationBarcode(decodedText, 'from');
            },
            function() {}
        ).then(() => {
            isFromScanning = true;
            $('#btn-start-from-scanner').hide();
            $('#btn-stop-from-scanner').show();
            $('#from-scanner-status').removeClass('inactive').addClass('active')
                .html('<i class="ti ti-camera"></i> Scanner aktif - Arahkan kamera ke barcode lokasi');
        }).catch((err) => {
            Toast.error('Gagal memulai scanner lokasi asal');
        });
    });

    $('#btn-stop-from-scanner').on('click', stopFromLocationScanner);
    function stopFromLocationScanner() {
        if (fromLocationScanner && isFromScanning) {
            fromLocationScanner.stop().then(() => {
                isFromScanning = false;
                $('#btn-start-from-scanner').show();
                $('#btn-stop-from-scanner').hide();
                $('#from-scanner-status').removeClass('active').addClass('inactive')
                    .html('<i class="ti ti-camera"></i> Scanner dihentikan');
            });
        }
    }

    // Manual input - show modal for from location
    $('#btn-manual-from-input').on('click', function() {
        // Reset search
        $('#from-location-search-input').val('');
        $('#from-location-search-results').html('<div class="text-center text-muted py-4"><i class="ti ti-search"></i> Mulai ketik untuk mencari lokasi</div>');
        // Show modal
        Modal.show('fromLocationSearchModal');
        // Focus on input
        setTimeout(function() {
            $('#from-location-search-input').focus();
        }, 300);
    });

    // From location search with debounce
    let fromLocationSearchTimeout;
    $('#from-location-search-input').on('input', function() {
        const query = $(this).val().trim();
        const resultsContainer = $('#from-location-search-results');

        clearTimeout(fromLocationSearchTimeout);

        if (query.length < 2) {
            resultsContainer.html('<div class="text-center text-muted py-4"><i class="ti ti-search"></i> Minimal 2 karakter untuk mencari</div>');
            return;
        }

        // Show loading
        resultsContainer.html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        fromLocationSearchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('transfer.searchLocations') }}",
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

    // Handle from location selection
    $(document).on('click', '#fromLocationSearchModal .location-search-result', function() {
        const locationId = $(this).data('location-id');
        const locationCode = $(this).data('location-code');
        const locationPath = $(this).data('location-path');
        const warehouseName = $(this).data('location-warehouse');
        
        // Set location
        $('#from_location_id').val(locationId);
        $('#from-location-path').text(warehouseName + ' - ' + locationPath);
        $('#from-location-info').show();
        
        // Hide modal
        Modal.hide('fromLocationSearchModal');
        
        // Enable to location scanner
        $('#btn-start-to-scanner, #btn-manual-to-input').prop('disabled', false);
        
        Toast.success('Lokasi asal berhasil dipilih!');
    });

    // To Location Scanner
    $('#btn-start-to-scanner').on('click', function() {
        if (isToScanning) return;
        initToLocationScanner();

        toLocationScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            function(decodedText) {
                stopToLocationScanner();
                processLocationBarcode(decodedText, 'to');
            },
            function() {}
        ).then(() => {
            isToScanning = true;
            $('#btn-start-to-scanner').hide();
            $('#btn-stop-to-scanner').show();
            $('#to-scanner-status').removeClass('inactive').addClass('active')
                .html('<i class="ti ti-camera"></i> Scanner aktif - Arahkan kamera ke barcode lokasi');
        }).catch((err) => {
            Toast.error('Gagal memulai scanner lokasi tujuan');
        });
    });

    $('#btn-stop-to-scanner').on('click', stopToLocationScanner);
    function stopToLocationScanner() {
        if (toLocationScanner && isToScanning) {
            toLocationScanner.stop().then(() => {
                isToScanning = false;
                $('#btn-start-to-scanner').show();
                $('#btn-stop-to-scanner').hide();
                $('#to-scanner-status').removeClass('active').addClass('inactive')
                    .html('<i class="ti ti-camera"></i> Scanner dihentikan');
            });
        }
    }

    // Manual input - show modal for to location
    $('#btn-manual-to-input').on('click', function() {
        // Reset search
        $('#to-location-search-input').val('');
        $('#to-location-search-results').html('<div class="text-center text-muted py-4"><i class="ti ti-search"></i> Mulai ketik untuk mencari lokasi</div>');
        // Show modal
        Modal.show('toLocationSearchModal');
        // Focus on input
        setTimeout(function() {
            $('#to-location-search-input').focus();
        }, 300);
    });

    // To location search with debounce
    let toLocationSearchTimeout;
    $('#to-location-search-input').on('input', function() {
        const query = $(this).val().trim();
        const resultsContainer = $('#to-location-search-results');

        clearTimeout(toLocationSearchTimeout);

        if (query.length < 2) {
            resultsContainer.html('<div class="text-center text-muted py-4"><i class="ti ti-search"></i> Minimal 2 karakter untuk mencari</div>');
            return;
        }

        // Show loading
        resultsContainer.html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        toLocationSearchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('transfer.searchLocations') }}",
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    if (response.success && response.locations && response.locations.length > 0) {
                        let html = '';
                        response.locations.forEach(function(location) {
                            html += `
                                <div class="location-search-result p-3 border-bottom cursor-pointer" 
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

    // Handle to location selection
    $(document).on('click', '#toLocationSearchModal .location-search-result', function() {
        const locationId = $(this).data('location-id');
        const locationCode = $(this).data('location-code');
        const locationPath = $(this).data('location-path');
        const warehouseName = $(this).data('location-warehouse');
        
        // Set location
        $('#to_location_id').val(locationId);
        $('#to-location-path').text(warehouseName + ' - ' + locationPath);
        $('#to-location-info').show();
        
        // Hide modal
        Modal.hide('toLocationSearchModal');
        
        Toast.success('Sekolah tujuan berhasil dipilih!');
    });

    // Handle Enter key in location search inputs
    $('#from-location-search-input, #to-location-search-input').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const modalId = $(this).closest('.modal').attr('id');
            const firstResult = $(`#${modalId} .location-search-result`).first();
            if (firstResult.length) {
                firstResult.click();
            }
        }
    });

    function processLocationBarcode(code, type) {
        $.ajax({
            url: "{{ route('transfer.getLocationByCode') }}",
            method: 'GET',
            data: { code: code },
            success: function(response) {
                if (response.success) {
                    if (type === 'from') {
                        $('#from_location_id').val(response.location.id);
                        $('#from-location-path').text(response.location.path);
                        $('#from-location-info').show();
                        $('#btn-start-to-scanner, #btn-manual-to-input').prop('disabled', false);
                        Toast.success('Lokasi asal berhasil ditemukan!');
                    } else {
                        $('#to_location_id').val(response.location.id);
                        $('#to-location-path').text(response.location.path);
                        $('#to-location-info').show();
                        Toast.success('Lokasi tujuan berhasil ditemukan!');
                    }
                }
            },
            error: function(xhr) {
                Toast.error(xhr.responseJSON?.message || 'Lokasi tidak ditemukan');
            }
        });
    }

    // Form submission
    $('#transfer-form').on('submit', function(e) {
        e.preventDefault();

        if (!Form.validate(this)) {
            return;
        }

        const formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message || 'Transaksi berhasil disimpan');
                    setTimeout(function() {
                        window.location.href = "{{ route('transfer.index') }}";
                    }, 1500);
                } else {
                    Toast.error(response.message || 'Gagal menyimpan transaksi');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};
                    Form.showErrors('#transfer-form', errors);
                    Toast.error(xhr.responseJSON?.message || 'Validasi gagal');
                } else {
                    Toast.error('Terjadi kesalahan saat menyimpan transaksi');
                }
            }
        });
    });

    // Cleanup
    $(window).on('beforeunload', function() {
        stopItemScanner();
        stopFromLocationScanner();
        stopToLocationScanner();
    });
    }); // end $(document).ready
    } // end initTransferPage
    
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

