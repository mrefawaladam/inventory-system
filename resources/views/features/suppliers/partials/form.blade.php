@php
    $isEdit = isset($supplier) && $supplier !== null;
@endphp

<form id="supplier-form" action="{{ $isEdit ? route('suppliers.update', $supplier) : route('suppliers.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Nama Supplier <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            value="{{ $isEdit ? $supplier->name : old('name') }}"
            required
            placeholder="Contoh: PT. Supplier ABC"
        >
        <div class="invalid-feedback d-none" id="name-error"></div>
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Telepon</label>
        <input
            type="text"
            class="form-control"
            id="phone"
            name="phone"
            value="{{ $isEdit ? $supplier->phone : old('phone') }}"
            placeholder="Contoh: 081234567890"
        >
        <div class="invalid-feedback d-none" id="phone-error"></div>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Alamat</label>
        <textarea
            class="form-control"
            id="address"
            name="address"
            rows="3"
            placeholder="Alamat lengkap supplier"
        >{{ $isEdit ? $supplier->address : old('address') }}</textarea>
        <div class="invalid-feedback d-none" id="address-error"></div>
    </div>
</form>

