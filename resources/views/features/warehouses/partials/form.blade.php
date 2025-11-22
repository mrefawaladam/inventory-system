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
        <label for="name" class="form-label">Nama Sekolah Rakyat <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            value="{{ $isEdit ? $warehouse->name : old('name') }}"
            placeholder="Contoh: Sekolah Rakyat Menengah Pertama 1 Deli Serdang"
            required
        >
        <small class="text-muted">Nama lengkap sekolah sesuai data CSV</small>
        <div class="invalid-feedback d-none" id="name-error"></div>
    </div>

    <div class="mb-3">
        <label for="recipient" class="form-label">Penerima / Instansi</label>
        <input
            type="text"
            class="form-control"
            id="recipient"
            name="recipient"
            value="{{ $isEdit ? $warehouse->recipient : old('recipient') }}"
            placeholder="Contoh: Sentra Insyaf Medan"
        >
        <small class="text-muted">Nama instansi atau penerima sarana sekolah</small>
        <div class="invalid-feedback d-none" id="recipient-error"></div>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Alamat Jalan <span class="text-danger">*</span></label>
        <textarea
            class="form-control"
            id="address"
            name="address"
            rows="3"
            placeholder="Alamat lengkap sekolah (contoh: Jl. Bedikari No. 37, Suka Rende, Kec. Kutalimbaru)"
            required
        >{{ $isEdit ? $warehouse->address : old('address') }}</textarea>
        <small class="text-muted">Alamat lengkap sekolah penerima sarana</small>
        <div class="invalid-feedback d-none" id="address-error"></div>
    </div>

    <div class="mb-3">
        <label for="province_select" class="form-label">Provinsi <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="province_select"
            required
        >
            <option value="">-- Pilih Provinsi --</option>
            @php
                $currentProvince = $isEdit ? $warehouse->province : old('province');
                $provincesCount = isset($provinces) && is_array($provinces) ? count($provinces) : 0;
                // Debug: Log provinces data
                if (isset($provinces) && is_array($provinces)) {
                    \Log::info("Form rendering - Provinces count: " . count($provinces));
                    if (count($provinces) > 0) {
                        \Log::info("First 3 provinces in form: " . json_encode(array_slice($provinces, 0, 3)));
                    }
                }
            @endphp
            @if(isset($provinces) && is_array($provinces) && $provincesCount > 0)
                @foreach($provinces as $index => $province)
                    @if($province && is_array($province) && isset($province['id']) && isset($province['name']) && !empty($province['name']))
                        @php
                            $provinceName = trim(preg_replace('/\s+/', ' ', $province['name'])); // Remove newlines and extra spaces
                            $provinceId = trim($province['id']);
                            $isSelected = $currentProvince == $provinceName || $currentProvince == $province['name'];
                        @endphp
                        <option 
                            value="{{ $provinceId }}" 
                            data-name="{{ $provinceName }}"
                            {{ $isSelected ? 'selected' : '' }}
                        >
                            {{ $provinceName }}
                        </option>
                    @endif
                @endforeach
            @else
                <option value="" disabled>Memuat provinsi... @if($provincesCount > 0)({{ $provincesCount }} ditemukan)@endif</option>
            @endif
        </select>
        <input type="hidden" id="province" name="province" value="{{ $currentProvince }}">
        <small class="text-muted">Pilih provinsi lokasi sekolah @if($provincesCount > 0)({{ $provincesCount }} provinsi tersedia)@endif</small>
        <div class="invalid-feedback d-none" id="province-error"></div>
    </div>

    <div class="mb-3">
        <label for="city_select" class="form-label">Kota/Kabupaten <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="city_select"
            required
            disabled
        >
            <option value="">-- Pilih Provinsi terlebih dahulu --</option>
            @if($isEdit && $warehouse->city)
                <option value="{{ $warehouse->city }}" selected>{{ $warehouse->city }}</option>
            @endif
        </select>
        <input type="hidden" id="city" name="city" value="{{ $isEdit ? $warehouse->city : old('city') }}">
        <small class="text-muted">Pilih kota atau kabupaten lokasi sekolah</small>
        <div class="invalid-feedback d-none" id="city-error"></div>
    </div>

    <div class="mb-3">
        <label for="district_select" class="form-label">Kecamatan</label>
        <select
            class="form-select"
            id="district_select"
            disabled
        >
            <option value="">-- Pilih Kota/Kabupaten terlebih dahulu --</option>
            @if($isEdit && $warehouse->district)
                <option value="{{ $warehouse->district }}" selected>{{ $warehouse->district }}</option>
            @endif
        </select>
        <input type="hidden" id="district" name="district" value="{{ $isEdit ? $warehouse->district : old('district') }}">
        <small class="text-muted">Pilih kecamatan lokasi sekolah (opsional)</small>
        <div class="invalid-feedback d-none" id="district-error"></div>
    </div>

    <div class="mb-3">
        <label for="village_select" class="form-label">Desa/Kelurahan</label>
        <select
            class="form-select"
            id="village_select"
            disabled
        >
            <option value="">-- Pilih Kecamatan terlebih dahulu --</option>
            @if($isEdit && $warehouse->village)
                <option value="{{ $warehouse->village }}" selected>{{ $warehouse->village }}</option>
            @endif
        </select>
        <input type="hidden" id="village" name="village" value="{{ $isEdit ? $warehouse->village : old('village') }}">
        <small class="text-muted">Pilih desa atau kelurahan lokasi sekolah (opsional)</small>
        <div class="invalid-feedback d-none" id="village-error"></div>
    </div>

    <div class="mb-3">
        <label class="form-label">Titik Lokasi Sekolah di Peta <span class="text-danger">*</span></label>
        <p class="text-muted small mb-2">Cari alamat jalan atau klik pada peta untuk mengatur lokasi sekolah</p>

        <!-- Search Box -->
        <div class="position-relative mb-2">
            <div class="input-group">
                <input
                    type="text"
                    class="form-control"
                    id="location-search"
                    placeholder="Cari alamat sekolah (contoh: Jln Sudirman, Kel. ABC)..."
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
