<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-customer" data-customer-id="{{ $customer->id }}" title="View">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-customer" data-customer-id="{{ $customer->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-customer" data-customer-id="{{ $customer->id }}" data-customer-name="{{ $customer->name }}" title="Delete">
        <i class="ti ti-trash"></i>
    </button>
</div>

