# Campaign Supporters & Download Tracking Feature

## Overview
This document describes the newly implemented features for tracking frame downloads and displaying a campaign supporters gallery.

## New Features

### 1. Download Statistics Tracking
- **Purpose**: Track how many times each frame has been downloaded
- **Implementation**: Automatic counter increments each time a user downloads a framed photo
- **Display**: Download counts are visible in:
  - Gallery page (frame cards show download count badge)
  - Admin dashboard (stats cards + individual frame cards)

### 2. Campaign Supporters Gallery
- **Purpose**: Showcase thumbnails of people who have supported the campaign by downloading frames
- **Implementation**: When users download their framed photo, a thumbnail is automatically saved
- **Display**: Visible on the frame editor page (frame.php) showing recent supporters
- **Storage Optimization**: Thumbnails are saved as 120x120px JPEG images at 75% quality

## Installation Steps

### 1. Run Database Migration
Execute the SQL migration file to add required tables and columns:

```sql
-- In phpMyAdmin or MySQL console, run:
SOURCE migrations/add_supporters_and_stats.sql;

-- Or manually execute the migration file contents
```

This will:
- Add `download_count` column to `frames` table
- Create `frame_supporters` table to store supporter thumbnails
- Create necessary indexes for optimal performance

### 2. Create Upload Directories
Ensure the supporters thumbnails directory exists with proper permissions:

```bash
# Windows (XAMPP)
mkdir uploads\supporters\thumbs

# Linux/Mac
mkdir -p uploads/supporters/thumbs
chmod -R 755 uploads/supporters
```

### 3. Verify GD Extension
The thumbnail generation requires PHP GD extension. Verify it's enabled:

```php
// Test script
<?php
if (extension_loaded('gd')) {
    echo "GD extension is enabled";
} else {
    echo "Please enable GD extension in php.ini";
}
?>
```

## Feature Details

### Download Tracking
**File**: `process.php`

When a user downloads a framed photo, the system:
1. Generates the composite image
2. Saves a thumbnail (120x120px) to `uploads/supporters/thumbs/`
3. Increments the frame's `download_count`
4. Records the supporter thumbnail in the `frame_supporters` table

**Storage Considerations**:
- Each thumbnail is approximately 3-5 KB (120x120px, JPEG 75% quality)
- 1,000 supporters = ~3-5 MB
- 10,000 supporters = ~30-50 MB
- Thumbnails are automatically cleaned when frames are deleted (CASCADE delete)

### Campaign Supporters Gallery
**Files**: 
- `frame.php` (displays gallery)
- `api-supporters.php` (API endpoint)

**Features**:
- Shows up to 50 most recent supporters
- Responsive grid layout (optimized for mobile)
- Auto-loads when frame page opens
- Updates automatically as new downloads occur
- View-only (no interaction required)

**API Endpoint**: `api-supporters.php`
```javascript
// Example usage
GET /api-supporters.php?frame_id=1&limit=50&offset=0

// Response
{
    "success": true,
    "frame_id": 1,
    "download_count": 245,
    "total_supporters": 245,
    "supporters": [
        {
            "thumbnail": "uploads/supporters/thumbs/supporter_1_1234567890_5678.jpg",
            "created_at": "2026-02-22 10:30:45"
        },
        // ... more supporters
    ],
    "has_more": true
}
```

## Display Locations

### 1. Gallery Page (`gallery.php`)
- Download count badge on each frame card (bottom-left corner)
- Green badge with download icon
- Format: "1.2K" for counts >= 1000

### 2. Frame Editor Page (`frame.php`)
- "Campaign Supporters" card in right sidebar
- Shows supporter count badge
- Scrollable grid of supporter thumbnails (120x120px)
- Empty state message: "Be the first to support this campaign!"

### 3. Admin Dashboard (`admin/dashboard.php`)
- Three stat cards:
  - Total Frames
  - Total Downloads (sum across all frames)
  - Campaign Supporters (total unique supporters)
- Each frame card shows individual download count

## Storage Management

### Thumbnail Specifications
- **Size**: 120x120 pixels (square crop from center)
- **Format**: JPEG
- **Quality**: 75%
- **Average file size**: 3-5 KB per thumbnail

