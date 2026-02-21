# Testing Guide: Download Tracking & Campaign Supporters

## Pre-Testing Checklist

Before testing, ensure:
- ✅ Database migration applied successfully
- ✅ Directory `uploads/supporters/thumbs/` exists
- ✅ GD extension enabled in PHP
- ✅ No PHP errors in error log
- ✅ Browser cache cleared

---

## Test 1: Database Structure

### Objective
Verify database tables and columns exist

### Steps
1. Open phpMyAdmin
2. Select `alburhan_frames` database
3. Run this query:

```sql
-- Check all components
SELECT 
    'frames.download_count' as component,
    CASE WHEN COUNT(*) > 0 THEN '✅ OK' ELSE '❌ MISSING' END as status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'alburhan_frames' 
    AND TABLE_NAME = 'frames' 
    AND COLUMN_NAME = 'download_count'

UNION ALL

SELECT 
    'frame_supporters table',
    CASE WHEN COUNT(*) > 0 THEN '✅ OK' ELSE '❌ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'alburhan_frames' 
    AND TABLE_NAME = 'frame_supporters';
```

### Expected Result
```
| component                 | status  |
|--------------------------|---------|
| frames.download_count    | ✅ OK   |
| frame_supporters table   | ✅ OK   |
```

### If Failed
- Re-run migration: `migrations/add_supporters_and_stats.sql`
- Check MySQL error log

---

## Test 2: Directory Permissions

### Objective
Verify thumbnail directory exists and is writable

### Steps

**Windows (XAMPP)**:
```cmd
cd c:\xampp\htdocs\frames
dir uploads\supporters\thumbs
```

**Linux/Mac**:
```bash
cd /path/to/frames
ls -la uploads/supporters/thumbs
stat uploads/supporters/thumbs
```

### Expected Result
- Directory exists
- Writable permissions (Windows: normal folder, Linux: 755 or 775)

### If Failed
```bash
# Create directory
mkdir uploads/supporters/thumbs

# Set permissions (Linux/Mac)
chmod -R 755 uploads/supporters
```

---

## Test 3: Frontend Display - Gallery Page

### Objective
Verify download count badge appears on frame cards

### Steps
1. Navigate to: `http://localhost/frames/gallery.php`
2. Look at frame cards
3. Check for download count badge (bottom-left corner)

### Expected Result - New Installation (0 downloads)
- No badge visible (only shows when count > 0)

### Expected Result - After Downloads
- Green badge visible: "1", "12", "1.2K", etc.
- Badge shows download icon
- Badge at bottom-left of frame image

### Visual Check
```
Frame Card:
┌─────────────────────┐
│  [Frame Image]      │
│                     │
│  ↖ 0 downloads      │ ← Should appear here (if > 0)
├─────────────────────┤
│  Frame Name         │
└─────────────────────┘
```

### If Failed
- Check browser console for errors
- Verify `download_count` column exists
- Clear browser cache
- Check `gallery.php` file was modified correctly

---

## Test 4: Frontend Display - Frame Editor Page

### Objective
Verify campaign supporters gallery appears

### Steps
1. Navigate to a frame: `http://localhost/frames/frame.php?id={frame_unique_id}`
2. Scroll to right sidebar
3. Look for "Campaign Supporters" card

### Expected Result - New Installation (0 supporters)
```
┌─────────────────────────┐
│ Campaign Supporters (0) │
├─────────────────────────┤
│          [+]            │
│ Be the first to support │
│    this campaign!       │
└─────────────────────────┘
```

### Expected Result - With Supporters
```
┌─────────────────────────┐
│ Campaign Supporters (5) │
├─────────────────────────┤
│ [👤][👤][👤][👤][👤]  │ ← Thumbnail grid
└─────────────────────────┘
```

### If Failed
- Open browser console (F12)
- Check for JavaScript errors
- Verify API endpoint: `api-supporters.php`
- Test API directly: `http://localhost/frames/api-supporters.php?frame_id=1`

---

## Test 5: Frontend Display - Admin Dashboard

### Objective
Verify statistics display in admin panel

### Steps
1. Login to admin: `http://localhost/frames/admin/`
2. View dashboard
3. Check stats cards at top

### Expected Result - Stats Cards
```
┌────────────┐  ┌────────────┐  ┌────────────┐
│ Frames     │  │ Downloads  │  │ Supporters │
│    X       │  │     Y      │  │      Z     │
└────────────┘  └────────────┘  └────────────┘
```

### Expected Result - Frame Cards
Each frame card should show:
- ✅ Green badge: "X downloads"
- ✅ Badge below date
- ✅ Format: "0 downloads", "245 downloads", etc.

