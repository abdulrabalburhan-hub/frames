# AlBurhan Frames (البرہان)

A professional photo framing web application that allows users to upload photos, place them in decorative frames, and download the final images. Built with a mobile-first approach to serve 99% mobile users.

## 🌟 Features

### Core Functionality
- **Multi-Photo Frame Support** - Frames can contain multiple photo slots with custom positioning
- **Interactive Editor** - Canvas-based photo editor with drag, zoom, and rotation controls
- **Smart Routing** - Automatically directs to single frame or gallery based on available frames
- **URL Shortening Integration** - Works with external shorteners (albn.org) for easy sharing
- **Mobile-First Design** - Optimized for touch devices with responsive layouts
- **Download Tracking** - Track how many times each frame has been downloaded
- **Campaign Supporters Gallery** - Showcase thumbnails of supporters who downloaded frames

### Admin Panel
- **Frame Management** - Upload, configure, and delete frames
- **Visual Slot Editor** - Define photo slot positions using interactive canvas
- **Short URL Tracking** - Save and manage shortened URLs for each frame
- **Session Management** - Secure admin authentication with session timeout
- **Download Analytics** - View download statistics and supporter counts for each frame

### User Experience
- **Frame Gallery** - Browse all available frames in responsive grid layout with download counts
- **Photo Upload** - Support for JPG and PNG images with size limits
- **Real-time Preview** - See changes instantly on canvas
- **Touch-Friendly Controls** - 44px minimum touch targets, iOS zoom prevention
- **Lazy Loading** - Optimized image loading for better performance
- **Supporters Showcase** - View campaign supporters gallery when editing frames

## 🚀 Quick Start

### Prerequisites
- **PHP 8.0+** with GD extension enabled
- **MySQL 5.7+** or MariaDB
- **Apache** with mod_rewrite enabled
- **Web Server** (XAMPP, WAMP, or production server)

### Installation

**Flexible Installation:** This application can be installed in any directory - root (`/`), subdirectory (`/frames/`, `/photo-app/`), or custom path. No `.htaccess` modifications needed!

1. **Clone or download** the project to your web server:
   ```bash
   # Example installations (choose any):
   cd c:\xampp\htdocs\              # For root: http://localhost/
   cd c:\xampp\htdocs\frames\        # For subdirectory: http://localhost/frames/
   cd c:\xampp\htdocs\my-app\        # For custom: http://localhost/my-app/
   ```

2. **Create the database**:
   - Open phpMyAdmin
   - Create database: `alburhan_frames`
   - Import: `database.sql`

3. **Configure database connection and site URL**:
   - Edit `config.php`
   - Update credentials and SITE_URL:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'alburhan_frames');
     define('SITE_URL', 'http://localhost/frames'); // Change to your actual URL
     ```
   - **Important:** Set `SITE_URL` to match your installation directory:
     - Root install: `http://localhost`
     - Subdirectory: `http://localhost/frames`
     - Production: `https://yourdomain.com` or `https://yourdomain.com/app`

4. **Enable GD extension** (for image processing):
   - Open `php.ini`
   - Uncomment: `extension=gd`
   - Restart Apache

5. **Set directory permissions**:
   ```bash
   # Ensure uploads directory is writable
   chmod -R 755 uploads/
   ```

6. **Access the application**:
   - Local: `http://localhost/frames/` (or your configured path)
   - Admin: `http://localhost/frames/admin/`
   - Default login: `admin` / `Admin@123`

### First-Time Setup

1. **Login to admin panel**
2. **Upload your first frame** - Click "Upload New Frame"
3. **Define photo slots** - Use the visual editor to set slot positions
4. **Test the frame** - Visit the user-facing editor
5. **Create short URL** - Use albn.org to create shareable links

### ⭐ New Features Setup (Download Tracking & Campaign Supporters)

To enable download tracking and campaign supporters gallery:

1. **Run database migration**:
   - Import `migrations/add_supporters_and_stats.sql` in phpMyAdmin
   - Or see `SETUP_NEW_FEATURES.md` for quick setup

2. **Create supporters directory**:
   ```bash
   mkdir uploads/supporters/thumbs
   chmod -R 755 uploads/supporters/
   ```

3. **Features will automatically activate**:
   - Download counts appear on gallery frame cards
   - Supporter thumbnails display on frame editor page
   - Admin dashboard shows download statistics

