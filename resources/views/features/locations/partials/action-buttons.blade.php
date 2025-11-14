<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-location" data-location-id="{{ $location->id }}" title="View">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-location" data-location-id="{{ $location->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-location" data-location-id="{{ $location->id }}" data-location-code="{{ $location->code }}" title="Delete">
        <i class="ti ti-trash"></i>
    </button>
</div>

