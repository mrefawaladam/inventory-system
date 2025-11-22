<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-supplier" data-supplier-id="{{ $supplier->id }}" title="View">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-supplier" data-supplier-id="{{ $supplier->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-supplier" data-supplier-id="{{ $supplier->id }}" data-supplier-name="{{ $supplier->name }}" title="Delete">
        <i class="ti ti-trash"></i>
    </button>
</div>

