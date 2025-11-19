<!-- Top Items by Transaction -->
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="card-title mb-0">Item Paling Banyak Ditransaksikan</h5>
      <iconify-icon icon="solar:star-bold-duotone" class="fs-4 text-warning"></iconify-icon>
    </div>
    
    @if($topItems->count() > 0)
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Item</th>
              <th>Jumlah Transaksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($topItems as $index => $transaction)
              <tr>
                <td>
                  <span class="badge bg-primary-subtle text-primary">
                    {{ $index + 1 }}
                  </span>
                </td>
                <td>
                  <div>
                    <strong>{{ $transaction->item->name ?? 'N/A' }}</strong>
                    @if($transaction->item && $transaction->item->sku)
                      <br><small class="text-muted">SKU: {{ $transaction->item->sku }}</small>
                    @endif
                  </div>
                </td>
                <td>
                  <span class="badge bg-info-subtle text-info">
                    {{ number_format($transaction->transaction_count, 0, ',', '.') }}x
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="text-center py-4">
        <iconify-icon icon="solar:box-bold-duotone" class="fs-1 text-muted"></iconify-icon>
        <p class="text-muted mt-2 mb-0">Belum ada data transaksi</p>
      </div>
    @endif
  </div>
</div>

