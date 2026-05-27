@extends('layouts.admin')

@section('title', 'Edit Attribute')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Attribute</h1>
    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">Back</a>
</div>

<form action="{{ route('admin.attributes.update', $attribute) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <label for="name" class="form-label">Name *</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $attribute->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $attribute->slug) }}">
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">Type *</label>
                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                    <option value="select" {{ old('type', $attribute->type) == 'select' ? 'selected' : '' }}>Select</option>
                    <option value="text" {{ old('type', $attribute->type) == 'text' ? 'selected' : '' }}>Text</option>
                    <option value="number" {{ old('type', $attribute->type) == 'number' ? 'selected' : '' }}>Number</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div id="values-section" style="display: {{ $attribute->type == 'select' ? 'block' : 'none' }};">
                <h5>Attribute Values</h5>
                <div id="values-container">
                    @foreach($attribute->values as $index => $value)
                    <div class="value-row mb-2">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="hidden" name="values[{{ $index }}][id]" value="{{ $value->id }}">
                                <input type="text" class="form-control" name="values[{{ $index }}][value]" value="{{ $value->value }}" placeholder="Value" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="values[{{ $index }}][display_value]" value="{{ $value->display_value }}" placeholder="Display Value">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="values[{{ $index }}][color_code]" value="{{ $value->color_code }}" placeholder="Color Code (#hex)" maxlength="7">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-value">×</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-secondary" id="add-value">Add Value</button>
            </div>
        </div>

        <div class="col-md-4">
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required', $attribute->is_required) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_required">Required</label>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_filterable" name="is_filterable" value="1" {{ old('is_filterable', $attribute->is_filterable) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_filterable">Filterable</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $attribute->sort_order) }}">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Attribute</button>
</form>
@endsection

@push('scripts')
<script>
    let valueIndex = {{ $attribute->values->count() }};

    document.getElementById('type').addEventListener('change', function() {
        document.getElementById('values-section').style.display = this.value === 'select' ? 'block' : 'none';
    });

    document.getElementById('add-value').addEventListener('click', function() {
        const container = document.getElementById('values-container');
        const row = document.createElement('div');
        row.className = 'value-row mb-2';
        row.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="values[${valueIndex}][value]" placeholder="Value" required>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="values[${valueIndex}][display_value]" placeholder="Display Value">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="values[${valueIndex}][color_code]" placeholder="Color Code (#hex)" maxlength="7">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-value">×</button>
                </div>
            </div>
        `;
        container.appendChild(row);
        valueIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-value')) {
            e.target.closest('.value-row').remove();
        }
    });
</script>
@endpush

