<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Peta History Pergerakan</h5>
            <div>
                <button type="button" id="btn-show-all-routes" class="btn btn-sm btn-outline-primary">
                    <iconify-icon icon="solar:route-line-duotone" class="me-1"></iconify-icon>
                    Tampilkan Semua
                </button>
                <button type="button" id="btn-clear-history-map" class="btn btn-sm btn-outline-secondary">
                    <iconify-icon icon="solar:close-circle-line-duotone" class="me-1"></iconify-icon>
                    Clear
                </button>
            </div>
        </div>
        <div id="item-history-map"></div>
        <div class="mt-3">
            <small class="text-muted">
                <iconify-icon icon="solar:info-circle-line-duotone" class="me-1"></iconify-icon>
                Menampilkan <strong id="history-count">{{ count($history) }}</strong> pergerakan dalam timeline
            </small>
        </div>
    </div>
</div>

@if($item)
<script>
// Pass data to JavaScript
window.itemHistoryData = {
    itemId: {{ $itemId ?? 'null' }},
    history: @json($history ?? []),
    warehouses: @json($warehousesMap ?? []),
    itemDetails: @json($itemDetails ?? null),
    filters: @json($filters ?? [])
};
</script>
@else
<script>
// Initialize empty data
window.itemHistoryData = {
    itemId: null,
    history: [],
    warehouses: [],
    itemDetails: null,
    filters: {}
};
</script>
@endif

