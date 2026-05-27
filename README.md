# E-Commerce Product Management System

A complete Laravel-based E-Commerce Product Management System with flexible attribute architecture, dynamic product variants, and comprehensive admin panel.

## Features

### 🔥 Core Features

1. **Flexible Category & Attribute System**
   - Multiple product categories
   - Dynamic attributes per category (Size, Color, Material, etc.)
   - Custom attribute values
   - Category-specific attribute requirements

2. **Product Management**
   - Full CRUD operations
   - Multiple images per product
   - SKU auto-generation
   - Barcode support
   - Stock management
   - Price & discount pricing
   - SEO fields (meta title, description, keywords)
   - Product tags

3. **Variant Generation**
   - Automatic variant generation based on attribute combinations
   - Size + Color combinations
   - Custom attribute combinations
   - Variant-specific pricing and stock

4. **Admin Panel**
   - Dashboard with statistics
   - Category management
   - Attribute management
   - Product management with variant generator
   - Brand management
   - Clean, modern UI with sidebar navigation

5. **Frontend**
   - Product listing with filters
   - Category filters
   - Attribute filters (Size, Color, etc.)
   - Price range filter
   - Sorting options
   - Product detail page with variant selection
   - Shopping cart

## Installation

### Prerequisites

- PHP >= 8.1
- MySQL >= 5.7
- Composer
- Node.js & NPM (optional, for frontend assets)

### Steps

1. **Clone/Download the project**
   ```bash
   cd D:\xampp8.2\htdocs\FullEcom
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Update .env file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fullecom
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Create database**
   ```sql
   CREATE DATABASE fullecom;
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed database (optional)**
   ```bash
   php artisan db:seed
   ```

8. **Create storage link**
   ```bash
   php artisan storage:link
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

## Default Login Credentials

After running the seeder:
- **Email:** admin@example.com
- **Password:** password

## Database Structure

### Main Tables

- `categories` - Product categories
- `attributes` - Product attributes (Size, Color, etc.)
- `attribute_values` - Values for attributes
- `category_attributes` - Pivot table linking categories to attributes
- `products` - Main products table
- `product_variants` - Product variants
- `variant_attributes` - Links variants to attribute values
- `product_images` - Product images
- `product_tags` - Product tags
- `brands` - Product brands
- `carts` - Shopping cart items
- `users` - Users (admin/customer)

## Usage Guide

### Setting Up Categories

1. Go to Admin Panel → Categories
2. Create a new category (e.g., "Shoes")
3. Click "Attributes" to attach attributes
4. Select attributes like "Size" and "Color"
5. Mark required attributes if needed

### Creating Attributes

1. Go to Admin Panel → Attributes
2. Create attribute (e.g., "Size")
3. Add attribute values (e.g., 6, 7, 8, 9, 10, 11)
4. Set as filterable if you want it in frontend filters

### Creating Products

1. Go to Admin Panel → Products → Add Product
2. Fill in basic information
3. Select category (this loads category-specific attributes)
4. In "Attributes & Variants" tab:
   - Select attribute values (e.g., sizes and colors)
   - Check "Generate Variants Automatically"
   - System will create all combinations
5. Upload images
6. Add tags and SEO information
7. Save product

### Example: Creating Shoes with Variants

1. Create category "Shoes"
2. Attach "Size" attribute with values: 6, 7, 8, 9, 10, 11
3. Attach "Color" attribute with values: Black, White, Red
4. Create product "Running Shoes"
5. Select sizes: 6, 7, 8
6. Select colors: Black, White
7. Enable variant generation
8. System creates 6 variants: (6/Black), (6/White), (7/Black), (7/White), (8/Black), (8/White)

## API Endpoints

### Public API

- `GET /api/products` - List products
- `GET /api/products/{slug}` - Get product details
- `POST /api/products/variant` - Get variant by attributes

### Admin API (requires authentication)

- `GET /api/admin/products` - List products
- `GET /api/admin/products/{id}` - Get product
- `GET /api/admin/categories/{id}/attributes` - Get category attributes

## Routes

### Frontend Routes
- `/` - Home/Product listing
- `/products` - Product listing with filters
- `/products/{slug}` - Product details
- `/cart` - Shopping cart

### Admin Routes
- `/admin/dashboard` - Admin dashboard
- `/admin/categories` - Category management
- `/admin/attributes` - Attribute management
- `/admin/products` - Product management
- `/admin/brands` - Brand management
- `/login` - Admin login

## Features Implemented

✅ Category management with parent-child support
✅ Dynamic attribute system
✅ Attribute values with color codes
✅ Category-attribute relationships
✅ Product CRUD operations
✅ Multiple image upload
✅ Automatic variant generation
✅ SKU auto-generation
✅ Stock management
✅ Price & discount pricing
✅ Product tags
✅ SEO fields
✅ Frontend product listing
✅ Advanced filtering (category, price, attributes)
✅ Product detail page with variant selection
✅ Shopping cart
✅ Responsive design
✅ Admin panel with sidebar
✅ Form validation
✅ Soft deletes

## Future Enhancements

- Order management system
- Payment gateway integration
- User authentication for customers
- Wishlist functionality
- Product reviews and ratings
- Inventory tracking with alerts
- Barcode generation
- Export/Import products
- Multi-language support
- Advanced reporting

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues and questions, please create an issue in the repository.

