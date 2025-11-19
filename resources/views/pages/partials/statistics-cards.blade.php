<!-- Basic Statistics Cards -->
<div class="row">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <div class="avatar-lg rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center">
              <iconify-icon icon="solar:box-bold-duotone" class="fs-1 text-primary"></iconify-icon>
            </div>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="text-muted mb-1">Total Items</h6>
            <h3 class="mb-0">{{ number_format($totalItems, 0, ',', '.') }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <div class="avatar-lg rounded-circle bg-success-subtle d-flex align-items-center justify-content-center">
              <iconify-icon icon="solar:shop-2-bold-duotone" class="fs-1 text-success"></iconify-icon>
            </div>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="text-muted mb-1">Total Warehouses</h6>
            <h3 class="mb-0">{{ number_format($totalWarehouses, 0, ',', '.') }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <div class="avatar-lg rounded-circle bg-info-subtle d-flex align-items-center justify-content-center">
              <iconify-icon icon="solar:map-point-bold-duotone" class="fs-1 text-info"></iconify-icon>
            </div>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="text-muted mb-1">Total Locations</h6>
            <h3 class="mb-0">{{ number_format($totalLocations, 0, ',', '.') }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <div class="avatar-lg rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center">
              <iconify-icon icon="solar:archive-bold-duotone" class="fs-1 text-warning"></iconify-icon>
            </div>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="text-muted mb-1">Total Stocks</h6>
            <h3 class="mb-0">{{ number_format($totalStocks, 0, ',', '.') }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

