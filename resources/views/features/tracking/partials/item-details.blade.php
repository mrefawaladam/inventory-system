@if($itemDetails)
<div class="card mb-4">
    <div class="card-body item-info-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="text-white mb-2">{{ $itemDetails['name'] }}</h4>
                <div class="d-flex flex-wrap gap-3 text-white-50">
                    @if($itemDetails['sku'])
                        <span><strong>SKU:</strong> {{ $itemDetails['sku'] }}</span>
                    @endif
                    @if($itemDetails['barcode'])
                        <span><strong>Barcode:</strong> {{ $itemDetails['barcode'] }}</span>
                    @endif
                    <span><strong>Unit:</strong> {{ $itemDetails['unit'] ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="mb-2">
                    <small class="text-white-50 d-block">Total Stok</small>
                    <h3 class="text-white mb-0">{{ number_format($itemDetails['total_stock'], 0, ',', '.') }}</h3>
                </div>
                @if($itemDetails['is_low_stock'])
                    <span class="badge bg-warning text-dark">
                        <iconify-icon icon="solar:danger-triangle-bold-duotone" class="me-1"></iconify-icon>
                        Stok Rendah
                    </span>
                @else
                    <span class="badge bg-success">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="me-1"></iconify-icon>
                        Stok Normal
                    </span>
                @endif
            </div>
        </div>

        @if(count($itemDetails['current_locations']) > 0)
        <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
        <div>
            <small class="text-white-50 d-block mb-2">Lokasi Saat Ini:</small>
            <div class="d-flex flex-wrap gap-2">
                @foreach($itemDetails['current_locations'] as $loc)
                    <span class="badge bg-light text-dark">
                        {{ $loc['location_path'] }} ({{ $loc['warehouse_name'] }}) -
                        {{ number_format($loc['quantity'], 0, ',', '.') }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endif

