<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-tag text-info me-2"></i> Product Type Management
            </h3>
            <p class="text-muted mb-0">Manage and organize your product types/subcategories efficiently</p>
        </div>
        <div>
            <button class="btn btn-primary" wire:click="createType">
                <i class="bi bi-plus-lg me-2"></i> Add Type
            </button>
        </div>
    </div>

    <!-- Type List Table -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-list-ul text-primary me-2"></i> Type List
                        </h5>
                        <p class="text-muted small mb-0">View and manage all product types</p>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Type Name</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($types->count() > 0)
                                @foreach ($types as $type)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-medium text-dark">{{ $loop->iteration }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $type->type_name }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                <i class="bi bi-gear-fill"></i> Actions
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <!-- Edit Type -->
                                                <li>
                                                    <button class="dropdown-item"
                                                            wire:click="editType({{ $type->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="editType({{ $type->id }})">
                                                        
                                                        <span wire:loading wire:target="editType({{ $type->id }})">
                                                            <i class="spinner-border spinner-border-sm me-2"></i>
                                                            Loading...
                                                        </span>
                                                        <span wire:loading.remove wire:target="editType({{ $type->id }})">
                                                            <i class="bi bi-pencil-square text-primary me-2"></i>
                                                            Edit
                                                        </span>
                                                    </button>
                                                </li>

                                                <!-- Delete Type -->
                                                <li>
                                                    <button class="dropdown-item"
                                                            wire:click="confirmDelete({{ $type->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="confirmDelete({{ $type->id }})">
                                                        
                                                        <span wire:loading wire:target="confirmDelete({{ $type->id }})">
                                                            <i class="spinner-border spinner-border-sm me-2"></i>
                                                            Loading...
                                                        </span>
                                                        <span wire:loading.remove wire:target="confirmDelete({{ $type->id }})">
                                                            <i class="bi bi-trash text-danger me-2"></i>
                                                            Delete
                                                        </span>
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="alert alert-primary bg-opacity-10">
                                            <i class="bi bi-info-circle me-2"></i> No product types found.
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Type Modal -->
    <div wire:ignore.self class="modal fade" id="createTypeModal" tabindex="-1" aria-labelledby="createTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle text-white me-2"></i> Add Type
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveType">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Type Name</label>
                            <input type="text" class="form-control" wire:model="typeName" placeholder="Enter type name" required>
                            @error('typeName')
                            <span class="text-danger small">* {{ $message }}</span>
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Save Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Type Modal -->
    <div wire:ignore.self class="modal fade" id="editTypeModal" tabindex="-1" aria-labelledby="editTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-white me-2"></i> Edit Type
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="updateType">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Type Name</label>
                            <input type="text" class="form-control" wire:model="editTypeName" placeholder="Enter type name" required>
                            @error('editTypeName')
                            <span class="text-danger small">* {{ $message }}</span>
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Update Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .summary-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .summary-card.total {
        border-left-color: #2a83df;
    }

    .icon-container {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }

    .btn-link {
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-link:hover {
        transform: scale(1.1);
    }

    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .form-control {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
    }

    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        border-color: #4361ee;
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #4361ee;
        border-color: #4361ee;
    }

    .btn-primary:hover {
        background-color: #3f37c9;
        border-color: #3f37c9;
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script>
    window.addEventListener('confirm-delete', event => {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('confirmDelete');
            }
        });
    });

    window.addEventListener('edit-type', event => {
        const modal = new bootstrap.Modal(document.getElementById('editTypeModal'));
        modal.show();
    });

    window.addEventListener('create-type-modal', event => {
        const modal = new bootstrap.Modal(document.getElementById('createTypeModal'));
        modal.show();
    });

    // Close modals on successful operations
    Livewire.on('types-updated', () => {
        const createModal = bootstrap.Modal.getInstance(document.getElementById('createTypeModal'));
        const editModal = bootstrap.Modal.getInstance(document.getElementById('editTypeModal'));
        if (createModal) createModal.hide();
        if (editModal) editModal.hide();
    });
</script>
@endpush
