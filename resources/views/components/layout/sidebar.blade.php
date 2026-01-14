<aside class="left-sidebar with-vertical">
  <div>
    <!-- Start Vertical Layout Sidebar -->
    <div>
      <div class="brand-logo d-flex align-items-center">
        <a href="{{ route('dashboard') }}" class="text-nowrap logo-img">
        </a>
      </div>

      <!-- Dashboard -->
      <nav class="sidebar-nav scroll-sidebar" data-simplebar>
        <ul class="sidebar-menu" id="sidebarnav">
          <!-- Dashboards -->
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Dashboards</span>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('dashboard') }}" id="get-url" aria-expanded="false">
              <iconify-icon icon="solar:widget-add-line-duotone" class=""></iconify-icon>
              <span class="hide-menu">Dashboard</span>
            </a>
          </li>

          <!-- Pages -->
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Pages</span>
          </li>

          @if(auth()->user()->hasRole('admin'))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('users.index') }}">
              <iconify-icon icon="solar:users-group-two-rounded-line-duotone"></iconify-icon>
              <span class="hide-menu">Kelola Pengguna</span>
            </a>
          </li>
          @endif

          @if(!auth()->user()->hasRole('user'))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('suppliers.index') }}">
              <iconify-icon icon="solar:shop-2-line-duotone"></iconify-icon>
              <span class="hide-menu">Kelola Supplier</span>
            </a>
          </li>
          @endif

          @if(!auth()->user()->hasRole('user'))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('customers.index') }}">
              <iconify-icon icon="solar:users-group-rounded-line-duotone"></iconify-icon>
              <span class="hide-menu">Kelola Customer</span>
            </a>
          </li>
          @endif

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('warehouses.index') }}">
              <iconify-icon icon="solar:home-smile-line-duotone"></iconify-icon>
              <span class="hide-menu">Kelola Sekolah</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('locations.index') }}">
              <iconify-icon icon="solar:map-point-line-duotone"></iconify-icon>
              <span class="hide-menu">Kelola Lokasi</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('items.index') }}">
              <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
              <span class="hide-menu">Kelola Barang</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('stocks.index') }}">
              <iconify-icon icon="solar:layers-line-duotone"></iconify-icon>
              <span class="hide-menu">Kelola Stok</span>
            </a>
          </li>

          <!-- Transactions -->
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Transaksi</span>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('inbound.index') }}">
              <iconify-icon icon="solar:arrow-down-line-duotone"></iconify-icon>
              <span class="hide-menu">Barang Masuk</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('outbound.index') }}">
              <iconify-icon icon="solar:arrow-up-line-duotone"></iconify-icon>
              <span class="hide-menu">Barang Keluar</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('transfer.index') }}">
              <iconify-icon icon="solar:transfer-horizontal-line-duotone"></iconify-icon>
              <span class="hide-menu">Pindah Barang</span>
            </a>
          </li>

          <!-- Reports -->
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Laporan</span>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('reports.index') }}">
              <iconify-icon icon="solar:document-text-line-duotone"></iconify-icon>
              <span class="hide-menu">Laporan Transaksi</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('tracking.index') }}">
              <iconify-icon icon="solar:map-point-search-line-duotone"></iconify-icon>
              <span class="hide-menu">Lacak Pengiriman</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('heatmap.index') }}">
              <iconify-icon icon="solar:graph-up-line-duotone"></iconify-icon>
              <span class="hide-menu">Peta Analitik</span>
            </a>
          </li>

          <!-- Keluar -->
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Keluar</span>
          </li>

          <li class="sidebar-item">
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="submit" class="sidebar-link border-0 bg-transparent w-100 text-start">
                <iconify-icon icon="solar:logout-2-line-duotone"></iconify-icon>
                <span class="hide-menu">Logout</span>
              </button>
            </form>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</aside>
