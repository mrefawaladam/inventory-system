$(document).ready(function() {
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
        return status === 'delivered' ? '#28a745' : '#dc3545';
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
            url: '{{ route("tracking.routes") }}',
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
    initMap();
    addWarehouseMarkers();
    addAllRoutes();

    // Filter form submit
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        const filters = {
            type: $('#filter-type').val(),
            item_id: $('#filter-item').val(),
            warehouse_id: $('#filter-warehouse').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val()
        };
        loadRoutes(filters);
    });

    // Reset filter
    $('#btn-reset-filter').on('click', function() {
        $('#filter-form')[0].reset();
        loadRoutes({});
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