### If Failed
- Check if admin logged in
- Verify session active
- Check `admin/dashboard.php` modifications
- Check database query returns data

---

## Test 6: Download Functionality

### Objective
Test complete download flow with tracking

### Steps
1. Navigate to a frame editor page
2. Upload a photo
3. Adjust position/zoom as needed
4. Click "Download Photo" button
5. Wait for processing
6. Save downloaded image

### Expected Result
- ✅ Image downloads successfully
- ✅ No errors in browser console
- ✅ No PHP errors in error log

### Verification After Download
Run these checks:

**Check 1: Download Count Incremented**
```sql
SELECT id, frame_name, download_count 
FROM frames 
ORDER BY id;
```
Expected: download_count increased by 1

**Check 2: Thumbnail Created**
```sql
SELECT * FROM frame_supporters 
ORDER BY created_at DESC 
LIMIT 1;
```
Expected: New record with thumbnail_path

**Check 3: File Exists**
Check if thumbnail file exists at path from database:
- Windows: `c:\xampp\htdocs\frames\uploads\supporters\thumbs\`
- Linux: `/path/to/frames/uploads/supporters/thumbs/`

Expected: JPEG file exists, ~3-5 KB size

### If Failed

**Issue: Download works but no thumbnail**
- Check PHP error log
- Verify GD extension: `<?php phpinfo(); ?>`
- Check directory permissions
- Check `process.php` modifications

**Issue: Download fails completely**
- Check browser console
- Check Network tab in developer tools
- Verify `process.php` has no syntax errors
- Check database connection

---

## Test 7: Thumbnail Quality

### Objective
Verify thumbnails are properly sized and optimized

### Steps
1. Complete a download (Test 6)
2. Navigate to: `uploads/supporters/thumbs/`
3. Check newest thumbnail file
4. Open in image viewer

### Expected Result
- ✅ Square image (1:1 aspect ratio)
- ✅ Size: 120x120 pixels
- ✅ Format: JPEG
- ✅ File size: 3-5 KB (approximately)
- ✅ Quality: Clearly visible, not pixelated
- ✅ Shows framed photo (not just user photo)

### How to Check File Size
**Windows**: Right-click → Properties → Size
**Linux/Mac**: `ls -lh uploads/supporters/thumbs/` or `stat filename`

### If Issues
- Check `saveSupporterThumbnail()` function in `process.php`
- Verify JPEG quality setting (should be 75)
- Check image dimensions in code (should be 120x120)

---

## Test 8: API Endpoint

### Objective
Verify API returns supporter data correctly

### Steps
1. Complete at least one download (Test 6)
2. Open browser
3. Navigate to: `http://localhost/frames/api-supporters.php?frame_id=1`

### Expected Result - JSON Response
```json
{
    "success": true,
    "frame_id": 1,
    "download_count": 1,
    "total_supporters": 1,
    "supporters": [
        {
            "thumbnail": "uploads/supporters/thumbs/supporter_1_1234567890_5678.jpg",
            "created_at": "2026-02-22 10:30:45"
        }
    ],
    "limit": 50,
    "offset": 0,
    "has_more": false
}
```

### If Failed
- Check if `api-supporters.php` file exists
- Verify frame_id is valid
- Check PHP syntax errors
- Test with different frame_id values

---

## Test 9: Gallery Updates Dynamically

### Objective
Verify supporters gallery refreshes after new downloads

### Steps
1. Open frame editor in one browser tab
2. Note supporter count
3. Open frame editor in incognito/private window
4. Complete a download in incognito window
5. Refresh first tab
6. Check if supporter count increased

### Expected Result
- ✅ New thumbnail appears in gallery
- ✅ Count badge updates (+1)
- ✅ Thumbnail shows in grid

### If Failed
- Check if AJAX loading works
- Verify API returns updated data
- Check browser cache settings

---

## Test 10: Multi-Download Stress Test

### Objective
Verify system handles multiple downloads

### Steps
1. Complete 5 downloads in quick succession
2. Check database
3. Check file system
4. Check admin dashboard

### Expected Result
- ✅ All 5 downloads counted correctly
- ✅ 5 thumbnails created
- ✅ No duplicate thumbnails
- ✅ All thumbnails have unique filenames
- ✅ No database errors
- ✅ Admin dashboard shows correct totals

### Query to Verify
```sql
-- Check counts match
SELECT 
    f.id,
    f.frame_name,
    f.download_count,
    COUNT(fs.id) as actual_supporters
FROM frames f
LEFT JOIN frame_supporters fs ON f.id = fs.frame_id
GROUP BY f.id;
```

Expected: `download_count` = `actual_supporters`

### If Issues
- Check for race conditions
- Verify transaction handling
- Check file system write speed

