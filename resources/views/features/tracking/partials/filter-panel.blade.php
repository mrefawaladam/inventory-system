<div class="card mb-4">
    <div class="card-body filter-panel">
        <h5 class="card-title mb-4">Filter Transaksi</h5>
        <form id="filter-form" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tipe Transaksi</label>
                <select name="type" id="filter-type" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="INBOUND" {{ $filters['type'] == 'INBOUND' ? 'selected' : '' }}>Inbound</option>
                    <option value="OUTBOUND" {{ $filters['type'] == 'OUTBOUND' ? 'selected' : '' }}>Outbound</option>
                    <option value="TRANSFER" {{ $filters['type'] == 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Item</label>
                <select name="item_id" id="filter-item" class="form-select">
                    <option value="">Semua Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $filters['item_id'] == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} @if($item->sku)({{ $item->sku }})@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Warehouse</label>
                <select name="warehouse_id" id="filter-warehouse" class="form-select">
                    <option value="">Semua Warehouse</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ $filters['warehouse_id'] == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Dari</label>
                <input type="date" name="date_from" id="filter-date-from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" name="date_to" id="filter-date-to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <iconify-icon icon="solar:magnifer-line-duotone" class="me-1"></iconify-icon>
                    Filter
                </button>
                <button type="button" id="btn-reset-filter" class="btn btn-secondary">
                    <iconify-icon icon="solar:refresh-line-duotone" class="me-1"></iconify-icon>
                    Reset
                </button>
            </div>
        </form>
    </div>
</div>

