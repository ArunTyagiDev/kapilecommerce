# Project Structure

## Folder Structure

```
FullEcom/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AttributeController.php
│   │   │   │   ├── BrandController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   └── ProductController.php
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   └── Frontend/
│   │   │       ├── CartController.php
│   │   │       └── ProductController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   └── Models/
│       ├── Attribute.php
│       ├── AttributeValue.php
│       ├── Brand.php
│       ├── Cart.php
│       ├── Category.php
│       ├── Product.php
│       ├── ProductImage.php
│       ├── ProductTag.php
│       ├── ProductVariant.php
│       ├── User.php
│       └── VariantAttribute.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_brands_table.php
│   │   ├── 2024_01_01_000002_create_categories_table.php
│   │   ├── 2024_01_01_000003_create_attributes_table.php
│   │   ├── 2024_01_01_000004_create_attribute_values_table.php
│   │   ├── 2024_01_01_000005_create_category_attributes_table.php
│   │   ├── 2024_01_01_000006_create_products_table.php
│   │   ├── 2024_01_01_000007_create_product_images_table.php
│   │   ├── 2024_01_01_000008_create_product_tags_table.php
│   │   ├── 2024_01_01_000009_create_product_variants_table.php
│   │   ├── 2024_01_01_000010_create_variant_attributes_table.php
│   │   ├── 2024_01_01_000011_create_carts_table.php
│   │   └── 2024_01_01_000012_create_users_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── attributes/
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── brands/
│       │   │   └── index.blade.php
│       │   ├── categories/
│       │   │   ├── attributes.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── products/
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   └── dashboard.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       ├── frontend/
│       │   ├── cart/
│       │   │   └── index.blade.php
│       │   └── products/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       └── layouts/
│           ├── admin.blade.php
│           └── frontend.blade.php
├── routes/
│   ├── api.php
│   ├── auth.php
│   └── web.php
├── composer.json
├── README.md
└── PROJECT_STRUCTURE.md
```

## Database Schema

### Core Tables

1. **categories** - Product categories with parent-child support
2. **attributes** - Product attributes (Size, Color, etc.)
3. **attribute_values** - Values for attributes
4. **category_attributes** - Pivot table linking categories to attributes
5. **products** - Main products table
6. **product_variants** - Product variants
7. **variant_attributes** - Links variants to attribute values
8. **product_images** - Product images
9. **product_tags** - Product tags
10. **brands** - Product brands
11. **carts** - Shopping cart items
12. **users** - Users (admin/customer)

## Key Features Implementation

### 1. Variant Generator Logic
Located in: `app/Http/Controllers/Admin/ProductController.php`
- Method: `generateVariants()`
- Generates all combinations of selected attribute values
- Creates variants automatically when creating products

### 2. Dynamic Attribute Loading
- Category selection triggers AJAX call to load category-specific attributes
- Frontend dynamically renders attribute selection fields
- Variant selection updates product details in real-time

### 3. Filter System
- Frontend filters by category, price range, and attributes
- Backend admin filters by category, brand, status, and search
- Uses Laravel query builder for efficient filtering

## API Response Examples

### Get Product Variant
```json
{
  "success": true,
  "variant": {
    "id": 1,
    "sku": "VAR-ABC12345",
    "price": 99.99,
    "stock_quantity": 10,
    "in_stock": true,
    "image": null
  }
}
```

### Get Category Attributes
```json
[
  {
    "id": 1,
    "name": "Size",
    "slug": "size",
    "type": "select",
    "pivot": {
      "is_required": true,
      "sort_order": 1
    },
    "values": [
      {
        "id": 1,
        "value": "S",
        "display_value": "Small",
        "color_code": null
      }
    ]
  }
]
```

## Model Relationships

- Category → hasMany Products
- Category → belongsToMany Attributes (through category_attributes)
- Product → belongsTo Category, Brand
- Product → hasMany Images, Tags, Variants
- ProductVariant → belongsTo Product
- ProductVariant → belongsToMany AttributeValues (through variant_attributes)
- Attribute → hasMany AttributeValues
- Attribute → belongsToMany Categories (through category_attributes)