---

## Test 11: Mobile Responsiveness

### Objective
Verify features work on mobile devices

### Steps
1. Open browser developer tools (F12)
2. Enable device emulation
3. Select mobile device (e.g., iPhone 12)
4. Navigate through:
   - Gallery page
   - Frame editor
   - Admin dashboard

### Expected Result - Gallery Page
- ✅ Download badge visible and readable
- ✅ Badge doesn't overlap frame name
- ✅ Touch targets adequate size

### Expected Result - Frame Editor
- ✅ Supporters gallery scrolls smoothly
- ✅ Thumbnails sized appropriately
- ✅ Card doesn't break layout
- ✅ Touch-friendly grid spacing

### Expected Result - Admin Dashboard
- ✅ Stats cards stack vertically
- ✅ Download counts readable
- ✅ No horizontal scrolling

### If Failed
- Check CSS media queries
- Verify responsive grid classes
- Test on actual mobile device

---

## Test 12: Browser Compatibility

### Objective
Verify features work across browsers

### Steps
Test in:
- ✅ Chrome/Edge
- ✅ Firefox  
- ✅ Safari (if available)
- ✅ Mobile browsers

### Expected Result
All features work identically across browsers

### If Issues
- Check JavaScript console for browser-specific errors
- Verify CSS prefix support
- Test ES6 compatibility

---

## Performance Test

### Objective
Verify no significant performance degradation

### Steps
1. Use browser's Performance/Network tab
2. Load gallery page
3. Load frame editor
4. Complete a download
5. Check timing

### Expected Metrics
- Gallery page load: < 2 seconds
- Frame editor load: < 2 seconds
- Download processing: < 3 seconds (total)
- Thumbnail generation: < 100ms (overhead)
- API response: < 200ms

### If Performance Issues
- Check image optimization
- Verify database indexes created
- Check server load
- Review thumbnail generation code

---

## Regression Testing

### Objective
Verify existing features still work

### Checklist
- ✅ Frame upload works
- ✅ Slot definition works
- ✅ Photo editing works
- ✅ Canvas rendering works
- ✅ Multi-photo frames work
- ✅ Admin authentication works
- ✅ Short URL saving works
- ✅ Frame deletion works

### If Any Fail
- Check modified files for syntax errors
- Review changes made
- Compare with backup
- Check error logs

---

## Final Verification Checklist

After completing all tests:

### Database
- [ ] Migration applied successfully
- [ ] download_count column exists
- [ ] frame_supporters table exists
- [ ] Indexes created
- [ ] Foreign keys working

### File System
- [ ] uploads/supporters/thumbs/ directory exists
- [ ] Directory is writable
- [ ] Thumbnails being created
- [ ] Thumbnails are correct size (~3-5 KB)

### Frontend - User Facing
- [ ] Gallery page shows download counts
- [ ] Frame editor shows supporters gallery
- [ ] API endpoint responds correctly
- [ ] Thumbnails display properly
- [ ] Mobile responsive

### Frontend - Admin
- [ ] Stats cards show correct data
- [ ] Individual frame cards show download counts
- [ ] Statistics accurate

### Functionality
- [ ] Downloads increment counter
- [ ] Thumbnails save to database
- [ ] Thumbnails save to disk
- [ ] Gallery updates dynamically
- [ ] No PHP errors
- [ ] No JavaScript errors

### Performance
- [ ] Page load times acceptable
- [ ] Download not significantly slower
- [ ] API responses fast
- [ ] No memory issues

---

## Bug Report Template

If you find issues, document:

```
BUG REPORT

Test: [Test number/name]
Browser: [Chrome/Firefox/Safari]
OS: [Windows/Linux/Mac]
PHP Version: [e.g., 8.1.12]

Expected Behavior:
[What should happen]

Actual Behavior:
[What actually happened]

Steps to Reproduce:
1. [Step 1]
2. [Step 2]
3. [Step 3]

Error Messages:
[Copy any error messages]

Screenshots:
[Attach if applicable]

Database State:
[Result of relevant queries]
```

---

## Success Criteria

All tests pass when:
- ✅ No PHP errors in logs
- ✅ No JavaScript errors in console
- ✅ All database records created correctly
- ✅ All thumbnails generated properly
- ✅ All UI elements display correctly
- ✅ Performance remains acceptable
- ✅ Mobile experience is good
- ✅ Existing features unaffected

---

**Testing Complete?** Congratulations! Your download tracking and campaign supporters features are ready for production use.

**Next Steps**:
1. Create database backup
2. Document any custom configurations
3. Monitor error logs for first few days
4. Collect user feedback

---

**Version**: 1.0  
**Last Updated**: February 22, 2026
