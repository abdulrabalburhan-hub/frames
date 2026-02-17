# Documentation

This directory contains detailed guides and setup instructions for the AlBurhan Frames application.

## Files

### INSTALLATION_PATHS.md
**Purpose**: Complete guide for flexible installation in any directory

**Contents**:
- Supported installation paths (root, subdirectory, custom)
- Configuration steps for different scenarios
- SITE_URL setup guide
- Troubleshooting installation issues
- Migration between directories
- Production deployment checklist

**Target Audience**: Developers, System Administrators, DevOps

**When to read**:
- Before installing the application
- When moving to a different directory
- Deploying to production server
- Troubleshooting path-related issues

---

### URL_SHORTENER_SETUP.md
**Purpose**: Comprehensive guide for URL shortener integration

**Contents**:
- Overview of external shortener workflow (albn.org)
- Step-by-step setup instructions
- URL format examples
- Admin panel features walkthrough
- User experience scenarios
- Technical details

**Target Audience**: Developers, System Administrators

**When to read**:
- Setting up URL shortening for the first time
- Understanding the shortener architecture
- Troubleshooting shortener-related issues
- Planning custom shortener integration

---

### SHORT_URL_GUIDE.md
**Purpose**: Quick reference for creating and managing short URLs

**Contents**:
- Quick overview of the shortener workflow
- Step-by-step process for creating short links
- Finding frame information
- Managing short URLs in admin panel
- URL type comparison table
- Pro tips for effective URL management

**Target Audience**: End Users, Content Managers, Administrators

**When to read**:
- First time creating a short URL
- Need quick reference for daily operations
- Training new team members
- Sharing frames with users

---

## Quick Reference

### URL Shortener Workflow
```
1. Upload Frame → Get full URL
2. Create short link at albn.org
3. Save short URL in dashboard (optional)
4. Share short link with users
```

### Example URLs

| Type | Example | Purpose |
|------|---------|---------|
| **Full Frame URL** | `https://alburhan.online/frame.php?id=frame_abc123` | Direct frame access |
| **Short URL** | `https://albn.org/seerat2025` | Easy sharing |
| **Gallery** | `https://alburhan.online/gallery.php` | Browse all frames |
| **Smart Route** | `https://alburhan.online/` | Auto-routing |

---

## Document Selection Guide

**Need to understand the system?**  
→ Start with **URL_SHORTENER_SETUP.md**

**Need to create a short link?**  
→ Jump to **SHORT_URL_GUIDE.md**

**Setting up for first time?**  
→ Read **URL_SHORTENER_SETUP.md** section 1-5

**Training someone?**  
→ Share **SHORT_URL_GUIDE.md**

**Technical deep dive?**  
→ Read **URL_SHORTENER_SETUP.md** section 6-8

---

## Additional Resources

### Main Project README
Located at: `../README.md`

**Contains**:
- Project overview and features
- Installation instructions
- Complete file structure
- Tech stack details
- Mobile optimization guide
- Troubleshooting section

### Testing Documentation
Located at: `../tests/README.md`

**Contains**:
- Test file descriptions
- Usage instructions
- Common scenarios
- Expected outputs

### Migration Documentation
Located at: `../migrations/README.md`

**Contains**:
- Database migration files
- How to apply migrations
- Migration status tracking
- Creating new migrations

---

## External Resources

### URL Shortener Service
- Website: **albn.org**
- Type: External URL shortening service
- Login: Required for creating short links
- Similar to: Bitly, TinyURL, Short.io

### Related Technologies
- **Bootstrap 5**: https://getbootstrap.com/docs/5.3
- **jQuery**: https://api.jquery.com
- **PHP GD Library**: https://www.php.net/manual/en/book.image.php
- **HTML5 Canvas**: https://developer.mozilla.org/en-US/docs/Web/API/Canvas_API

---

## Contributing to Documentation

When updating or adding documentation:

1. **Use Markdown**: All docs should be `.md` files
2. **Follow template**:
   ```markdown
   # Title
   Brief description
   
   ## Section 1
   Content...
   
   ## Section 2
   Content...
   
   ---
   **Last Updated**: [Date]
   ```
3. **Include examples**: Code snippets, screenshots, or step-by-step guides
4. **Keep it current**: Update dates when making changes
5. **Cross-reference**: Link to related documents
6. **Test instructions**: Verify all steps work as described

---

## Documentation Standards

### File Naming
- Use descriptive names: `FEATURE_NAME_GUIDE.md`
- All caps for main docs: `SHORTENER_SETUP.md`
- Lowercase for supporting docs: `troubleshooting.md`

### Formatting
- Use headers (`#`, `##`, `###`) for structure
- Use code blocks with language tags: ` ```php `
- Use tables for comparisons
- Use lists for steps or items
- Use blockquotes for important notes: `> Note: ...`

### Content
- Start with "Purpose" or "Overview"
- Include "When to use" or "When to read"
- Provide examples for complex concepts
- End with "Last Updated" date

---

**Last Updated**: February 2026  
**Part of**: AlBurhan Frames Project
