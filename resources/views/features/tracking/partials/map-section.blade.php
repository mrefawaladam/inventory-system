<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Peta Tracking Transaksi</h5>
            <div>
                <button type="button" id="btn-refresh-map" class="btn btn-sm btn-outline-primary">
                    <iconify-icon icon="solar:refresh-line-duotone" class="me-1"></iconify-icon>
                    Refresh
                </button>
                <button type="button" id="btn-clear-routes" class="btn btn-sm btn-outline-secondary">
                    <iconify-icon icon="solar:close-circle-line-duotone" class="me-1"></iconify-icon>
                    Clear Routes
                </button>
            </div>
        </div>
        <div class="position-relative">
            <div id="tracking-map"></div>
        </div>
        <div class="mt-3">
            <small class="text-muted">
                <iconify-icon icon="solar:info-circle-line-duotone" class="me-1"></iconify-icon>
                Menampilkan <strong id="route-count">{{ count($routes) }}</strong> route transaksi
            </small>
        </div>
    </div>
</div>

<script>
// Pass data to JavaScript
window.trackingData = {
    routes: @json($routes),
    warehouses: @json($warehousesMap),
    filters: @json($filters)
};
</script>

