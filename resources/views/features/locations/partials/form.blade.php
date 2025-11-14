@php
    $isEdit = isset($location) && $location !== null;
    $selectedWarehouseId = $isEdit ? $location->warehouse_id : (old('warehouse_id') ?? null);
    $selectedType = $isEdit ? $location->type->value : (old('type') ?? 'ZONE');
    $selectedParentId = $isEdit ? $location->parent_id : (old('parent_id') ?? null);
@endphp

<form id="location-form" action="{{ $formAction }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="warehouse_id" class="form-label">Gudang <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="warehouse_id"
            name="warehouse_id"
            required
        >
            <option value="">Pilih Gudang</option>
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" {{ $selectedWarehouseId == $warehouse->id ? 'selected' : '' }}>
                    {{ $warehouse->name }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback d-none" id="warehouse_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="type" class="form-label">Tipe Lokasi <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="type"
            name="type"
            required
        >
            <option value="ZONE" {{ $selectedType === 'ZONE' ? 'selected' : '' }}>Zone</option>
            <option value="RACK" {{ $selectedType === 'RACK' ? 'selected' : '' }}>Rack</option>
            <option value="SLOT" {{ $selectedType === 'SLOT' ? 'selected' : '' }}>Slot</option>
        </select>
        <small class="text-muted">Zone: Area utama gudang | Rack: Rak dalam zone | Slot: Slot dalam rack</small>
        <div class="invalid-feedback d-none" id="type-error"></div>
    </div>

    <div class="mb-3" id="parent-zone-container" style="display: none;">
        <label for="parent_zone_id" class="form-label">Zone <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="parent_zone_id"
        >
            <option value="">Pilih Zone</option>
        </select>
        <input type="hidden" id="parent_id" name="parent_id" value="{{ $selectedParentId }}">
        <div class="invalid-feedback d-none" id="parent_id-error"></div>
    </div>

    <div class="mb-3" id="parent-rack-container" style="display: none;">
        <label for="parent_rack_id" class="form-label">Rack <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="parent_rack_id"
        >
            <option value="">Pilih Rack</option>
        </select>
        <div class="invalid-feedback d-none" id="parent_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="code" class="form-label">Kode</label>
        <input
            type="text"
            class="form-control"
            id="code"
            name="code"
            value="{{ $isEdit ? $location->code : old('code') }}"
            placeholder="Kosongkan untuk auto-generate"
        >
        <small class="text-muted">Biarkan kosong untuk generate otomatis (contoh: R01-S01)</small>
        <div class="invalid-feedback d-none" id="code-error"></div>
    </div>

    <div class="mb-3">
        <label for="capacity" class="form-label">Kapasitas <span class="text-danger">*</span></label>
        <input
            type="number"
            class="form-control"
            id="capacity"
            name="capacity"
            value="{{ $isEdit ? $location->capacity : (old('capacity') ?? 0) }}"
            min="0"
            required
        >
        <small class="text-muted">Kapasitas maksimal yang dapat ditampung</small>
        <div class="invalid-feedback d-none" id="capacity-error"></div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea
            class="form-control"
            id="description"
            name="description"
            rows="3"
        >{{ $isEdit ? $location->description : old('description') }}</textarea>
        <div class="invalid-feedback d-none" id="description-error"></div>
    </div>
</form>

<script>
(function() {
    const warehouseSelect = $('#warehouse_id');
    const typeSelect = $('#type');
    const parentZoneContainer = $('#parent-zone-container');
    const parentRackContainer = $('#parent-rack-container');
    const parentZoneSelect = $('#parent_zone_id');
    const parentRackSelect = $('#parent_rack_id');

    // Function to load zones
    function loadZones(warehouseId, selectedId = null) {
        if (!warehouseId) {
            parentZoneSelect.html('<option value="">Pilih Zone</option>');
            return;
        }

        $.ajax({
            url: "{{ route('locations.get-by-warehouse') }}",
            method: 'GET',
            data: {
                warehouse_id: warehouseId,
                type: 'ZONE'
            },
            success: function(zones) {
                let html = '<option value="">Pilih Zone</option>';
                zones.forEach(function(zone) {
                    const selected = selectedId == zone.id ? 'selected' : '';
                    html += `<option value="${zone.id}" ${selected}>${zone.code} - ${zone.description || ''}</option>`;
                });
                parentZoneSelect.html(html);
            }
        });
    }

    // Function to load racks
    function loadRacks(zoneId, selectedId = null) {
        if (!zoneId) {
            parentRackSelect.html('<option value="">Pilih Rack</option>');
            return;
        }

        $.ajax({
            url: "{{ route('locations.get-by-warehouse') }}",
            method: 'GET',
            data: {
                parent_id: zoneId,
                type: 'RACK'
            },
            success: function(racks) {
                let html = '<option value="">Pilih Rack</option>';
                racks.forEach(function(rack) {
                    const selected = selectedId == rack.id ? 'selected' : '';
                    html += `<option value="${rack.id}" ${selected}>${rack.code} - ${rack.description || ''}</option>`;
                });
                parentRackSelect.html(html);
            }
        });
    }

    // Handle type change
    typeSelect.on('change', function() {
        const type = $(this).val();
        const warehouseId = warehouseSelect.val();

        // Hide all parent containers
        parentZoneContainer.hide();
        parentRackContainer.hide();

        // Show appropriate parent container
        if (type === 'RACK') {
            parentZoneContainer.show();
            if (warehouseId) {
                loadZones(warehouseId);
            }
        } else if (type === 'SLOT') {
            parentZoneContainer.show();
            parentRackContainer.show();
            if (warehouseId) {
                loadZones(warehouseId);
            }
        }

        // Clear parent selects when type changes
        if (type === 'ZONE') {
            parentZoneSelect.val('');
            parentRackSelect.val('');
        } else if (type === 'RACK') {
            parentRackSelect.val('');
        }
    });

    // Handle warehouse change
    warehouseSelect.on('change', function() {
        const warehouseId = $(this).val();
        const type = typeSelect.val();

        if (type === 'RACK' || type === 'SLOT') {
            loadZones(warehouseId);
        }
    });

    // Handle zone change (for slots)
    parentZoneSelect.on('change', function() {
        const zoneId = $(this).val();
        const type = typeSelect.val();

        // Update hidden parent_id field
        if (type === 'RACK') {
            $('#parent_id').val(zoneId || '');
        }

        if (type === 'SLOT' && zoneId) {
            loadRacks(zoneId);
        } else {
            parentRackSelect.html('<option value="">Pilih Rack</option>');
            if (type === 'SLOT') {
                $('#parent_id').val('');
            }
        }
    });

    // Handle rack change (for slots)
    parentRackSelect.on('change', function() {
        const rackId = $(this).val();
        $('#parent_id').val(rackId || '');
    });

    // Initialize on page load
    $(document).ready(function() {
        const type = typeSelect.val();
        const warehouseId = warehouseSelect.val();
        
        @if($isEdit && $location->parent)
            const currentParentId = {{ $location->parent_id }};
            @if($location->parent->type->value === 'ZONE')
                const currentZoneId = {{ $location->parent->id }};
            @else
                const currentZoneId = {{ $location->parent->parent_id }};
                const currentRackId = {{ $location->parent->id }};
            @endif
        @else
            const currentParentId = null;
            const currentZoneId = null;
            const currentRackId = null;
        @endif

        if (type === 'RACK') {
            parentZoneContainer.show();
            if (warehouseId) {
                loadZones(warehouseId, currentZoneId || currentParentId);
                // Set parent_id if we have a current parent
                if (currentZoneId || currentParentId) {
                    $('#parent_id').val(currentZoneId || currentParentId);
                }
            }
        } else if (type === 'SLOT') {
            parentZoneContainer.show();
            parentRackContainer.show();
            if (warehouseId) {
                if (currentZoneId) {
                    loadZones(warehouseId, currentZoneId);
                    setTimeout(function() {
                        loadRacks(currentZoneId, currentRackId || currentParentId);
                        // Set parent_id to the rack
                        if (currentRackId || currentParentId) {
                            $('#parent_id').val(currentRackId || currentParentId);
                        }
                    }, 500);
                } else {
                    loadZones(warehouseId);
                }
            }
        }
    });
})();
</script>

