# Test Files

This directory contains diagnostic and testing utilities for the AlBurhan Frames application.

## ⚠️ SECURITY WARNING

**These test files expose sensitive configuration and database information.**

### Protection Status
- ✅ **Web access blocked** - A `.htaccess` file in this directory denies all HTTP access
- ⚠️ **Local access only** - Access these files only from command line or local file system

### Production Deployment
**Before deploying to production:**
1. Delete the entire `tests/` directory, OR
2. Verify the `.htaccess` file is present and working
3. Test that accessing `https://yourdomain.com/tests/test_db.php` returns 403 Forbidden

**What these files expose:**
- Database credentials (host, name, username)
- Admin usernames and password hashes
- Directory structure and permissions
- PHP configuration details
- Database schema information

**Never commit sensitive test results** or leave test files accessible in production!

---

## Files

### test_db.php
**Purpose**: Database connection testing and admin user verification

**Usage**:
```
http://localhost/frames/tests/test_db.php
```

**What it checks**:
- Database connection status
- Config file loading
- Admin users table existence
- Password hash verification
- Provides fix command if password doesn't match

**When to use**:
- After fresh installation
- When admin login fails
- To verify database setup
- To check password hash

---

### test-upload.php
**Purpose**: Upload functionality diagnostics

**Usage**:
```
http://localhost/frames/tests/test-upload.php
```

**What it checks**:
- Admin login status
- Directory existence (`uploads/frames/`, `uploads/photos/`, `uploads/frames/thumbs/`)
- Directory write permissions
- GD library availability
- PNG and JPEG support
- PHP upload limits (max filesize, post size, memory limit)
- Execution time limits

**When to use**:
- Before uploading frames
- When upload fails with errors
- To verify GD extension is enabled
- To check directory permissions
- To see PHP configuration limits

---

## Common Test Scenarios

### Scenario 1: Fresh Installation
1. Run `test_db.php` - Verify database connection
2. Check admin user exists
3. Test login with default credentials
4. Run `test-upload.php` - Verify upload environment

### Scenario 2: Upload Not Working
1. Run `test-upload.php`
2. Check GD library status (should be "✓ GD library loaded")
3. Verify all directories are writable
4. Check PHP limits are sufficient (at least 10M upload)

### Scenario 3: Admin Login Fails
1. Run `test_db.php`
2. Check password verification section
3. If password doesn't match, copy the SQL command provided
4. Run in phpMyAdmin to fix password hash

---

## Security Note

⚠️ **Remove or restrict access to test files in production!**

Add to `.htaccess` in tests folder:
```apache
Order deny,allow
Deny from all
Allow from 127.0.0.1
```

Or delete the entire `tests/` directory after deployment.

---

## Expected Output Examples

### test_db.php (Success)
```
✓ Config loaded
Database: alburhan_frames
Host: localhost
User: root

✓ Database connected successfully!

✓ admin_users table exists
Admin users found: 1

Username: admin
Password hash: $2y$10$abcdef...
✓ Password 'Admin@123' is CORRECT for this user
```

### test-upload.php (Success)
```
✓ Logged in as: admin
✓ Frame uploads directory exists
  ✓ Writable
✓ Thumbnails directory exists
  ✓ Writable
✓ Photos directory exists
  ✓ Writable
✓ GD library loaded
  - PNG Support: Yes
  - JPEG Support: Yes

PHP Upload Limits:
upload_max_filesize: 10M
post_max_size: 10M
memory_limit: 128M
```

---

**Last Updated**: February 2026  
**Part of**: AlBurhan Frames Project
