@push('scripts')
<script>
(function() {
    const categorySelect = document.getElementById('category_id');
    const container = document.getElementById('category-attributes-container');
    const attributesUrlTemplate = @json($attributesUrl ?? '/admin/products/category/__ID__/attributes');

    if (!categorySelect || !container) return;

    function renderAttributes(payload) {
        const attributes = payload.attributes || payload;
        const message = payload.message;

        if (!attributes || attributes.length === 0) {
            container.innerHTML = `<div class="alert alert-warning">
                <strong>No attributes for this category.</strong><br>
                ${message || 'Pick a subcategory (e.g. <em>Shoes → Women Shoes</em>), not the parent. Or attach attributes: Admin → Categories → open subcategory → Attributes.'}
            </div>`;
            return;
        }

        let html = '<div class="alert alert-info py-2 small">Check the sizes/colors you sell, then enable <strong>Generate Variants Automatically</strong> below.</div>';
        attributes.forEach(attr => {
            const required = attr.pivot && attr.pivot.is_required;
            html += `<div class="mb-4">
                <label class="form-label"><strong>${attr.name}</strong> ${required ? '<span class="text-danger">*</span>' : ''}</label>`;

            if (attr.type === 'select' && attr.values && attr.values.length > 0) {
                html += '<div class="row">';
                attr.values.forEach(value => {
                    const inputId = `attribute_${attr.id}_${value.id}`;
                    const swatch = value.color_code
                        ? `<span class="d-inline-block rounded-circle border" style="width:14px;height:14px;background:${value.color_code};vertical-align:middle;"></span> `
                        : '';
                    html += `<div class="col-md-3 col-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="${inputId}"
                                name="attribute_${attr.id}[]" value="${value.id}">
                            <label class="form-check-label" for="${inputId}">${swatch}${value.display_value || value.value}</label>
                        </div>
                    </div>`;
                });
                html += '</div>';
            } else {
                html += `<input type="text" class="form-control" name="attribute_${attr.id}" placeholder="Enter ${attr.name}">`;
            }
            html += '</div>';
        });
        container.innerHTML = html;
    }

    function loadAttributes(categoryId) {
        if (!categoryId) {
            container.innerHTML = '<p class="text-muted">Select a <strong>subcategory</strong> first (e.g. Women Shoes, Jeans).</p>';
            return;
        }
        container.innerHTML = '<p class="text-muted">Loading attributes...</p>';
        const url = attributesUrlTemplate.replace('__ID__', categoryId);
        fetch(url)
            .then(r => r.json())
            .then(renderAttributes)
            .catch(() => { container.innerHTML = '<p class="text-danger">Could not load attributes.</p>'; });
    }

    categorySelect.addEventListener('change', () => loadAttributes(categorySelect.value));
    if (categorySelect.value) loadAttributes(categorySelect.value);

    const genCheckbox = document.getElementById('generate_variants');
    const stockSection = document.getElementById('variant-stock-section');
    if (genCheckbox && stockSection) {
        genCheckbox.addEventListener('change', function() {
            stockSection.style.display = this.checked ? 'block' : 'none';
        });
        if (genCheckbox.checked) stockSection.style.display = 'block';
    }
})();
</script>
@endpush
