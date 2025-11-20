@extends('layouts.app')

@section('title', 'History Pengiriman Item')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #item-history-map {
        height: 500px;
        width: 100%;
        border-radius: 8px;
        z-index: 1;
    }
    .timeline-container {
        max-height: 600px;
        overflow-y: auto;
    }
    .timeline-item {
        padding: 15px;
        border-left: 3px solid #ddd;
        margin-bottom: 15px;
        background: #f8f9fa;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .timeline-item:hover {
        background: #e9ecef;
        border-left-color: #007bff;
    }
    .timeline-item.active {
        background: #e7f3ff;
        border-left-color: #007bff;
    }
    .timeline-item.inbound {
        border-left-color: #ffc107;
    }
    .timeline-item.outbound {
        border-left-color: #6c757d;
    }
    .timeline-item.transfer {
        border-left-color: #007bff;
    }
    .item-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        padding: 20px;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="History Pengiriman Item"
    :breadcrumb-title="'History Pengiriman Item'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Navigation Tabs -->
<div class="card mb-4">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('tracking.index') }}">
                    <iconify-icon icon="solar:map-point-search-line-duotone" class="me-1"></iconify-icon>
                    Tracking Pengiriman
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('tracking.item-history') }}">
                    <iconify-icon icon="solar:history-line-duotone" class="me-1"></iconify-icon>
                    History Pengiriman Item
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Item Selection & Info -->
@include('features.tracking.partials.item-selection')

@if($item)
    <!-- Item Details -->
    @include('features.tracking.partials.item-details')

    <!-- Filter Panel -->
    @include('features.tracking.partials.history-filter')

    <!-- Map & Timeline -->
    <div class="row">
        <div class="col-lg-8">
            @include('features.tracking.partials.history-map')
        </div>
        <div class="col-lg-4">
            @include('features.tracking.partials.timeline')
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <iconify-icon icon="solar:box-search-line-duotone" class="fs-1 text-muted mb-3"></iconify-icon>
            <h5 class="text-muted">Pilih Item untuk Melihat History</h5>
            <p class="text-muted">Silakan pilih item dari dropdown di atas untuk melihat timeline pergerakan</p>
        </div>
    </div>
@endif

@endsection

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('assets/js/features/item-history.js') }}"></script>
@endpush

