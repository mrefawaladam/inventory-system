<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-stock" data-stock-id="{{ $stock->id }}" title="View">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-stock" data-stock-id="{{ $stock->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-stock" data-stock-id="{{ $stock->id }}" title="Delete">
        <i class="ti ti-trash"></i>
    </button>
</div>