### Storage Estimates
| Supporters | Approx. Storage |
|-----------|----------------|
| 100 | 300-500 KB |
| 1,000 | 3-5 MB |
| 10,000 | 30-50 MB |
| 100,000 | 300-500 MB |

### Cleanup Considerations
If you need to manage storage:

```sql
-- Delete old supporters (older than 6 months)
DELETE FROM frame_supporters 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Keep only most recent 1000 supporters per frame
DELETE fs1 FROM frame_supporters fs1
LEFT JOIN (
    SELECT id FROM frame_supporters 
    WHERE frame_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1000
) fs2 ON fs1.id = fs2.id
WHERE fs1.frame_id = ? AND fs2.id IS NULL;
```

## Database Schema

### New Column: `frames.download_count`
```sql
ALTER TABLE frames 
ADD COLUMN download_count INT(11) NOT NULL DEFAULT 0;
```

### New Table: `frame_supporters`
```sql
CREATE TABLE frame_supporters (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    frame_id INT(11) NOT NULL,
    thumbnail_path VARCHAR(500) NOT NULL,
    thumbnail_size INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (frame_id) REFERENCES frames(id) ON DELETE CASCADE,
    INDEX idx_frame_created (frame_id, created_at DESC)
);
```

## Performance Considerations

### Thumbnail Generation
- **Time**: ~50-100ms per thumbnail (minimal impact)
- **CPU**: Low (single image resize operation)
- **Memory**: ~2-3 MB per thumbnail generation

### Gallery Loading
- **Initial Load**: Fetches 50 thumbnails via AJAX
- **Lazy Loading**: Images use `loading="lazy"` attribute
- **Caching**: Consider implementing browser caching headers

### Database Queries
- Indexed queries for optimal performance
- Composite index on `(frame_id, created_at)` for fast lookups
- No N+1 query problems

## Troubleshooting

### Issue: Thumbnails not being saved
**Check**:
1. Directory exists: `uploads/supporters/thumbs/`
2. Write permissions: `chmod 755 uploads/supporters/`
3. GD extension enabled in PHP
4. Check PHP error logs for image processing errors

### Issue: Supporters gallery not loading
**Check**:
1. Database migration completed
2. API endpoint accessible: `api-supporters.php`
3. Browser console for JavaScript errors
4. Network tab for API response

### Issue: Download count not incrementing
**Check**:
1. `download_count` column exists in `frames` table
2. Check `process.php` for errors in `incrementDownloadCount()` function
3. Database connection is working

## Future Enhancements

### Potential Additions
1. **Download Analytics**:
   - Track download timestamps
   - Geographic distribution
   - Peak download times

2. **Supporter Profiles** (optional):
   - Allow supporters to add names (optional)
   - Social sharing integration

3. **Image Optimization**:
   - WebP format support for better compression
   - Progressive JPEG loading

4. **Gallery Features**:
   - Pagination for large supporter lists
   - Filter by date range
   - Search functionality

5. **Admin Tools**:
   - Export supporter data
   - Bulk thumbnail regeneration
   - Storage usage reports

## Security Considerations

### Privacy
- Supporter thumbnails are anonymous (no personal data stored)
- No EXIF data retained in thumbnails
- Only framed photos displayed (not original uploads)

### File Security
- Thumbnails saved with random filenames
- Directory traversal protection in API endpoint
- File type validation (JPEG only for thumbnails)

### Database Security
- Prepared statements prevent SQL injection
- Foreign key constraints maintain referential integrity
- Cascade deletes clean up orphaned records

## Testing Checklist

- [ ] Database migration runs successfully
- [ ] Upload directories created with correct permissions
- [ ] Frame download increments counter
- [ ] Thumbnail is saved to disk
- [ ] Thumbnail record created in database
- [ ] Gallery displays on frame.php
- [ ] API endpoint returns correct data
- [ ] Download count badge shows in gallery.php
- [ ] Admin dashboard shows statistics
- [ ] Empty state displays when no supporters
- [ ] Mobile responsive layout works
- [ ] Performance is acceptable (< 200ms page load)

## Support

For issues or questions:
1. Check the error logs: `php_error.log`
2. Verify database schema matches migration
3. Test API endpoint directly in browser
4. Review browser console for JavaScript errors

---

**Version**: 1.0  
**Last Updated**: February 22, 2026  
**Author**: AlBurhan Development Team
