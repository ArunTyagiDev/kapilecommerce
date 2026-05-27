@extends('layouts.admin')

@section('title', 'Category Attributes')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Attributes for: {{ $category->name }}</h1>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="row">
    <div class="col-md-6">
        <h4>Attached Attributes</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Attribute</th>
                    <th>Required</th>
                    <th>Values</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($category->attributes as $attribute)
                <tr>
                    <td>{{ $attribute->name }}</td>
                    <td>
                        @if($attribute->pivot->is_required)
                            <span class="badge bg-danger">Required</span>
                        @else
                            <span class="badge bg-secondary">Optional</span>
                        @endif
                    </td>
                    <td>
                        @foreach($attribute->values as $value)
                            <span class="badge bg-info">{{ $value->value }}</span>
                        @endforeach
                    </td>
                    <td>
                        <form action="{{ route('admin.categories.attributes.detach', [$category, $attribute->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="bi bi-x-circle"></i> Remove
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No attributes attached</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="col-md-6">
        <h4>Attach New Attribute</h4>
        <form action="{{ route('admin.categories.attributes.attach', $category) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="attribute_id" class="form-label">Attribute</label>
                <select class="form-select" id="attribute_id" name="attribute_id" required>
                    <option value="">Select Attribute</option>
                    @foreach($allAttributes as $attr)
                        @if(!$category->attributes->contains($attr->id))
                            <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1">
                    <label class="form-check-label" for="is_required">Required</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
            </div>

            <button type="submit" class="btn btn-primary">Attach Attribute</button>
        </form>
    </div>
</div>
@endsection

