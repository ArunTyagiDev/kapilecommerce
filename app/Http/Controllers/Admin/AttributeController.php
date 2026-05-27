<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->orderBy('sort_order')->paginate(20);
        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes',
            'type' => 'required|in:select,text,number',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $attribute = Attribute::create($validated);

        // Handle attribute values if type is select
        if ($request->has('values') && is_array($request->values)) {
            foreach ($request->values as $index => $valueData) {
                if (!empty($valueData['value'])) {
                    AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $valueData['value'],
                        'display_value' => $valueData['display_value'] ?? $valueData['value'],
                        'color_code' => $valueData['color_code'] ?? null,
                        'sort_order' => $valueData['sort_order'] ?? $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');
        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes,slug,' . $attribute->id,
            'type' => 'required|in:select,text,number',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $attribute->update($validated);

        // Update attribute values
        if ($request->has('values') && is_array($request->values)) {
            $existingIds = [];
            
            foreach ($request->values as $valueData) {
                if (isset($valueData['id'])) {
                    // Update existing
                    AttributeValue::where('id', $valueData['id'])
                        ->where('attribute_id', $attribute->id)
                        ->update([
                            'value' => $valueData['value'],
                            'display_value' => $valueData['display_value'] ?? $valueData['value'],
                            'color_code' => $valueData['color_code'] ?? null,
                            'sort_order' => $valueData['sort_order'] ?? 0,
                        ]);
                    $existingIds[] = $valueData['id'];
                } else if (!empty($valueData['value'])) {
                    // Create new
                    $newValue = AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $valueData['value'],
                        'display_value' => $valueData['display_value'] ?? $valueData['value'],
                        'color_code' => $valueData['color_code'] ?? null,
                        'sort_order' => $valueData['sort_order'] ?? 0,
                    ]);
                    $existingIds[] = $newValue->id;
                }
            }

            // Delete removed values
            AttributeValue::where('attribute_id', $attribute->id)
                ->whereNotIn('id', $existingIds)
                ->delete();
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    public function addValue(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'display_value' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        AttributeValue::create([
            'attribute_id' => $attribute->id,
            'value' => $validated['value'],
            'display_value' => $validated['display_value'] ?? $validated['value'],
            'color_code' => $validated['color_code'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Attribute value added successfully.');
    }
}

