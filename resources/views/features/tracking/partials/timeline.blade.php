<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Timeline</h5>
            <small class="text-muted">{{ count($history) }} pergerakan</small>
        </div>
        <div class="timeline-container" id="timeline-container">
            @if(count($history) > 0)
                @foreach($history as $index => $movement)
                    @php
                        $status = $movement['delivery_status'] ?? 'pending';
                        $statusLabel = $movement['delivery_status_label'] ?? ($status === 'delivered' ? 'Sudah Dikirim' : 'Belum Dikirim');
                        $statusClass = $status === 'delivered' ? 'success' : 'danger';
                    @endphp
                    <div class="timeline-item {{ strtolower($movement['type']) }}"
                         data-sequence="{{ $movement['sequence'] }}"
                         data-route-id="{{ $movement['id'] }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong class="d-block">{{ $movement['transaction_code'] }}</strong>
                                <small class="text-muted">{{ $movement['created_at_formatted'] }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $movement['type'] == 'INBOUND' ? 'warning' : ($movement['type'] == 'OUTBOUND' ? 'secondary' : 'primary') }}">
                                    {{ $movement['type_label'] }}
                                </span>
                                <span class="badge bg-{{ $statusClass }} ms-1">{{ $statusLabel }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Dari:</small>
                            <strong>{{ $movement['from']['warehouse_name'] ?? 'N/A' }}</strong>
                            @if($movement['from']['location_path'])
                                <br><small class="text-muted">{{ $movement['from']['location_path'] }}</small>
                            @endif
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Ke:</small>
                            <strong>{{ $movement['to']['warehouse_name'] ?? 'N/A' }}</strong>
                            @if($movement['to']['location_path'])
                                <br><small class="text-muted">{{ $movement['to']['location_path'] }}</small>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Qty:</strong> {{ number_format($movement['quantity'], 0, ',', '.') }}</span>
                            <span class="text-muted"><strong>Jarak:</strong> {{ $movement['distance_km'] }} km</span>
                        </div>
                        @if($movement['user']['name'])
                            <div class="mt-2">
                                <small class="text-muted">User: {{ $movement['user']['name'] }}</small>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <iconify-icon icon="solar:document-text-line-duotone" class="fs-1 text-muted"></iconify-icon>
                    <p class="text-muted mt-2 mb-0">Tidak ada history pergerakan</p>
                </div>
            @endif
        </div>
    </div>
</div>

