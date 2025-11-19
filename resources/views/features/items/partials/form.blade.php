@php
    $isEdit = isset($item) && $item !== null;
@endphp

<form id="item-form" action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="sku" class="form-label">SKU</label>
            <input
                type="text"
                class="form-control"
                id="sku"
                name="sku"
                value="{{ $isEdit ? $item->sku : old('sku') }}"
                placeholder="Kosongkan untuk auto-generate"
                {{ $isEdit ? 'readonly' : '' }}
            >
            <small class="text-muted">Biarkan kosong untuk generate otomatis (contoh: PRD-001)</small>
            <div class="invalid-feedback d-none" id="sku-error"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="barcode" class="form-label">Barcode</label>
            <input
                type="text"
                class="form-control"
                id="barcode"
                name="barcode"
                value="{{ $isEdit ? $item->barcode : old('barcode') }}"
                placeholder="Kosongkan untuk auto-generate"
                {{ $isEdit ? 'readonly' : '' }}
            >
            <small class="text-muted">Biarkan kosong untuk generate otomatis (EAN-13)</small>
            <div class="invalid-feedback d-none" id="barcode-error"></div>
        </div>
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            value="{{ $isEdit ? $item->name : old('name') }}"
            required
        >
        <div class="invalid-feedback d-none" id="name-error"></div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="unit" class="form-label">Unit</label>
            <input
                type="text"
                class="form-control"
                id="unit"
                name="unit"
                value="{{ $isEdit ? $item->unit : old('unit') }}"
                placeholder="Contoh: Unit, Box, Pack, Set"
            >
            <div class="invalid-feedback d-none" id="unit-error"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="minimum_stock" class="form-label">Minimum Stok</label>
            <input
                type="number"
                class="form-control"
                id="minimum_stock"
                name="minimum_stock"
                value="{{ $isEdit ? $item->minimum_stock : (old('minimum_stock') ?? 0) }}"
                min="0"
            >
            <div class="invalid-feedback d-none" id="minimum_stock-error"></div>
        </div>
    </div>

    <div class="mb-3">
        <label for="image" class="form-label">Gambar Produk</label>
        <input
            type="file"
            class="form-control"
            id="image"
            name="image"
            accept="image/jpeg,image/png,image/jpg,image/gif"
        >
        <small class="text-muted">Format: JPEG, PNG, JPG, GIF (Max: 2MB)</small>
        @if($isEdit && $item->image)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" style="max-width: 200px; max-height: 200px; border-radius: 4px;">
                <p class="text-muted small mt-1">Gambar saat ini</p>
            </div>
        @endif
        <div class="invalid-feedback d-none" id="image-error"></div>
    </div>
</form>

