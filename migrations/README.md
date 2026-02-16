# Database Migrations

This directory contains SQL migration files for updating the AlBurhan Frames database schema.

## Migration Files

### update_database.sql
**Purpose**: Add multi-photo frame feature support

**Applied**: Initial multi-photo feature implementation

**Changes**:
- Adds `is_multi_photo` column to `frames` table
- Adds `slot_count` column to `frames` table
- Creates `frame_slots` table for storing photo slot positions
- Sets up foreign key relationships

**When to apply**:
- When upgrading from single-photo to multi-photo version
- Should be applied ONCE if upgrading existing installation
- Already included in fresh `database.sql` installations

**SQL Preview**:
```sql
ALTER TABLE `frames` 
ADD COLUMN `is_multi_photo` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN `slot_count` int(11) NOT NULL DEFAULT 1;

CREATE TABLE `frame_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frame_id` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `x_position` int(11) NOT NULL,
  `y_position` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `rotation` int(11) NOT NULL DEFAULT 0,
  ...
);
```

---

### add_short_url_field.sql
**Purpose**: Add short URL tracking support

**Applied**: URL shortener integration feature

**Changes**:
- Adds `short_url` column to `frames` table
- Creates index for quick lookups

**When to apply**:
- When adding URL shortener integration
- Should be applied ONCE if upgrading existing installation
- Already included in fresh `database.sql` installations

**SQL Preview**:
```sql
ALTER TABLE `frames` 
ADD COLUMN `short_url` varchar(255) DEFAULT NULL;

ALTER TABLE `frames`
ADD INDEX `idx_short_url` (`short_url`);
```

---

## How to Apply Migrations

### Method 1: phpMyAdmin (Recommended)
1. Open phpMyAdmin
2. Select `alburhan_frames` database
3. Click "SQL" tab
4. Paste migration file contents
5. Click "Go"
6. Verify success message

### Method 2: MySQL Command Line
```bash
mysql -u root -p alburhan_frames < migrations/update_database.sql
mysql -u root -p alburhan_frames < migrations/add_short_url_field.sql
```

### Method 3: PHP Script
```php
<?php
require_once 'config.php';

$sql = file_get_contents('migrations/update_database.sql');
$conn->multi_query($sql);

while ($conn->next_result()) {
    if ($result = $conn->store_result()) {
        $result->free();
    }
}

echo "Migration applied successfully!";
?>
```

---

## Migration Status

| File | Status | Fresh Install | Existing Install |
|------|--------|---------------|------------------|
| `update_database.sql` | ✅ Applied | Included in `database.sql` | Apply if upgrading |
| `add_short_url_field.sql` | ✅ Applied | Included in `database.sql` | Apply if upgrading |

---

## Checking if Migration is Needed

### Check for multi-photo feature:
```sql
SHOW COLUMNS FROM frames LIKE 'is_multi_photo';
```
If returns empty, apply `update_database.sql`

### Check for short URL feature:
```sql
SHOW COLUMNS FROM frames LIKE 'short_url';
```
If returns empty, apply `add_short_url_field.sql`

---

## Fresh Installation vs Migration

### Fresh Installation
- Use `database.sql` in project root
- Contains complete schema with all features
- No migrations needed

### Existing Installation
- Check which features are missing
- Apply only needed migrations
- Migrations are idempotent (safe to re-run with minor warnings)

---

## Creating New Migrations

When adding new features that require schema changes:

1. **Create new migration file**: `YYYY_MM_DD_feature_name.sql`
2. **Add ALTER/CREATE statements**: Include IF NOT EXISTS where possible
3. **Document changes**: Add header comments
4. **Test on copy of database**: Never test on production
5. **Add to this README**: Update migration list

### Migration Template:
```sql
-- Migration: Feature Name
-- Date: YYYY-MM-DD
-- Description: What this migration does

-- Check if already applied (optional):
-- SELECT 1 FROM information_schema.COLUMNS 
-- WHERE TABLE_SCHEMA='alburhan_frames' AND TABLE_NAME='table_name' AND COLUMN_NAME='column_name';

ALTER TABLE `table_name`
ADD COLUMN `new_column` VARCHAR(255) DEFAULT NULL;

-- Add indexes if needed
ALTER TABLE `table_name`
ADD INDEX `idx_new_column` (`new_column`);
```

---

## Rollback Migrations

⚠️ **These migrations do not include rollback scripts.**

If needed, manually create rollback:

### Rollback multi-photo feature:
```sql
DROP TABLE IF EXISTS `frame_slots`;
ALTER TABLE `frames` DROP COLUMN `slot_count`;
ALTER TABLE `frames` DROP COLUMN `is_multi_photo`;
```

### Rollback short URL feature:
```sql
ALTER TABLE `frames` DROP INDEX `idx_short_url`;
ALTER TABLE `frames` DROP COLUMN `short_url`;
```

---

## Production Deployment Checklist

- [ ] Backup database before applying migrations
- [ ] Test migration on development/staging first
- [ ] Review migration SQL for correctness
- [ ] Apply during low-traffic period
- [ ] Verify application works after migration
- [ ] Check for errors in PHP error log
- [ ] Keep migration files for documentation

---

**Last Updated**: February 2026  
**Part of**: AlBurhan Frames Project
