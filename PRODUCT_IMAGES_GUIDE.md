# Product Images Setup Guide

## ✅ Directory Verification Complete

### Directory Structure
```
assets/
└── images/
    └── products/  (drwxrwxrwx - permissions OK)
```

**Status**: ✅ Directory exists with correct permissions (777)
**Location**: `/assets/images/products/`
**Upload via**: Admin panel at `/admin/products.php`

---

## 📸 How to Upload Product Images

### Method 1: Using Admin Panel (Recommended)

1. **Access Admin Panel**
   - Visit: `/admin/index.php`
   - Login with admin credentials

2. **Add New Product with Image**
   - Go to: `/admin/products.php?action=add`
   - Fill in product details
   - Upload image (JPG, PNG, WEBP - max 5MB)
   - Click "Salvează"

3. **Edit Existing Product**
   - Go to: `/admin/products.php?action=edit&id={product_id}`
   - Upload new image
   - Click "Actualizează"

### Method 2: Manual FTP Upload

1. Connect via FTP/SFTP to server
2. Navigate to: `assets/images/products/`
3. Upload images manually
4. Update product database records to reference image filenames

---

## 🖼️ Image Requirements

### Technical Specifications
- **Formats**: JPG, PNG, WEBP
- **Max Size**: 5MB
- **Recommended Size**:
  - Product Cards: 300x200px (width x height)
  - Product Detail: 500x500px
  - Thumbnails: 100x100px

### File Naming
- Use lowercase letters, numbers, and hyphens only
- Example: `piesa-cusut-singer-123.jpg`
- Avoid spaces and special characters

### Alt Text
- Product images automatically use product name as alt text
- Format: `{product_name} - {brand_name}`
- Good for SEO and accessibility

---

## 📋 Current Image Usage

### Placeholder Images Found
The following pages currently use placeholders when no image exists:

1. **Product Cards** (catalog.php, index.php)
   - Placeholder: `https://via.placeholder.com/300x200?text=Imagine+indisponibilă`
   - Size: 300x200px

2. **Product Detail** (product.php)
   - Placeholder: `https://via.placeholder.com/500x500?text=Imagine+indisponibilă`
   - Size: 500x500px

3. **Cart Thumbnails** (cart.php)
   - Placeholder: `https://via.placeholder.com/100x100?text=No+Image`
   - Size: 100x100px

### Image Display Code
All pages use the following pattern:
```php
<?php if ($product['image']): ?>
    <img src="<?= URL_PRODUCTS . '/' . e($product['image']) ?>"
         alt="<?= e($product['name']) ?>"
         loading="lazy"
         width="300"
         height="200">
<?php else: ?>
    <img src="https://via.placeholder.com/300x200?text=Imagine+indisponibilă"
         alt="<?= e($product['name']) ?>"
         loading="lazy"
         width="300"
         height="200">
<?php endif; ?>
```

---

## 🎯 Next Steps

### 1. Upload Product Images
- [ ] Access admin panel
- [ ] For each product: upload image or set existing image filename
- [ ] Verify images appear on catalog page
- [ ] Verify images appear on product detail pages
- [ ] Verify images appear in cart

### 2. Test Image Display
Visit these URLs to verify:
- Homepage: `/index.php` - Featured products
- Catalog: `/pages/catalog.php` - All products grid
- Product Detail: `/pages/product.php?slug={test_slug}`
- Cart: `/pages/cart.php` - Product thumbnails

### 3. Optimize Images (Optional)
For better performance:
- Compress images using TinyPNG or similar
- Use WebP format for smaller file sizes
- Ensure consistent aspect ratios

---

## 🔧 Troubleshooting

### Images Not Displaying

**Check 1: File Permissions**
```bash
ls -la assets/images/products/
```
Should show: `drwxrwxrwx`

**Check 2: File Existence**
```bash
ls -la assets/images/products/*.jpg
```
Should list uploaded image files.

**Check 3: Database Records**
```sql
SELECT id, name, image FROM products WHERE is_active = 1;
```
Verify `image` column has correct filenames.

**Check 4: URL Configuration**
Verify `URL_PRODUCTS` in `config/config.php`:
```php
define('URL_PRODUCTS', URL_IMAGES . '/products');
```

### Upload Failing

**Check Max File Size**
- PHP upload_max_filesize: `php -i | grep upload`
- Verify against `MAX_FILE_SIZE` in config (5MB)

**Check File Type**
- Allowed: image/jpeg, image/png, image/webp
- Verify file is valid image format

---

## ✅ Implementation Status

- [x] Directory structure verified
- [x] Permissions configured (777)
- [x] Admin upload forms functional
- [x] Placeholder images configured
- [x] Alt text generation implemented
- [x] Lazy loading enabled
- [x] Responsive image sizing
- [ ] Actual product images uploaded *(requires manual action)*

---

## 📞 Need Help?

If you encounter issues:
1. Check browser console for image loading errors
2. Verify file exists in `assets/images/products/`
3. Check database `products.image` column
4. Review server error logs
5. Use `/debug_cart.php` for general diagnostics

---

**Last Updated**: 2026-01-16
**Status**: Ready for image uploads
