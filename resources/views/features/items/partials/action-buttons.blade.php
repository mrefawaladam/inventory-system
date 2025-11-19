<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-item" data-item-id="{{ $item->id }}" title="View">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-item" data-item-id="{{ $item->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-item" data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}" title="Delete">
        <i class="ti ti-trash"></i>
    </button>
</div>

