<!-- Low Stock Items -->
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="card-title mb-0">Item Stok Rendah</h5>
      <span class="badge bg-warning-subtle text-warning">{{ $lowStockItems->count() }}</span>
    </div>
    
    @if($lowStockItems->count() > 0)
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Item</th>
              <th>Stok</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($lowStockItems as $item)
              <tr>
                <td>
                  <div>
                    <strong>{{ $item->name }}</strong>
                    @if($item->sku)
                      <br><small class="text-muted">SKU: {{ $item->sku }}</small>
                    @endif
                  </div>
                </td>
                <td>
                  <span class="badge bg-danger-subtle text-danger">
                    {{ number_format($item->stocks->sum('quantity'), 0, ',', '.') }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-warning text-white">
                    <iconify-icon icon="solar:danger-triangle-bold-duotone" class="me-1"></iconify-icon>
                    Stok Rendah
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="text-center py-4">
        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-1 text-success"></iconify-icon>
        <p class="text-muted mt-2 mb-0">Tidak ada item dengan stok rendah</p>
      </div>
    @endif
  </div>
</div>

