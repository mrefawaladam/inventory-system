<!-- Recent Transactions -->
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="card-title mb-0">Transaksi Terbaru</h5>
          <a href="{{ route('reports.index') }}" class="btn btn-sm btn-primary">
            Lihat Laporan
            <iconify-icon icon="solar:arrow-right-bold-duotone" class="ms-1"></iconify-icon>
          </a>
        </div>
        
        @if($recentTransactions->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Tipe</th>
                  <th>Item</th>
                  <th>Dari</th>
                  <th>Ke</th>
                  <th>Qty</th>
                  <th>User</th>
                  <th>Tanggal</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentTransactions as $transaction)
                  <tr>
                    <td>
                      <code class="text-primary">{{ $transaction->transaction_code }}</code>
                    </td>
                    <td>
                      @if($transaction->type->value === 'INBOUND')
                        <span class="badge bg-primary-subtle text-primary">
                          <iconify-icon icon="solar:arrow-down-bold-duotone" class="me-1"></iconify-icon>
                          Inbound
                        </span>
                      @elseif($transaction->type->value === 'OUTBOUND')
                        <span class="badge bg-danger-subtle text-danger">
                          <iconify-icon icon="solar:arrow-up-bold-duotone" class="me-1"></iconify-icon>
                          Outbound
                        </span>
                      @else
                        <span class="badge bg-info-subtle text-info">
                          <iconify-icon icon="solar:transfer-horizontal-bold-duotone" class="me-1"></iconify-icon>
                          Transfer
                        </span>
                      @endif
                    </td>
                    <td>
                      <strong>{{ $transaction->item->name ?? 'N/A' }}</strong>
                    </td>
                    <td>
                      @if($transaction->fromLocation)
                        <small>
                          {{ $transaction->fromLocation->name }}
                          @if($transaction->fromLocation->warehouse)
                            <br><span class="text-muted">({{ $transaction->fromLocation->warehouse->name }})</span>
                          @endif
                        </small>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if($transaction->toLocation)
                        <small>
                          {{ $transaction->toLocation->name }}
                          @if($transaction->toLocation->warehouse)
                            <br><span class="text-muted">({{ $transaction->toLocation->warehouse->name }})</span>
                          @endif
                        </small>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-secondary-subtle text-secondary">
                        {{ number_format($transaction->quantity, 0, ',', '.') }}
                      </span>
                    </td>
                    <td>
                      <small>{{ $transaction->user->name ?? 'N/A' }}</small>
                    </td>
                    <td>
                      <small class="text-muted">
                        {{ $transaction->created_at->format('d M Y') }}<br>
                        {{ $transaction->created_at->format('H:i') }}
                      </small>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-4">
            <iconify-icon icon="solar:document-text-bold-duotone" class="fs-1 text-muted"></iconify-icon>
            <p class="text-muted mt-2 mb-0">Belum ada transaksi</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

