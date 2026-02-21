# Quick Setup Guide - Download Tracking & Campaign Supporters

## 🚀 Quick Start (3 Steps)

### Step 1: Run Database Migration
Open phpMyAdmin and run this SQL:

```sql
-- Add download_count column
ALTER TABLE `frames` 
ADD COLUMN `download_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Number of times this frame was downloaded' AFTER `slot_count`;

-- Create supporters table
CREATE TABLE IF NOT EXISTS `frame_supporters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frame_id` int(11) NOT NULL,
  `thumbnail_path` varchar(500) NOT NULL,
  `thumbnail_size` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `frame_id` (`frame_id`),
  KEY `created_at` (`created_at`),
  KEY `idx_frame_created` (`frame_id`, `created_at` DESC),
  CONSTRAINT `frame_supporters_ibfk_1` FOREIGN KEY (`frame_id`) REFERENCES `frames` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Or simply import: `migrations/add_supporters_and_stats.sql`

### Step 2: Create Upload Directory
In your terminal/command prompt:

**Windows (XAMPP)**:
```cmd
cd c:\xampp\htdocs\frames
mkdir uploads\supporters\thumbs
```

**Linux/Mac**:
```bash
cd /path/to/frames
mkdir -p uploads/supporters/thumbs
chmod -R 755 uploads/supporters
```

### Step 3: Test the Feature
1. Visit your site: `http://localhost/frames`
2. Select a frame
3. Upload a photo and download it
4. Check:
   - Download count appears on gallery page ✓
   - Your thumbnail appears in supporters gallery ✓
   - Admin dashboard shows statistics ✓

## ✅ What's Been Added

### Files Modified:
- ✅ `process.php` - Saves thumbnails and tracks downloads
- ✅ `gallery.php` - Shows download counts on frame cards
- ✅ `frame.php` - Displays campaign supporters gallery
- ✅ `admin/dashboard.php` - Shows download statistics

### Files Created:
- ✅ `api-supporters.php` - API endpoint for fetching supporters
- ✅ `migrations/add_supporters_and_stats.sql` - Database migration
- ✅ `docs/CAMPAIGN_SUPPORTERS_FEATURE.md` - Complete documentation

### New Features:
1. **Download Counter** - Tracks downloads per frame
2. **Campaign Supporters Gallery** - Shows supporter thumbnails (120x120px)
3. **Download Statistics** - Admin dashboard analytics
4. **Storage Optimized** - ~3-5 KB per supporter thumbnail

## 📊 What You'll See

### Gallery Page
- Green download count badge on each frame
- Format: "245" or "1.2K" for large numbers

### Frame Editor Page
- "Campaign Supporters" section in right sidebar
- Grid of supporter thumbnails
- Live counter of total supporters

### Admin Dashboard
- 3 stat cards: Total Frames, Total Downloads, Campaign Supporters
- Download count on each frame card

## 🔧 Troubleshooting

**Problem**: Thumbnails not saving  
**Solution**: Check directory permissions and GD extension

**Problem**: Gallery not loading  
**Solution**: Clear browser cache, check browser console

**Problem**: Download count not updating  
**Solution**: Verify database migration completed

## 📝 Notes

- Thumbnails are **120x120px JPEG** at 75% quality (~3-5 KB each)
- Storage: 1,000 supporters ≈ 3-5 MB
- Automatically cleans up when frames are deleted
- Mobile-optimized and responsive
- View-only gallery (no user interaction needed)

## 🎯 Testing Checklist

✅ Database tables created  
✅ Directory created with correct permissions  
✅ Download increments counter  
✅ Thumbnail saves to disk  
✅ Gallery displays on frame editor page  
✅ Badge shows on gallery page  
✅ Admin stats display correctly  

---

**Need Help?** Check the full documentation in `docs/CAMPAIGN_SUPPORTERS_FEATURE.md`
