@extends('layouts.app')

@section('title', 'Dashboard')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="card card-body py-3">
  <div class="row align-items-center">
    <div class="col-12">
      <div class="d-sm-flex align-items-center justify-space-between">
        <h4 class="mb-4 mb-sm-0 card-title">Dashboard</h4>
        <nav aria-label="breadcrumb" class="ms-auto">
          <ol class="breadcrumb">
            <li class="breadcrumb-item" aria-current="page">
              <span class="badge fw-medium fs-2 bg-primary-subtle text-primary">Dashboard</span>
            </li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Statistics Cards -->
@include('pages.partials.statistics-cards')

<!-- Transaction Statistics -->
@include('pages.partials.transaction-stats')

<!-- Chart Section -->
@include('pages.partials.chart-section')

<!-- Low Stock Items & Top Items -->
<div class="row">
  <div class="col-lg-6">
    @include('pages.partials.low-stock-items')
      </div>
  <div class="col-lg-6">
    @include('pages.partials.top-items')
  </div>
</div>

<!-- Recent Transactions -->
@include('pages.partials.recent-transactions')

@endsection
