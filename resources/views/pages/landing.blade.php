<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Inventory - Tracking Pengiriman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
<style>
        #tracking-map {
            height: 500px;
            width: 100%;
            border-radius: 8px;
            z-index: 1;
  }
</style>
</head>
<body class="bg-gray-50">
<!-- Hero Section -->
    <section class="bg-blue-600 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-5">Sistem Inventory & Tracking</h1>
            <p class="text-xl mb-8 max-w-2xl mx-auto font-light">
          Pantau pergerakan barang dan pengiriman secara real-time dengan mudah dan cepat
        </p>
            <div class="flex gap-4 justify-center flex-wrap">
                <a href="{{ route('public.tracking') }}" class="bg-green-500 hover:bg-green-600 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors inline-flex items-center gap-2">
                    <iconify-icon icon="solar:map-point-search-bold"></iconify-icon>
          Mulai Tracking
        </a>
                <a href="{{ route('login') }}" class="bg-white hover:bg-gray-100 text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg transition-colors inline-flex items-center gap-2">
                    <iconify-icon icon="solar:login-3-bold"></iconify-icon>
                    Masuk ke Dashboard
                </a>
      </div>
    </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4 text-gray-800">Fitur Utama</h2>
            <p class="text-center text-gray-600 mb-16 text-lg">Sistem yang dirancang untuk memudahkan manajemen inventory dan tracking</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-8 text-center border-4 border-transparent hover:border-blue-500 transition-colors">
                    <iconify-icon icon="solar:map-point-search-bold" class="text-6xl text-blue-500 mb-6 block"></iconify-icon>
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Tracking Real-time</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Pantau pergerakan barang secara real-time dengan peta interaktif yang mudah digunakan
                    </p>
  </div>
                
                <div class="bg-white p-8 text-center border-4 border-transparent hover:border-green-500 transition-colors">
                    <iconify-icon icon="solar:box-bold" class="text-6xl text-green-500 mb-6 block"></iconify-icon>
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Manajemen Inventory</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Kelola stok barang dengan sistem yang terintegrasi dan akurat untuk efisiensi maksimal
                    </p>
