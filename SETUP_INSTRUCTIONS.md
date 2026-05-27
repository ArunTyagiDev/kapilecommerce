# Setup Instructions

## Quick Start Guide

### Step 1: Install Dependencies
```bash
cd D:\xampp8.2\htdocs\FullEcom
composer install
```

### Step 2: Configure Environment
1. Copy `.env.example` to `.env` (if not exists)
2. Update database credentials in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fullecom
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Step 3: Generate Application Key
```bash
php artisan key:generate
```

### Step 4: Create Database
Create a MySQL database named `fullecom`:
```sql
CREATE DATABASE fullecom;
```

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Seed Database (Optional)
This will create:
- Admin user (admin@example.com / password)
- Sample categories, attributes, and brands
```bash
php artisan db:seed
```

### Step 7: Create Storage Link
```bash
php artisan storage:link
```

### Step 8: Start Development Server
```bash
php artisan serve
```

Visit: http://localhost:8000

## Default Login Credentials

**Admin Panel:** http://localhost:8000/login
- Email: `admin@example.com`
- Password: `password`

## Testing the System

### 1. Create a Category
1. Login to admin panel
2. Go to Categories → Add Category
3. Create "Shoes" category

### 2. Create Attributes
1. Go to Attributes → Add Attribute
2. Create "Size" attribute with values: 6, 7, 8, 9, 10, 11
3. Create "Color" attribute with values: Black, White, Red

### 3. Attach Attributes to Category
1. Go to Categories → Click "Attributes" on Shoes category
2. Attach "Size" and "Color" attributes
3. Mark "Size" as required

### 4. Create a Product with Variants
1. Go to Products → Add Product
2. Fill in product details
3. Select "Shoes" category
4. Go to "Attributes & Variants" tab
5. Select sizes: 6, 7, 8
6. Select colors: Black, White
7. Check "Generate Variants Automatically"
8. Set default stock quantity for variants
9. Save product

**Result:** System will create 6 variants:
- Size 6 / Black
- Size 6 / White
- Size 7 / Black
- Size 7 / White
- Size 8 / Black
- Size 8 / White

### 5. Test Frontend
1. Visit http://localhost:8000/products
2. Use filters to find products
3. Click on a product to see variant selection
4. Select size and color
5. Add to cart

## Common Issues & Solutions

### Issue: Storage link not working
**Solution:** Run `php artisan storage:link` and ensure `public/storage` directory exists

### Issue: Images not displaying
**Solution:** 
1. Check `storage/app/public` directory exists
2. Ensure storage link is created
3. Check file permissions

### Issue: 500 Error
**Solution:**
1. Check `.env` file configuration
2. Clear cache: `php artisan config:clear`
3. Check Laravel logs in `storage/logs`

### Issue: Database connection error
**Solution:**
1. Verify MySQL is running
2. Check database credentials in `.env`
3. Ensure database exists

## File Permissions (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Optimize autoloader: `composer install --optimize-autoloader --no-dev`

## Additional Notes

- All images are stored in `storage/app/public`
- Product images are stored in `storage/app/public/products`
- Category images are stored in `storage/app/public/categories`
- Brand logos are stored in `storage/app/public/brands`

## Support

For issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Browser console for JavaScript errors
3. Network tab for API errors

