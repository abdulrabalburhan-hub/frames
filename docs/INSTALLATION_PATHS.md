# Installation Directory Guide

## Overview

AlBurhan Frames supports **flexible installation** - you can install it in any directory without modifying `.htaccess` or code files. The application automatically adapts to its installation location.

## Supported Installation Paths

### ✅ Root Directory
```
Installation: C:\xampp\htdocs\
URL: http://localhost/
Config: define('SITE_URL', 'http://localhost');
```

### ✅ Subdirectory (Default)
```
Installation: C:\xampp\htdocs\frames\
URL: http://localhost/frames/
Config: define('SITE_URL', 'http://localhost/frames');
```

### ✅ Custom Path
```
Installation: C:\xampp\htdocs\my-photo-app\
URL: http://localhost/my-photo-app/
Config: define('SITE_URL', 'http://localhost/my-photo-app');
```

### ✅ Production Domain
```
Installation: /var/www/html/ or /var/www/html/subdirectory/
URL: https://yourdomain.com/ or https://yourdomain.com/subdirectory/
Config: define('SITE_URL', 'https://yourdomain.com');
```

## Configuration Steps

### 1. Install Files
Place the application files in your chosen directory:
```bash
# Examples:
mv alburhan-frames /var/www/html/                 # Root
mv alburhan-frames /var/www/html/frames/          # Subdirectory
mv alburhan-frames /var/www/html/photo-app/       # Custom name
```

### 2. Update config.php
Edit `config.php` and set `SITE_URL` to match your installation:

```php
// Root installation
define('SITE_URL', 'http://localhost');

// OR subdirectory installation
define('SITE_URL', 'http://localhost/frames');

// OR production domain
define('SITE_URL', 'https://yourdomain.com');
```

**Important Rules:**
- ✅ No trailing slash: `http://localhost/frames` (correct)
- ❌ With trailing slash: `http://localhost/frames/` (incorrect)
- ✅ Use HTTPS in production: `https://yourdomain.com`
- ❌ Don't use HTTP in production: `http://yourdomain.com`

### 3. No .htaccess Changes Needed
The `.htaccess` file automatically detects the installation directory. You don't need to modify:
- `RewriteBase` (commented out by default)
- `RewriteCond` paths (already flexible)
- `ErrorDocument` paths (using relative paths)

## Troubleshooting

### Issue: RewriteBase Error
If you see Apache errors about RewriteBase:
1. Open `.htaccess`
2. Uncomment the line: `# RewriteBase /`
3. Change it to match your path: `RewriteBase /frames/` (if in subdirectory)

### Issue: 404 on Assets (CSS/JS not loading)
**Problem:** SITE_URL is incorrect
**Solution:** 
- Check browser console (F12) for failed requests
- Note the path in the error (e.g., `/frames/assets/css/style.css`)
- Update `SITE_URL` in `config.php` to match that path

### Issue: Short URLs Don't Work from Admin Panel
**Problem:** SITE_URL doesn't match actual URL
**Solution:**
- Visit your site and copy the exact URL from browser
- Update `SITE_URL` to match (without trailing slash)
- Example: Browser shows `http://localhost/photo-app/`, set `SITE_URL` to `http://localhost/photo-app`

### Issue: Broken Links in Admin Panel
**Problem:** SITE_URL has trailing slash
**Solution:**
- Remove trailing slash from SITE_URL
- ❌ `http://localhost/frames/`
- ✅ `http://localhost/frames`

## Migration Between Directories

If you need to move the application to a different directory:

### Step 1: Move Files
```bash
# Example: Moving from /frames/ to /photo-app/
mv /var/www/html/frames /var/www/html/photo-app
```

### Step 2: Update config.php
```php
// Old
define('SITE_URL', 'http://localhost/frames');

// New
define('SITE_URL', 'http://localhost/photo-app');
```

### Step 3: Update Database Short URLs (Optional)
If you've saved short URLs in the database with full paths:
```sql
UPDATE frames 
SET short_url = REPLACE(short_url, '/frames/', '/photo-app/')
WHERE short_url LIKE '%/frames/%';
```

### Step 4: Clear Browser Cache
Press `Ctrl + Shift + Delete` and clear cached images and files.

## Verification Checklist

After installation, verify everything works:

- [ ] Homepage loads: `http://yourdomain.com/` or `http://yourdomain.com/subdirectory/`
- [ ] Admin login loads: `http://yourdomain.com/admin/`
- [ ] CSS styles load correctly (check page source)
- [ ] Logo image displays in navbar
- [ ] Can login to admin panel
- [ ] Can upload a frame (test GD library)
- [ ] Gallery page shows uploaded frames
- [ ] Frame editor opens correctly
- [ ] Short URL copy button shows correct full URL

## Production Deployment

For production servers:

1. **Set correct SITE_URL:**
   ```php
   define('SITE_URL', 'https://yourdomain.com');
   // OR
   define('SITE_URL', 'https://yourdomain.com/frames');
   ```

2. **Use HTTPS:**
   - Always use `https://` in production
   - Configure SSL certificate first
   - Update SITE_URL after SSL is active

3. **Update .htaccess if needed:**
   - Most shared hosting works with default settings
   - If you see 500 errors, comment out problematic directives
   - Contact hosting support for mod_rewrite confirmation

4. **Test thoroughly:**
   - All pages load over HTTPS
   - No mixed content warnings in console
   - Short URLs generate with HTTPS
   - Image uploads work
   - Downloads work

## Common Hosting Scenarios

### Shared Hosting (cPanel/Plesk)
```
Installation: public_html/frames/
URL: https://yourdomain.com/frames/
Config: define('SITE_URL', 'https://yourdomain.com/frames');
```

### VPS/Dedicated Server
```
Installation: /var/www/html/
URL: https://yourdomain.com/
Config: define('SITE_URL', 'https://yourdomain.com');
```

### Subdomain
```
Installation: /var/www/frames.yourdomain.com/
URL: https://frames.yourdomain.com/
Config: define('SITE_URL', 'https://frames.yourdomain.com');
```

### Docker Container
```
Installation: /var/www/html/
URL: http://localhost:8080/ (mapped port)
Config: define('SITE_URL', 'http://localhost:8080');
```

## Technical Details

### How It Works

1. **Apache mod_rewrite** detects the request URI automatically
2. **RewriteCond** rules use relative paths (not absolute)
3. **PHP includes** use relative paths from script location
4. **SITE_URL constant** provides absolute URLs for links/redirects

### What Changed from Original

**Before (Hardcoded):**
```apache
RewriteBase /frames/
RewriteCond %{REQUEST_URI} !^/frames/(admin|assets)
ErrorDocument 404 /404.php
```

**After (Flexible):**
```apache
# RewriteBase / (commented - auto-detected)
RewriteCond %{REQUEST_URI} !/(admin|assets)/
ErrorDocument 404 404.php
```

The app now works in **any directory** without code changes!

---

**Last Updated:** February 2026  
**Version:** 1.0  
**Part of:** AlBurhan Frames Project
