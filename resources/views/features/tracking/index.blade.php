@extends('layouts.app')

@section('title', 'Tracking Pengiriman')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #tracking-map {
        height: 600px;
        width: 100%;
        border-radius: 8px;
        z-index: 1;
    }
    .filter-panel {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .route-legend {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        min-width: 200px;
    }
    .map-container-wrapper {
        position: relative;
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin-right: 10px;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Tracking Pengiriman"
    :breadcrumb-title="'Tracking Pengiriman'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Navigation Tabs -->
<div class="card mb-4">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('tracking.index') }}">
                    <iconify-icon icon="solar:map-point-search-line-duotone" class="me-1"></iconify-icon>
                    Tracking Pengiriman
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('tracking.item-history') }}">
                    <iconify-icon icon="solar:history-line-duotone" class="me-1"></iconify-icon>
                    History Pengiriman Item
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Filter Panel -->
@include('features.tracking.partials.filter-panel')

<!-- Map Section -->
<div class="row">
    <div class="col-lg-9">
        @include('features.tracking.partials.map-section')
    </div>
    <div class="col-lg-3">
        @include('features.tracking.partials.legend')
    </div>
</div>

@endsection

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('assets/js/features/tracking.js') }}"></script>
@endpush