</div>

                <div class="bg-white p-8 text-center border-4 border-transparent hover:border-red-500 transition-colors">
                    <iconify-icon icon="solar:graph-up-bold" class="text-6xl text-red-500 mb-6 block"></iconify-icon>
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Laporan & Analytics</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Dapatkan insight dari data transaksi dan pergerakan barang untuk pengambilan keputusan
                    </p>
  </div>
  
                <div class="bg-white p-8 text-center border-4 border-transparent hover:border-yellow-500 transition-colors">
                    <iconify-icon icon="solar:settings-bold" class="text-6xl text-yellow-500 mb-6 block"></iconify-icon>
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Mudah Digunakan</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Interface yang intuitif dan user-friendly untuk pengalaman pengguna yang optimal
          </p>
        </div>
      </div>
    </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-gray-800 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-5xl font-bold text-blue-500 mb-2">100%</div>
                    <div class="text-lg text-gray-300">Real-time Tracking</div>
                </div>
                <div>
                    <div class="text-5xl font-bold text-blue-500 mb-2">24/7</div>
                    <div class="text-lg text-gray-300">Akses Kapan Saja</div>
                </div>
                <div>
                    <div class="text-5xl font-bold text-blue-500 mb-2">100+</div>
                    <div class="text-lg text-gray-300">Transaksi per Hari</div>
                </div>
                <div>
                    <div class="text-5xl font-bold text-blue-500 mb-2">99%</div>
                    <div class="text-lg text-gray-300">Akurasi Data</div>
        </div>
      </div>
        </div>
    </section>

    <!-- Tracking Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4 text-gray-800">Tracking Pengiriman</h2>
            <p class="text-center text-gray-600 mb-12 text-lg">Pantau pergerakan barang secara real-time di peta interaktif</p>
            
            <!-- Filter Panel -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6 border-2 border-gray-200">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">Filter Transaksi</h3>
                <form id="filter-form" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Transaksi</label>
                        <select name="type" id="filter-type" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            <option value="">Semua Tipe</option>
                            <option value="INBOUND" {{ ($filters['type'] ?? '') == 'INBOUND' ? 'selected' : '' }}>Inbound</option>
                            <option value="OUTBOUND" {{ ($filters['type'] ?? '') == 'OUTBOUND' ? 'selected' : '' }}>Outbound</option>
                            <option value="TRANSFER" {{ ($filters['type'] ?? '') == 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                        <select name="item_id" id="filter-item" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            <option value="">Semua Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ ($filters['item_id'] ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} @if($item->sku)({{ $item->sku }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sekolah</label>
                        <select name="warehouse_id" id="filter-warehouse" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            <option value="">Semua Sekolah</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                        <input type="date" name="date_from" id="filter-date-from" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                        <input type="date" name="date_to" id="filter-date-to" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors inline-flex items-center justify-center gap-2">
                            <iconify-icon icon="solar:magnifer-bold"></iconify-icon>
                            Filter
                        </button>
                        <button type="button" id="btn-reset-filter" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors inline-flex items-center justify-center gap-2">
                            <iconify-icon icon="solar:refresh-bold"></iconify-icon>
                            Reset
                        </button>
                    </div>
                </form>
    </div>
    
            <!-- Map Section -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-800">Peta Tracking Transaksi</h3>
                            <div class="flex gap-2">
                                <button type="button" id="btn-refresh-map" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold text-sm transition-colors inline-flex items-center gap-2">
                                    <iconify-icon icon="solar:refresh-bold"></iconify-icon>
                                    Refresh
                                </button>
                                <button type="button" id="btn-clear-routes" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold text-sm transition-colors inline-flex items-center gap-2">
                                    <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                                    Clear
                                </button>
                            </div>
                        </div>
                        <div id="tracking-map"></div>
                        <div class="mt-4 text-sm text-gray-600">
                            <iconify-icon icon="solar:info-circle-bold" class="mr-2"></iconify-icon>
                            Menampilkan <strong id="route-count">{{ count($routes) }}</strong> route transaksi
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Legenda</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 bg-green-500 rounded"></div>
                                <span class="text-gray-700">Sudah Dikirim</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 bg-red-500 rounded"></div>
                                <span class="text-gray-700">Belum Dikirim</span>
                            </div>
                        </div>
                        <hr class="my-4 border-gray-300">
                        <p class="text-sm text-gray-600">
                            <iconify-icon icon="solar:info-circle-bold" class="mr-2"></iconify-icon>
                            Klik marker untuk detail transaksi
          </p>
        </div>
      </div>
    </div>
  </div>
    </section>

<!-- CTA Section -->
    <section class="py-20 bg-green-500 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-5">Siap untuk mulai tracking?</h2>
            <p class="text-xl mb-10 font-light max-w-2xl mx-auto">
                Akses halaman tracking tanpa perlu login atau masuk ke dashboard untuk fitur lengkap
            </p>
            <a href="{{ route('public.tracking') }}" class="bg-white hover:bg-gray-100 text-green-500 px-10 py-5 rounded-lg font-semibold text-lg transition-colors inline-flex items-center gap-2">
                <iconify-icon icon="solar:arrow-right-bold"></iconify-icon>
        Tracking Sekarang
      </a>
    </div>
    </section>

<!-- Footer -->
    <footer class="bg-gray-700 text-gray-300 py-10">
        <div class="container mx-auto px-4 text-center">
    <p>&copy; {{ date('Y') }} Sistem Inventory. All rights reserved.</p>
  </div>
</footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Pass data to JavaScript
        window.trackingData = {
            routes: @json($routes),
            warehouses: @json($warehousesMap),
            filters: @json($filters ?? []),
            apiUrl: '{{ route("public.tracking.routes") }}'
        };

        let trackingMap;
        let routePolylines = [];
        let routeMarkers = [];
        let warehouseMarkers = [];

        // Initialize map
        function initMap() {
            if (typeof L === 'undefined') {
                console.error('Leaflet library not loaded');
                return;
            }

            // Default center (Jakarta)
            let defaultLat = -6.2088;
            let defaultLng = 106.8456;

            // Check if we have warehouses data
            if (window.trackingData && window.trackingData.warehouses && window.trackingData.warehouses.length > 0) {
                const firstWarehouse = window.trackingData.warehouses[0];
                defaultLat = firstWarehouse.latitude;
                defaultLng = firstWarehouse.longitude;
            }

            // Initialize map
            trackingMap = L.map('tracking-map').setView([defaultLat, defaultLng], 6);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(trackingMap);

            // Invalidate size
            setTimeout(function() {
                if (trackingMap) {
                    trackingMap.invalidateSize();
                }
            }, 100);
        }

        // Add warehouse markers
        function addWarehouseMarkers() {
            if (!trackingMap || !window.trackingData || !window.trackingData.warehouses) return;

            // Clear existing warehouse markers
            warehouseMarkers.forEach(marker => trackingMap.removeLayer(marker));
            warehouseMarkers = [];

            window.trackingData.warehouses.forEach(function(warehouse) {
                const marker = L.marker([warehouse.latitude, warehouse.longitude], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34]
                    })
                }).addTo(trackingMap);

                marker.bindPopup(`
                    <b>${warehouse.name}</b><br>
                    ${warehouse.address || ''}
                `);

                warehouseMarkers.push(marker);
            });
        }

        // Get color based on delivery status
        function getStatusColor(status) {
            return status === 'delivered' ? '#22c55e' : '#ef4444';
        }

        function getMarkerIcon(status) {
            const color = status === 'delivered' ? 'green' : 'red';
            return L.icon({
                iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${color}.png`,
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34]
            });
        }

        // Add route to map
        function addRoute(route) {
            if (!trackingMap) return;

            const fromLat = route.from.latitude;
            const fromLng = route.from.longitude;
            const toLat = route.to.latitude;
            const toLng = route.to.longitude;

            const status = route.delivery_status || 'pending';
            const statusColor = getStatusColor(status);
            const markerIcon = getMarkerIcon(status);

            // Create polyline
            const polyline = L.polyline(
                [[fromLat, fromLng], [toLat, toLng]],
                {
                    color: statusColor,
                    weight: 3,
                    opacity: 0.7
                }
            ).addTo(trackingMap);

            // Create origin marker
            const originMarker = L.marker([fromLat, fromLng], {
                icon: markerIcon
            }).addTo(trackingMap);

            // Create destination marker
            const destMarker = L.marker([toLat, toLng], {
                icon: markerIcon
            }).addTo(trackingMap);

            // Popup content
            const popupContent = `
                <div style="min-width: 200px;">
                    <h6><strong>${route.transaction_code}</strong></h6>
                    <hr>
                    <p class="mb-1"><strong>Tipe:</strong> ${route.type_label}</p>
                    <p class="mb-1"><strong>Item:</strong> ${route.item.name}</p>
                    <p class="mb-1"><strong>Quantity:</strong> ${route.quantity.toLocaleString('id-ID')}</p>
                    <p class="mb-1"><strong>Dari:</strong> ${route.from.warehouse_name || 'N/A'}</p>
                    <p class="mb-1"><strong>Ke:</strong> ${route.to.warehouse_name || 'N/A'}</p>
                    <p class="mb-1"><strong>Status:</strong> ${route.delivery_status_label || (status === 'delivered' ? 'Sudah Dikirim' : 'Belum Dikirim')}</p>
                    <p class="mb-1"><strong>Jarak:</strong> ${route.distance_km} km</p>
                    <p class="mb-1"><strong>Tanggal:</strong> ${route.created_at_formatted}</p>
                    <p class="mb-0"><strong>User:</strong> ${route.user.name}</p>
                    ${route.notes ? `<hr><p class="mb-0"><small><strong>Notes:</strong> ${route.notes}</small></p>` : ''}
                </div>
            `;

            originMarker.bindPopup(popupContent);
            destMarker.bindPopup(popupContent);
            polyline.bindPopup(popupContent);

            // Store references
            routePolylines.push(polyline);
            routeMarkers.push(originMarker);
            routeMarkers.push(destMarker);
        }

        // Add all routes to map
        function addAllRoutes() {
            if (!window.trackingData || !window.trackingData.routes) return;

            clearRoutes();

            window.trackingData.routes.forEach(function(route) {
                addRoute(route);
            });

            // Fit bounds to show all routes
            if (routePolylines.length > 0) {
                const group = new L.featureGroup(routePolylines);
                trackingMap.fitBounds(group.getBounds().pad(0.1));
            }

            // Update route count
            $('#route-count').text(window.trackingData.routes.length);
        }

        // Clear all routes
        function clearRoutes() {
            routePolylines.forEach(polyline => trackingMap.removeLayer(polyline));
            routeMarkers.forEach(marker => trackingMap.removeLayer(marker));
            routePolylines = [];
            routeMarkers = [];
        }

        // Load routes from API
        function loadRoutes(filters = {}) {
            $.ajax({
                url: window.trackingData.apiUrl,
                method: 'GET',
                data: filters,
                success: function(response) {
                    if (response.success) {
                        window.trackingData.routes = response.routes;
                        window.trackingData.warehouses = response.warehouses;
                        addAllRoutes();
                        addWarehouseMarkers();
                    }
                },
                error: function() {
                    console.error('Error loading routes');
                }
            });
        }

        // Initialize
        $(document).ready(function() {
            initMap();
            addWarehouseMarkers();
            addAllRoutes();

            // Filter form submit
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                window.location.href = '{{ route("landing") }}?' + formData;
            });

            // Reset filter
            $('#btn-reset-filter').on('click', function() {
                window.location.href = '{{ route("landing") }}';
            });

            // Refresh map
            $('#btn-refresh-map').on('click', function() {
                const filters = {
                    type: $('#filter-type').val(),
                    item_id: $('#filter-item').val(),
                    warehouse_id: $('#filter-warehouse').val(),
                    date_from: $('#filter-date-from').val(),
                    date_to: $('#filter-date-to').val()
                };
                loadRoutes(filters);
            });

            // Clear routes
            $('#btn-clear-routes').on('click', function() {
                clearRoutes();
                $('#route-count').text(0);
            });
        });
    </script>
</body>
</html>
