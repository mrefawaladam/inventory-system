@extends('layouts.app')

@section('title', 'Heatmap Analytics')

@push('styles')
<style>
    .heatmap-container {
        position: relative;
        margin: 20px 0;
    }
    .heatmap-item {
        padding: 12px 16px;
        margin: 8px 0;
        border-radius: 6px;
        border-left: 4px solid;
        transition: all 0.3s;
        cursor: pointer;
    }
    .heatmap-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .heatmap-item .item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .heatmap-item .item-name {
        font-weight: 600;
        font-size: 1rem;
    }
    .heatmap-item .item-count {
        font-size: 1.25rem;
        font-weight: 700;
    }
    .heatmap-item .item-details {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 4px;
    }
    .intensity-legend {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
    }
    .intensity-legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .intensity-color {
        width: 30px;
        height: 20px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .traffic-connection {
        display: flex;
        align-items: center;
        padding: 12px;
        margin: 8px 0;
        background: #fff;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
    .traffic-connection .connection-line {
        flex: 1;
        height: 2px;
        margin: 0 15px;
        position: relative;
    }
    .traffic-connection .connection-arrow {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 8px solid;
        border-top: 6px solid transparent;
        border-bottom: 6px solid transparent;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Heatmap Analytics"
    :breadcrumb-title="'Heatmap Analytics'"
/>

<!-- Filter Section -->
<div class="filter-section">
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <i class="ti ti-info-circle"></i> 
                <strong>Data yang ditampilkan:</strong> Transaksi yang dibuat oleh <strong>{{ auth()->user()->name }}</strong>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <label for="start_date" class="form-label">Tanggal Mulai</label>
            <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">Tanggal Akhir</label>
            <input type="date" class="form-control" id="end_date" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="button" class="btn btn-primary w-100" id="btn-apply-filter">
                <i class="ti ti-filter"></i> Terapkan Filter
            </button>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                <i class="ti ti-refresh"></i> Reset
            </button>
        </div>
    </div>
</div>

<!-- Tabs for different heatmaps -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="item-movement-tab" data-bs-toggle="tab" data-bs-target="#item-movement" type="button" role="tab">
            <i class="ti ti-package"></i> Item Movement
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="warehouse-activity-tab" data-bs-toggle="tab" data-bs-target="#warehouse-activity" type="button" role="tab">
            <i class="ti ti-building-warehouse"></i> Aktivitas Sekolah
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="traffic-tab" data-bs-toggle="tab" data-bs-target="#traffic" type="button" role="tab">
            <i class="ti ti-route"></i> Traffic Visualization
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="time-based-tab" data-bs-toggle="tab" data-bs-target="#time-based" type="button" role="tab">
            <i class="ti ti-chart-line"></i> Time-Based Activity
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Item Movement Heatmap -->
    <div class="tab-pane fade show active" id="item-movement" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Heatmap Item Paling Sering Dipindahkan</h5>
                <div class="intensity-legend">
                    <span class="fw-bold">Intensitas:</span>
                    <div class="intensity-legend-item">
                        <div class="intensity-color" style="background: #d1ecf1;"></div>
                        <span>Rendah</span>
                    </div>
                    <div class="intensity-legend-item">
                        <div class="intensity-color" style="background: #0d6efd;"></div>
                        <span>Sedang</span>
                    </div>
                    <div class="intensity-legend-item">
                        <div class="intensity-color" style="background: #0a58ca;"></div>
                        <span>Tinggi</span>
                    </div>
                </div>
                <div id="item-movement-heatmap" class="heatmap-container">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Activity Heatmap -->
    <div class="tab-pane fade" id="warehouse-activity" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Heatmap Sekolah Paling Aktif</h5>
                <div class="intensity-legend">
                    <span class="fw-bold">Intensitas:</span>
                    <div class="intensity-legend-item">
                        <div class="intensity-color" style="background: #d1ecf1;"></div>
                        <span>Rendah</span>
                    </div>
                    <div class="intensity-legend-item">
                        <div class="intensity-color" style="background: #0d6efd;"></div>
                        <span>Sedang</span>
                    </div>
                    <div class="intensity-legend-item">
                        <div class="intensity-color" style="background: #0a58ca;"></div>
                        <span>Tinggi</span>
                    </div>
                </div>
                <div id="warehouse-activity-heatmap" class="heatmap-container">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Traffic Visualization -->
    <div class="tab-pane fade" id="traffic" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Visualisasi Traffic Perpindahan</h5>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="traffic-type" id="traffic-warehouse" value="warehouse" checked>
                        <label class="btn btn-outline-primary" for="traffic-warehouse">Sekolah</label>

                        <input type="radio" class="btn-check" name="traffic-type" id="traffic-location" value="location">
                        <label class="btn btn-outline-primary" for="traffic-location">Location</label>
                    </div>
                </div>
                <div id="traffic-visualization" class="heatmap-container">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time-Based Activity -->
    <div class="tab-pane fade" id="time-based" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Aktivitas Berdasarkan Waktu</h5>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="time-group" id="time-day" value="day" checked>
                        <label class="btn btn-outline-primary" for="time-day">Harian</label>

                        <input type="radio" class="btn-check" name="time-group" id="time-week" value="week">
                        <label class="btn btn-outline-primary" for="time-week">Mingguan</label>

                        <input type="radio" class="btn-check" name="time-group" id="time-month" value="month">
                        <label class="btn btn-outline-primary" for="time-month">Bulanan</label>
                    </div>
                </div>
                <canvas id="time-based-chart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('assets/js/helpers/toast.js') }}"></script>
<script>
$(document).ready(function() {
    let timeBasedChart = null;

    // Get color based on intensity
    function getIntensityColor(intensity) {
        if (intensity >= 0.7) {
            return '#0a58ca'; // High intensity - dark blue
        } else if (intensity >= 0.4) {
            return '#0d6efd'; // Medium intensity - blue
        } else {
            return '#d1ecf1'; // Low intensity - light blue
        }
    }

    // Load Item Movement Heatmap
    function loadItemMovementHeatmap() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const container = $('#item-movement-heatmap');

        container.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: "{{ route('heatmap.itemMovement') }}",
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(item) {
                        const color = getIntensityColor(item.intensity);
                        html += `
                            <div class="heatmap-item" style="border-left-color: ${color}; background: linear-gradient(to right, ${color}15, transparent);">
                                <div class="item-header">
                                    <div class="item-name">${item.item_name}</div>
                                    <div class="item-count" style="color: ${color};">${item.movement_count}</div>
                                </div>
                                <div class="item-details">
                                    SKU: ${item.item_sku} | Total Quantity: ${item.total_quantity}
                                </div>
                            </div>
                        `;
                    });
                    container.html(html);
                } else {
                    container.html('<div class="alert alert-info text-center">Tidak ada data untuk periode yang dipilih</div>');
                }
            },
            error: function(xhr) {
                container.html('<div class="alert alert-danger">Gagal memuat data heatmap item</div>');
                if (window.Toast) {
                    Toast.error('Gagal memuat data heatmap item');
                }
            }
        });
    }

    // Load Warehouse Activity Heatmap
    function loadWarehouseActivityHeatmap() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const container = $('#warehouse-activity-heatmap');

        container.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: "{{ route('heatmap.warehouseActivity') }}",
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(warehouse) {
                        const color = getIntensityColor(warehouse.intensity);
                        html += `
                            <div class="heatmap-item" style="border-left-color: ${color}; background: linear-gradient(to right, ${color}15, transparent);">
                                <div class="item-header">
                                    <div class="item-name">${warehouse.warehouse_name}</div>
                                    <div class="item-count" style="color: ${color};">${warehouse.transaction_count}</div>
                                </div>
                                <div class="item-details">
                                    Total Quantity: ${warehouse.total_quantity} | Unique Items: ${warehouse.unique_items}
                                </div>
                            </div>
                        `;
                    });
                    container.html(html);
                } else {
                    container.html('<div class="alert alert-info text-center">Tidak ada data untuk periode yang dipilih</div>');
                }
            },
            error: function(xhr) {
                container.html('<div class="alert alert-danger">Gagal memuat data heatmap warehouse</div>');
                if (window.Toast) {
                    Toast.error('Gagal memuat data heatmap warehouse');
                }
            }
        });
    }

    // Load Traffic Visualization
    function loadTrafficVisualization() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const type = $('input[name="traffic-type"]:checked').val();
        const container = $('#traffic-visualization');

        container.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: "{{ route('heatmap.traffic') }}",
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate,
                type: type
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(traffic) {
                        const color = getIntensityColor(traffic.intensity);
                        if (type === 'warehouse') {
                            html += `
                                <div class="traffic-connection">
                                    <div class="fw-bold">${traffic.from_warehouse_name}</div>
                                    <div class="connection-line" style="background: ${color};">
                                        <div class="connection-arrow" style="border-left-color: ${color};"></div>
                                    </div>
                                    <div class="fw-bold">${traffic.to_warehouse_name}</div>
                                    <div class="ms-3">
                                        <span class="badge" style="background: ${color};">${traffic.count} perpindahan</span>
                                        <small class="d-block text-muted">Qty: ${traffic.total_quantity}</small>
                                    </div>
                                </div>
                            `;
                        } else {
                            html += `
                                <div class="traffic-connection">
                                    <div class="fw-bold" style="font-size: 0.875rem;">${traffic.from_location_path}</div>
                                    <div class="connection-line" style="background: ${color};">
                                        <div class="connection-arrow" style="border-left-color: ${color};"></div>
                                    </div>
                                    <div class="fw-bold" style="font-size: 0.875rem;">${traffic.to_location_path}</div>
                                    <div class="ms-3">
                                        <span class="badge" style="background: ${color};">${traffic.count} perpindahan</span>
                                        <small class="d-block text-muted">Qty: ${traffic.total_quantity}</small>
                                    </div>
                                </div>
                            `;
                        }
                    });
                    container.html(html);
                } else {
                    container.html('<div class="alert alert-info text-center">Tidak ada data untuk periode yang dipilih</div>');
                }
            },
            error: function(xhr) {
                container.html('<div class="alert alert-danger">Gagal memuat data traffic</div>');
                if (window.Toast) {
                    Toast.error('Gagal memuat data traffic');
                }
            }
        });
    }

    // Load Time-Based Activity Chart
    function loadTimeBasedActivity() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const groupBy = $('input[name="time-group"]:checked').val();

        $.ajax({
            url: "{{ route('heatmap.timeBased') }}",
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate,
                group_by: groupBy
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    const labels = response.data.map(item => item.period || item.date);
                    const counts = response.data.map(item => item.count);
                    const quantities = response.data.map(item => item.total_quantity);

                    const ctx = document.getElementById('time-based-chart').getContext('2d');
                    
                    if (timeBasedChart) {
                        timeBasedChart.destroy();
                    }

                    timeBasedChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Jumlah Transaksi',
                                    data: counts,
                                    borderColor: 'rgb(13, 110, 253)',
                                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Total Quantity',
                                    data: quantities,
                                    borderColor: 'rgb(220, 53, 69)',
                                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                }
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    beginAtZero: true,
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    beginAtZero: true,
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                }
                            }
                        }
                    });
                } else {
                    if (timeBasedChart) {
                        timeBasedChart.destroy();
                    }
                    const ctx = document.getElementById('time-based-chart').getContext('2d');
                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                }
            },
            error: function(xhr) {
                if (window.Toast) {
                    Toast.error('Gagal memuat data aktivitas waktu');
                }
            }
        });
    }

    // Apply filter
    $('#btn-apply-filter').on('click', function() {
        const activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');
        if (activeTab === '#item-movement') {
            loadItemMovementHeatmap();
        } else if (activeTab === '#warehouse-activity') {
            loadWarehouseActivityHeatmap();
        } else if (activeTab === '#traffic') {
            loadTrafficVisualization();
        } else if (activeTab === '#time-based') {
            loadTimeBasedActivity();
        }
    });

    // Reset filter
    $('#btn-reset-filter').on('click', function() {
        $('#start_date').val('{{ date('Y-m-d', strtotime('-30 days')) }}');
        $('#end_date').val('{{ date('Y-m-d') }}');
        $('#btn-apply-filter').click();
    });

    // Tab change handler
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('data-bs-target');
        if (target === '#item-movement') {
            loadItemMovementHeatmap();
        } else if (target === '#warehouse-activity') {
            loadWarehouseActivityHeatmap();
        } else if (target === '#traffic') {
            loadTrafficVisualization();
        } else if (target === '#time-based') {
            loadTimeBasedActivity();
        }
    });

    // Traffic type change
    $('input[name="traffic-type"]').on('change', function() {
        loadTrafficVisualization();
    });

    // Time group change
    $('input[name="time-group"]').on('change', function() {
        loadTimeBasedActivity();
    });

    // Load initial data
    loadItemMovementHeatmap();
});
</script>
@endpush

