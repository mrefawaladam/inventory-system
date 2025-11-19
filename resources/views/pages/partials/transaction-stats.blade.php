<!-- Transaction Statistics -->
<div class="row">
  <div class="col-lg-12 mb-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4">Statistik Transaksi</h5>
        
        <div class="row">
          <!-- Total Transactions -->
          <div class="col-md-3 mb-3">
            <div class="border rounded p-3 text-center">
              <h6 class="text-muted mb-2">Total Transaksi</h6>
              <h3 class="mb-0">{{ number_format($totalTransactions, 0, ',', '.') }}</h3>
            </div>
          </div>

          <!-- Transaction Types -->
          <div class="col-md-3 mb-3">
            <div class="border rounded p-3 text-center bg-primary-subtle">
              <h6 class="text-muted mb-2">Inbound</h6>
              <h3 class="mb-0 text-primary">{{ number_format($inboundCount, 0, ',', '.') }}</h3>
            </div>
          </div>

          <div class="col-md-3 mb-3">
            <div class="border rounded p-3 text-center bg-danger-subtle">
              <h6 class="text-muted mb-2">Outbound</h6>
              <h3 class="mb-0 text-danger">{{ number_format($outboundCount, 0, ',', '.') }}</h3>
            </div>
          </div>

          <div class="col-md-3 mb-3">
            <div class="border rounded p-3 text-center bg-info-subtle">
              <h6 class="text-muted mb-2">Transfer</h6>
              <h3 class="mb-0 text-info">{{ number_format($transferCount, 0, ',', '.') }}</h3>
            </div>
          </div>
        </div>

        <hr>

        <!-- Today's Transactions -->
        <div class="row mt-3">
          <div class="col-md-12">
            <h6 class="mb-3">Transaksi Hari Ini</h6>
            <div class="row">
              <div class="col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <iconify-icon icon="solar:arrow-down-bold-duotone" class="fs-4 text-primary me-2"></iconify-icon>
                  <div>
                    <small class="text-muted d-block">Inbound</small>
                    <strong>{{ number_format($todayInbound, 0, ',', '.') }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <iconify-icon icon="solar:arrow-up-bold-duotone" class="fs-4 text-danger me-2"></iconify-icon>
                  <div>
                    <small class="text-muted d-block">Outbound</small>
                    <strong>{{ number_format($todayOutbound, 0, ',', '.') }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <iconify-icon icon="solar:transfer-horizontal-bold-duotone" class="fs-4 text-info me-2"></iconify-icon>
                  <div>
                    <small class="text-muted d-block">Transfer</small>
                    <strong>{{ number_format($todayTransfer, 0, ',', '.') }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <hr>

        <!-- This Week's Transactions -->
        <div class="row mt-3">
          <div class="col-md-12">
            <h6 class="mb-3">Transaksi Minggu Ini</h6>
            <div class="row">
              <div class="col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <iconify-icon icon="solar:arrow-down-bold-duotone" class="fs-4 text-primary me-2"></iconify-icon>
                  <div>
                    <small class="text-muted d-block">Inbound</small>
                    <strong>{{ number_format($weekInbound, 0, ',', '.') }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <iconify-icon icon="solar:arrow-up-bold-duotone" class="fs-4 text-danger me-2"></iconify-icon>
                  <div>
                    <small class="text-muted d-block">Outbound</small>
                    <strong>{{ number_format($weekOutbound, 0, ',', '.') }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="d-flex align-items-center">
                  <iconify-icon icon="solar:transfer-horizontal-bold-duotone" class="fs-4 text-info me-2"></iconify-icon>
                  <div>
                    <small class="text-muted d-block">Transfer</small>
                    <strong>{{ number_format($weekTransfer, 0, ',', '.') }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

