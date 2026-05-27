@extends('layouts.admin')

@section('title', 'Brands')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Brands</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#brandModal">
        <i class="bi bi-plus-circle"></i> Add Brand
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Logo</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $brand)
            <tr>
                <td>{{ $brand->id }}</td>
                <td>
                    @if($brand->logo)
                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" style="width: 50px; height: 50px; object-fit: cover;">
                    @else
                        <span class="text-muted">No Logo</span>
                    @endif
                </td>
                <td>{{ $brand->name }}</td>
                <td>{{ $brand->slug }}</td>
                <td>
                    @if($brand->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick="editBrand({{ $brand->id }}, '{{ $brand->name }}', '{{ $brand->slug }}', '{{ $brand->description }}', {{ $brand->is_active ? 'true' : 'false' }})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No brands found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $brands->links() }}

<!-- Brand Modal -->
<div class="modal fade" id="brandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="brandForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="brandModalTitle">Add Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="brand_id" name="brand_id">
                    <div class="mb-3">
                        <label for="brand_name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="brand_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="brand_slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="brand_slug" name="slug">
                    </div>
                    <div class="mb-3">
                        <label for="brand_description" class="form-label">Description</label>
                        <textarea class="form-control" id="brand_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="brand_logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="brand_logo" name="logo" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="brand_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="brand_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const brandModal = new bootstrap.Modal(document.getElementById('brandModal'));
    const brandForm = document.getElementById('brandForm');

    function editBrand(id, name, slug, description, isActive) {
        document.getElementById('brandModalTitle').textContent = 'Edit Brand';
        document.getElementById('brand_id').value = id;
        document.getElementById('brand_name').value = name;
        document.getElementById('brand_slug').value = slug;
        document.getElementById('brand_description').value = description || '';
        document.getElementById('brand_is_active').checked = isActive;
        
        brandForm.action = `/admin/brands/${id}`;
        brandForm.innerHTML += '<input type="hidden" name="_method" value="PUT">';
        
        brandModal.show();
    }

    document.getElementById('brand_name').addEventListener('input', function() {
        const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        document.getElementById('brand_slug').value = slug;
    });

    brandForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: this.querySelector('input[name="_method"]')?.value || 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || formData.get('_token')
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Error saving brand');
            }
        });
    });

    document.getElementById('brandModal').addEventListener('hidden.bs.modal', function() {
        brandForm.reset();
        brandForm.action = '/admin/brands';
        const methodInput = brandForm.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
        document.getElementById('brandModalTitle').textContent = 'Add Brand';
    });
</script>
@endpush