📖 **Full Documentation**: See `docs/CAMPAIGN_SUPPORTERS_FEATURE.md` for complete details

## 📁 Project Structure

```
frames/
├── admin/                      # Admin panel
│   ├── dashboard.php          # Frame management interface
│   ├── upload-frame.php       # Frame upload handler
│   ├── define-slots.php       # Visual slot positioning editor
│   ├── manage-short-urls.php  # URL management interface
│   ├── index.php              # Admin login page
│   └── logout.php             # Session termination
├── assets/                     # Static resources
│   ├── css/
│   │   └── style.css          # Mobile-first responsive styles (488+ lines)
│   ├── js/
│   │   └── editor.js          # Canvas editor logic
│   └── images/
│       └── logo.png           # AlBurhan branding logo
├── uploads/                    # User-generated content
│   ├── frames/                # Frame images
│   │   └── thumbs/            # Thumbnails (200x200)
│   └── photos/                # User photo uploads
├── tests/                      # Testing utilities
│   ├── test_db.php            # Database connection test
│   └── test-upload.php        # Upload functionality test
├── migrations/                 # Database migrations
│   ├── update_database.sql    # Multi-photo feature migration
│   └── add_short_url_field.sql # Short URL field addition
├── config.php                  # Database & app configuration
├── index.php                   # Smart routing entry point
├── gallery.php                 # Frame selection interface
├── frame.php                   # User-facing editor
├── process.php                 # Image processing backend
├── download.php                # Final image download
├── redirect.php                # Generic redirect handler
├── database.sql                # Fresh database schema
├── .htaccess                   # Apache rewrite rules
└── README.md                   # This file
```

## 🛠️ Tech Stack

### Backend
- **PHP 8.2.12** - Core application logic
- **MySQL** - Data persistence
- **GD Library** - Image manipulation (thumbnails, composition)
- **PDO/MySQLi** - Database abstraction

### Frontend
- **Bootstrap 5** - UI framework
- **jQuery 3.x** - DOM manipulation
- **HTML5 Canvas** - Interactive photo editor
- **CSS3** - Custom mobile-first styles
- **Bootstrap Icons** - Icon set

### Server
- **Apache** - Web server with mod_rewrite
- **XAMPP** - Local development environment

## 🎨 Mobile Optimization

### Design Principles
- **Mobile-First CSS** - Base styles target 375px viewport
- **Touch-Friendly** - Minimum 44px tap targets
- **iOS Zoom Prevention** - 16px font-size on inputs
- **Responsive Grid** - `col-6` (mobile) → `col-md-4` → `col-lg-3`
- **Viewport Settings** - `maximum-scale=5.0, user-scalable=yes`

### Key Optimizations
```css
/* Touch-friendly buttons */
.btn { min-height: 44px; }

/* iOS zoom prevention */
input, select, textarea { font-size: 16px; }

/* Responsive canvas */
#canvas-container {
  width: 250px; /* mobile */
  width: 500px; /* desktop */
}

/* Mobile gallery grid */
.col-6.col-sm-6.col-md-4.col-lg-3 { /* 2-4 columns */ }
```

## 🔗 URL Shortener Integration

### Workflow

1. **Upload frame** in admin panel
2. **Copy full URL** from dashboard (e.g., `https://alburhan.online/frame.php?id=frame_abc123`)
3. **Create short link** at albn.org:
   - Source: `https://alburhan.online/frame.php?id=frame_abc123`
   - Short: `https://albn.org/seeratframe72`
4. **Save short URL** in dashboard (optional, for reference)
5. **Share** the short link with users

### URL Types

| Type | Example | Behavior |
|------|---------|----------|
| **Direct Frame** | `frame.php?id=frame_abc123` | Opens specific frame editor |
| **Gallery** | `gallery.php` | Shows all frames |
| **Smart Route** | `/` | Auto: 1 frame→direct, multiple→gallery |

### External Shortener Setup
- Service: **albn.org** (or Bitly, TinyURL, etc.)
- No DNS configuration needed
- Works via HTTP redirects
- Track URLs in dashboard for reference

For detailed shortener setup, see `docs/` folder.

## 🧪 Testing

### Database Connection Test
```bash
# Navigate to: http://localhost/frames/tests/test_db.php
# Verifies: Database connection, admin user, password hash
```

### Upload Functionality Test
```bash
# Navigate to: http://localhost/frames/tests/test-upload.php
# Checks: GD library, directory permissions, PHP limits
```

