@extends('layouts.admin')

@section('title', 'Attributes')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Attributes</h1>
    <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Attribute
    </a>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Values</th>
                <th>Required</th>
                <th>Filterable</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attributes as $attribute)
            <tr>
                <td>{{ $attribute->id }}</td>
                <td>{{ $attribute->name }}</td>
                <td><span class="badge bg-info">{{ $attribute->type }}</span></td>
                <td>
                    @foreach($attribute->values->take(5) as $value)
                        <span class="badge bg-secondary">{{ $value->value }}</span>
                    @endforeach
                    @if($attribute->values->count() > 5)
                        <span class="badge bg-light text-dark">+{{ $attribute->values->count() - 5 }} more</span>
                    @endif
                </td>
                <td>
                    @if($attribute->is_required)
                        <span class="badge bg-danger">Yes</span>
                    @else
                        <span class="badge bg-secondary">No</span>
                    @endif
                </td>
                <td>
                    @if($attribute->is_filterable)
                        <span class="badge bg-success">Yes</span>
                    @else
                        <span class="badge bg-secondary">No</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                <td colspan="7" class="text-center">No attributes found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $attributes->links() }}
@endsection

