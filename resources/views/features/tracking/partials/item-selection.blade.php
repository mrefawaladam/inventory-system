<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Pilih Item</h5>
        <form method="GET" action="{{ route('tracking.item-history') }}" id="item-select-form">
            <div class="row g-3">
                <div class="col-md-10">
                    <select name="itemId" id="select-item" class="form-select" required>
                        <option value="">-- Pilih Item --</option>
                        @foreach($items as $itm)
                            <option value="{{ $itm->id }}" {{ $itemId == $itm->id ? 'selected' : '' }}>
                                {{ $itm->name }} @if($itm->sku)({{ $itm->sku }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <iconify-icon icon="solar:magnifer-line-duotone" class="me-1"></iconify-icon>
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

