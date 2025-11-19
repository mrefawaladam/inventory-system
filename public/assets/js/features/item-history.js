$(document).ready(function() {
    let historyMap;
    let historyPolylines = [];
    let historyMarkers = [];
    let warehouseMarkers = [];
    let currentActiveRoute = null;
    let playbackInterval = null;
    let playbackIndex = 0;

    // Initialize map
    function initHistoryMap() {
        if (typeof L === 'undefined') {
            console.error('Leaflet library not loaded');
            return;
        }

        if (!window.itemHistoryData || !window.itemHistoryData.warehouses) {
            return;
        }

        // Default center
        let defaultLat = -6.2088;
        let defaultLng = 106.8456;

        if (window.itemHistoryData.warehouses.length > 0) {
            const firstWarehouse = window.itemHistoryData.warehouses[0];
            defaultLat = firstWarehouse.latitude;
            defaultLng = firstWarehouse.longitude;
        }

        // Initialize map
        historyMap = L.map('item-history-map').setView([defaultLat, defaultLng], 6);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(historyMap);

        // Invalidate size
        setTimeout(function() {
            if (historyMap) {
                historyMap.invalidateSize();
            }
        }, 100);
    }

    // Add warehouse markers
    function addWarehouseMarkers() {
        if (!historyMap || !window.itemHistoryData || !window.itemHistoryData.warehouses) return;

        warehouseMarkers.forEach(marker => historyMap.removeLayer(marker));
        warehouseMarkers = [];

        window.itemHistoryData.warehouses.forEach(function(warehouse) {
            const marker = L.marker([warehouse.latitude, warehouse.longitude], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34]
                })
            }).addTo(historyMap);

            marker.bindPopup(`
                <b>${warehouse.name}</b><br>
                ${warehouse.address || ''}
            `);

            warehouseMarkers.push(marker);
        });
    }

    // Get route color based on type
    function getRouteColor(type) {
        switch(type) {
            case 'TRANSFER':
                return '#007bff';
            case 'INBOUND':
                return '#ffc107';
            case 'OUTBOUND':
                return '#6c757d';
            default:
                return '#007bff';
        }
    }

    // Add single route to map
    function addRoute(route, isActive = false) {
        if (!historyMap) return;

        const fromLat = route.from.latitude;
        const fromLng = route.from.longitude;
        const toLat = route.to.latitude;
        const toLng = route.to.longitude;

        const color = getRouteColor(route.type);
        const weight = isActive ? 5 : 3;
        const opacity = isActive ? 1 : 0.6;

        // Create polyline
        const polyline = L.polyline(
            [[fromLat, fromLng], [toLat, toLng]],
            {
                color: color,
                weight: weight,
                opacity: opacity
            }
        ).addTo(historyMap);

        // Create origin marker
        const originMarker = L.marker([fromLat, fromLng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34]
            })
        }).addTo(historyMap);

        // Create destination marker
        const destMarker = L.marker([toLat, toLng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34]
            })
        }).addTo(historyMap);

        // Popup content
        const popupContent = `
            <div style="min-width: 200px;">
                <h6><strong>#${route.sequence} - ${route.transaction_code}</strong></h6>
                <hr>
                <p class="mb-1"><strong>Tipe:</strong> ${route.type_label}</p>
                <p class="mb-1"><strong>Quantity:</strong> ${route.quantity.toLocaleString('id-ID')}</p>
                <p class="mb-1"><strong>Dari:</strong> ${route.from.warehouse_name || 'N/A'}</p>
                <p class="mb-1"><strong>Ke:</strong> ${route.to.warehouse_name || 'N/A'}</p>
                <p class="mb-1"><strong>Jarak:</strong> ${route.distance_km} km</p>
                <p class="mb-0"><strong>Tanggal:</strong> ${route.created_at_formatted}</p>
            </div>
        `;

        originMarker.bindPopup(popupContent);
        destMarker.bindPopup(popupContent);
        polyline.bindPopup(popupContent);

        // Store references
        const routeGroup = {
            polyline: polyline,
            originMarker: originMarker,
            destMarker: destMarker,
            route: route
        };

        historyPolylines.push(routeGroup);
        historyMarkers.push(originMarker);
        historyMarkers.push(destMarker);

        return routeGroup;
    }

    // Clear all routes
    function clearHistoryMap() {
        historyPolylines.forEach(group => {
            historyMap.removeLayer(group.polyline);
            historyMap.removeLayer(group.originMarker);
            historyMap.removeLayer(group.destMarker);
        });
        historyPolylines = [];
        historyMarkers = [];
    }

    // Show all routes
    function showAllRoutes() {
        clearHistoryMap();

        if (!window.itemHistoryData || !window.itemHistoryData.history) return;

        window.itemHistoryData.history.forEach(function(route) {
            addRoute(route, false);
        });

        // Fit bounds
        if (historyPolylines.length > 0) {
            const allPolylines = historyPolylines.map(g => g.polyline);
            const group = new L.featureGroup(allPolylines);
            historyMap.fitBounds(group.getBounds().pad(0.1));
        }

        $('#history-count').text(window.itemHistoryData.history.length);
    }

    // Highlight specific route
    function highlightRoute(routeId) {
        // Remove active class from timeline
        $('.timeline-item').removeClass('active');

        // Clear map
        clearHistoryMap();

        if (!window.itemHistoryData || !window.itemHistoryData.history) return;

        // Add all routes, highlight the selected one
        window.itemHistoryData.history.forEach(function(route) {
            const isActive = route.id == routeId;
            addRoute(route, isActive);

            if (isActive) {
                // Scroll to timeline item
                const timelineItem = $(`.timeline-item[data-route-id="${routeId}"]`);
                if (timelineItem.length) {
                    timelineItem.addClass('active');
                    const container = $('#timeline-container');
                    const scrollTop = container.scrollTop();
                    const itemTop = timelineItem.position().top;
                    container.scrollTop(scrollTop + itemTop - 100);
                }
            }
        });

        // Fit bounds to active route
        const activeGroup = historyPolylines.find(g => g.route.id == routeId);
        if (activeGroup) {
            historyMap.fitBounds(activeGroup.polyline.getBounds().pad(0.2));
        }
    }

    // Playback animation
    function startPlayback() {
        if (playbackInterval) {
            stopPlayback();
            return;
        }

        if (!window.itemHistoryData || !window.itemHistoryData.history.length) {
            alert('Tidak ada history untuk di-playback');
            return;
        }

        clearHistoryMap();
        playbackIndex = 0;
        const history = window.itemHistoryData.history;

        // Update button
        $('#btn-playback').html('<iconify-icon icon="solar:stop-circle-line-duotone" class="me-1"></iconify-icon> Stop');
        $('#btn-playback').removeClass('btn-success').addClass('btn-danger');

        // Play first route
        if (history.length > 0) {
            highlightRoute(history[0].id);
        }

        // Play next routes
        playbackInterval = setInterval(function() {
            playbackIndex++;
            if (playbackIndex >= history.length) {
                stopPlayback();
                return;
            }
            highlightRoute(history[playbackIndex].id);
        }, 2000); // 2 seconds per route
    }

    // Stop playback
    function stopPlayback() {
        if (playbackInterval) {
            clearInterval(playbackInterval);
            playbackInterval = null;
        }
        $('#btn-playback').html('<iconify-icon icon="solar:play-circle-line-duotone" class="me-1"></iconify-icon> Playback');
        $('#btn-playback').removeClass('btn-danger').addClass('btn-success');
    }

    // Load history from API
    function loadHistory(filters = {}) {
        if (!window.itemHistoryData || !window.itemHistoryData.itemId) return;

        stopPlayback();

        $.ajax({
            url: `/tracking/item-history/${window.itemHistoryData.itemId}/data`,
            method: 'GET',
            data: filters,
            success: function(response) {
                if (response.success) {
                    window.itemHistoryData.history = response.history;
                    window.itemHistoryData.itemDetails = response.itemDetails;
                    window.itemHistoryData.warehouses = response.warehouses;

                    // Reload timeline (you might want to use AJAX to update just the timeline)
                    location.reload();
                }
            },
            error: function() {
                console.error('Error loading history');
            }
        });
    }

    // Initialize
    if ($('#item-history-map').length) {
        initHistoryMap();
        addWarehouseMarkers();
        showAllRoutes();
    }

    // Timeline item click
    $(document).on('click', '.timeline-item', function() {
        const routeId = $(this).data('route-id');
        if (routeId) {
            highlightRoute(routeId);
        }
    });

    // Filter form submit
    $('#history-filter-form').on('submit', function(e) {
        e.preventDefault();
        const filters = {
            type: $('#filter-type').val(),
            warehouse_id: $('#filter-warehouse').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val()
        };
        loadHistory(filters);
    });

    // Reset filter
    $('#btn-reset-history-filter').on('click', function() {
        $('#history-filter-form')[0].reset();
        $('input[name="itemId"]').val(window.itemHistoryData.itemId);
        loadHistory({});
    });

    // Show all routes
    $('#btn-show-all-routes').on('click', function() {
        stopPlayback();
        showAllRoutes();
    });

    // Clear map
    $('#btn-clear-history-map').on('click', function() {
        stopPlayback();
        clearHistoryMap();
        $('.timeline-item').removeClass('active');
    });

    // Playback button
    $('#btn-playback').on('click', function() {
        startPlayback();
    });
});

