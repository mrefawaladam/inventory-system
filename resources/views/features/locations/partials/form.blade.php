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
        <label for="warehouse_id" class="form-label">Sekolah <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="warehouse_id"
            name="warehouse_id"
            required
        >
            <option value="">Pilih Sekolah</option>
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
            <option value="ZONE" {{ $selectedType === 'ZONE' ? 'selected' : '' }}>Area</option>
            <option value="RACK" {{ $selectedType === 'RACK' ? 'selected' : '' }}>Rak</option>
            <option value="SLOT" {{ $selectedType === 'SLOT' ? 'selected' : '' }}>Tempat</option>
        </select>
        <small class="text-muted">Area: Bagian utama gudang | Rak: Rak penyimpanan dalam area | Tempat: Tempat penyimpanan dalam rak</small>
        <div class="invalid-feedback d-none" id="type-error"></div>
    </div>

    <div class="mb-3" id="parent-zone-container" style="display: none;">
        <label for="parent_zone_id" class="form-label">Area <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="parent_zone_id"
        >
            <option value="">Pilih Area</option>
        </select>
        <input type="hidden" id="parent_id" name="parent_id" value="{{ $selectedParentId }}">
        <small class="text-muted">Pilih Area sebagai parent untuk Rak ini</small>
        <div class="invalid-feedback d-none" id="parent_id-error"></div>
    </div>

    <div class="mb-3" id="parent-rack-container" style="display: none;">
        <label for="parent_rack_id" class="form-label">Rak <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="parent_rack_id"
        >
            <option value="">Pilih Rak</option>
        </select>
        <small class="text-muted">Pilih Rak sebagai parent untuk Tempat ini</small>
        <div class="invalid-feedback d-none" id="parent_rack_id-error"></div>
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
        <label for="capacity" class="form-label">Jumlah Siswa yang Menerima <span class="text-danger">*</span></label>
        <input
            type="number"
            class="form-control"
            id="capacity"
            name="capacity"
            value="{{ $isEdit ? $location->capacity : (old('capacity') ?? 0) }}"
            min="0"
            required
        >
        <small class="text-muted">Isikan jumlah siswa penerima untuk lokasi ini</small>
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

