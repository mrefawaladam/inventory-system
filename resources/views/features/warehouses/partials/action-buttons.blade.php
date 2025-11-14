<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-warehouse" data-warehouse-id="{{ $warehouse->id }}" title="View">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-warehouse" data-warehouse-id="{{ $warehouse->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-warehouse" data-warehouse-id="{{ $warehouse->id }}" data-warehouse-name="{{ $warehouse->name }}" title="Delete">
        <i class="ti ti-trash"></i>
    </button>
</div>