### Manual Testing Checklist
- [ ] Admin login works
- [ ] Frame upload creates thumbnail
- [ ] Slot positioning saves correctly
- [ ] User can upload photo
- [ ] Canvas controls respond to touch/mouse
- [ ] Download produces correct image
- [ ] Mobile layout displays properly
- [ ] Short URL saves in database

## 🔧 Configuration

### Database Settings (`config.php`)
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'alburhan_frames');
define('SITE_URL', 'http://localhost/frames'); // No trailing slash!
```

**IMPORTANT:** `SITE_URL` must match your installation directory:
- Root directory: `http://localhost` or `https://yourdomain.com`
- Subdirectory: `http://localhost/frames` or `https://yourdomain.com/photo-app`
- **Never** include a trailing slash

### Installation Directory Flexibility
The application automatically detects its installation path. You can install it:
- At server root: `/` (e.g., `http://localhost/`)
- In a subdirectory: `/frames/` (e.g., `http://localhost/frames/`)
- Any custom path: `/my-photo-app/` (e.g., `http://localhost/my-photo-app/`)

No `.htaccess` modifications required - just set the `SITE_URL` in `config.php` correctly.

### Upload Limits
```php
// Adjust in php.ini:
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 128M
```

### Session Configuration
- **Timeout**: 24 hours (86400 seconds)
- **Storage**: Server-side PHP sessions
- **Security**: Regenerates session ID on login

## 📱 Browser Support

### Tested & Optimized For:
- ✅ **iOS Safari** (iOS 14+)
- ✅ **Chrome Mobile** (Android 10+)
- ✅ **Samsung Internet**
- ✅ **Chrome Desktop** (v100+)
- ✅ **Firefox Desktop** (v100+)
- ✅ **Edge** (Chromium-based)

### Required Features:
- HTML5 Canvas
- CSS Grid & Flexbox
- JavaScript ES6+
- Touch Events API

## 🚨 Common Issues

### Frame Upload Fails
**Problem**: "GD library not loaded"  
**Solution**: Enable GD extension in `php.ini`, restart Apache

**Problem**: "Incorrect parameter count for bind_param"  
**Solution**: Fixed in current version (ssssisi signature)

### Images Not Displaying
**Problem**: 404 on image paths  
**Solution**: Verify `uploads/` directory exists with correct permissions

### Mobile Layout Broken
**Problem**: Viewport too zoomed in  
**Solution**: Check meta viewport tag includes `maximum-scale=5.0`

### Short URLs Not Saving
**Problem**: Border doesn't turn green  
**Solution**: Check database has `short_url` column (run migrations)

## 🔐 Security Considerations

- ✅ **SQL Injection**: Protected via prepared statements
- ✅ **XSS**: Output escaped with `htmlspecialchars()`
- ✅ **CSRF**: Admin-only actions require session validation
- ✅ **File Upload**: Validates image types, generates unique filenames
- ✅ **Session Hijacking**: Uses `session_regenerate_id()`
- ⚠️ **Production**: Change default admin password immediately

## 📈 Future Enhancements

- [ ] PWA manifest for "Add to Home Screen"
- [ ] Favicon with AlBurhan logo
- [ ] Multi-language support (Arabic/English)
- [ ] Social media sharing integration
- [ ] Analytics tracking
- [ ] Batch frame upload
- [ ] User accounts with saved frames
- [ ] Custom watermark placement

## 👨‍💻 Development

### Adding a New Frame
1. Upload via admin panel
2. Image stored in `uploads/frames/`
3. Thumbnail generated at `uploads/frames/thumbs/`
4. Database entry in `frames` table

### Database Migrations
```bash
# Located in: migrations/
# Apply manually via phpMyAdmin
# Files: update_database.sql, add_short_url_field.sql
```

### CSS Variables (Branding)
```css
:root {
  --primary-color: #2563eb;
  --accent-color: #667eea;
  --success-color: #10b981;
  --danger-color: #ef4444;
}
```

## 📄 License

Private project for AlBurhan organization. Not licensed for public use.

## 📞 Support

For issues or questions, contact the development team or refer to:
- `tests/` - Diagnostic tools
- `migrations/` - Database update scripts
- Error logs in Apache error.log

---

**Built with ❤️ for AlBurhan (البرہان)**  
*Optimized for mobile users worldwide*
