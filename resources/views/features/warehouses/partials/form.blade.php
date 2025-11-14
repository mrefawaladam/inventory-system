@php
    $isEdit = isset($warehouse) && $warehouse !== null;
    $defaultLat = $isEdit && $warehouse->latitude ? $warehouse->latitude : -6.2088;
    $defaultLng = $isEdit && $warehouse->longitude ? $warehouse->longitude : 106.8456;
@endphp

<form id="warehouse-form" action="{{ $formAction }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            value="{{ $isEdit ? $warehouse->name : old('name') }}"
            required
        >
        <div class="invalid-feedback d-none" id="name-error"></div>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Alamat</label>
        <textarea
            class="form-control"
            id="address"
            name="address"
            rows="2"
            placeholder="Alamat akan terisi otomatis saat memilih lokasi di peta"
        >{{ $isEdit ? $warehouse->address : old('address') }}</textarea>
        <small class="text-muted">Alamat akan terisi otomatis berdasarkan koordinat yang dipilih</small>
        <div class="invalid-feedback d-none" id="address-error"></div>
    </div>

    <div class="mb-3">
        <label class="form-label">Lokasi di Peta <span class="text-danger">*</span></label>
        <p class="text-muted small mb-2">Cari lokasi atau klik pada peta untuk mengatur lokasi gudang</p>

        <!-- Search Box -->
        <div class="position-relative mb-2">
            <div class="input-group">
                <input
                    type="text"
                    class="form-control"
                    id="location-search"
                    placeholder="Cari lokasi (contoh: Jakarta, Bandung, Surabaya)..."
                >
                <button class="btn btn-outline-primary" type="button" id="btn-search-location">
                    <i class="ti ti-search"></i> Cari
                </button>
            </div>
            <div id="search-results" class="list-group position-absolute w-100 mt-1" style="max-height: 200px; overflow-y: auto; display: none; z-index: 1050; box-shadow: 0 2px 8px rgba(0,0,0,0.15); border-radius: 4px;"></div>
        </div>

        <div id="location-map" style="height: 300px; width: 100%; border-radius: 8px; border: 1px solid #ddd; background-color: #f5f5f5; margin-top: 10px;"></div>
        <div class="mt-2">
            <div class="row">
                <div class="col-md-6">
                    <label for="latitude" class="form-label small">Latitude</label>
                    <input
                        type="number"
                        step="any"
                        class="form-control form-control-sm"
                        id="latitude"
                        name="latitude"
                        value="{{ $isEdit ? $warehouse->latitude : old('latitude', $defaultLat) }}"
                        readonly
                    >
                </div>
                <div class="col-md-6">
                    <label for="longitude" class="form-label small">Longitude</label>
                    <input
                        type="number"
                        step="any"
                        class="form-control form-control-sm"
                        id="longitude"
                        name="longitude"
                        value="{{ $isEdit ? $warehouse->longitude : old('longitude', $defaultLng) }}"
                        readonly
                    >
                </div>
            </div>
        </div>
        <div class="invalid-feedback d-none" id="latitude-error"></div>
        <div class="invalid-feedback d-none" id="longitude-error"></div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea
            class="form-control"
            id="description"
            name="description"
            rows="3"
        >{{ $isEdit ? $warehouse->description : old('description') }}</textarea>
        <div class="invalid-feedback d-none" id="description-error"></div>
    </div>
</form>

<div id="location-map-data" data-lat="{{ $defaultLat }}" data-lng="{{ $defaultLng }}" style="display: none;"></div>

