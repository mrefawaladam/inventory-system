@php
    $isEdit = isset($stock) && $stock !== null;
@endphp

<form id="stock-form" action="{{ $formAction }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="item_id" class="form-label">Barang <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="item_id"
            name="item_id"
            required
            {{ $isEdit ? 'disabled' : '' }}
        >
            <option value="">Pilih Barang</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" {{ ($isEdit && $stock->item_id == $item->id) ? 'selected' : '' }}>
                    {{ $item->sku }} - {{ $item->name }}
                </option>
            @endforeach
        </select>
        @if($isEdit)
            <input type="hidden" name="item_id" value="{{ $stock->item_id }}">
        @endif
        <div class="invalid-feedback d-none" id="item_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="location_id" class="form-label">Lokasi <span class="text-danger">*</span></label>
        <select
            class="form-select"
            id="location_id"
            name="location_id"
            required
            {{ $isEdit ? 'disabled' : '' }}
        >
            <option value="">Pilih Lokasi</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ ($isEdit && $stock->location_id == $location->id) ? 'selected' : '' }}>
                    {{ $location->code }} - {{ $location->warehouse->name ?? '' }} ({{ $location->type->label() }})
                </option>
            @endforeach
        </select>
        @if($isEdit)
            <input type="hidden" name="location_id" value="{{ $stock->location_id }}">
        @endif
        <div class="invalid-feedback d-none" id="location_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
        <input
            type="number"
            class="form-control"
            id="quantity"
            name="quantity"
            value="{{ $isEdit ? $stock->quantity : (old('quantity') ?? 1) }}"
            min="{{ $isEdit ? 0 : 1 }}"
            required
        >
        <div class="invalid-feedback d-none" id="quantity-error"></div>
    </div>

    <div class="mb-3">
        <label for="batch" class="form-label">Batch</label>
        <input
            type="text"
            class="form-control"
            id="batch"
            name="batch"
            value="{{ $isEdit ? $stock->batch : old('batch') }}"
            placeholder="Contoh: BATCH-20241114-ABC"
        >
        <small class="text-muted">Kosongkan jika tidak menggunakan batch</small>
        <div class="invalid-feedback d-none" id="batch-error"></div>
    </div>

    <div class="mb-3">
        <label for="expired_at" class="form-label">Tanggal Pengiriman</label>
        <input
            type="date"
            class="form-control"
            id="expired_at"
            name="expired_at"
            value="{{ $isEdit && $stock->expired_at ? $stock->expired_at->format('Y-m-d') : old('expired_at') }}"
        >
        <small class="text-muted">Isi jika ada jadwal pengiriman</small>
        <div class="invalid-feedback d-none" id="expired_at-error"></div>
    </div>
</form>

