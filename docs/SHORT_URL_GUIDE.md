# AlBurhan Frames - Quick Start Guide

## Working with albn.org URL Shortener

### 🎯 Quick Overview
You have an external URL shortener service (**albn.org**) where you create short links that redirect to your frame URLs. This is similar to how Bitly or TinyURL works.

### 📋 Step-by-Step Process:

#### 1. **Upload a Frame**
   - Go to: Admin Dashboard → Click "Upload New Frame"
   - Upload your frame image (PNG/JPG)
   - Frame is now available!

#### 2. **Get the Full URL**
   - On the dashboard, find your frame card
   - Look for "Full URL (for albn.org)" section
   - Click the <i class="bi bi-clipboard"></i> **Copy** button
   - Example URL: `https://alburhan.online/frame.php?id=frame_699206c382ea65.66089756`

#### 3. **Create Short Link at albn.org**
   - Go to **albn.org** (your URL shortener)
   - Paste the full URL you copied
   - Choose a custom short code: `seeratframe72` (or auto-generate)
   - Create the short link
   - You'll get: `https://albn.org/seeratframe72`

#### 4. **Save the Short URL (Optional)**
   - Back in dashboard, find the same frame card
   - Look for "Short URL (optional)" field
   - Paste: `albn.org/seeratframe72` or `https://albn.org/seeratframe72`
   - It auto-saves (border turns green)

#### 5. **Share with Users!**
   - Share: `https://albn.org/seeratframe72`
   - Users click it → albn.org redirects → Your frame editor opens!
   - Users upload photos, adjust, and download 🎉

### 🔍 Finding Frame Information

Looking at your example:
- **Short URL**: `https://albn.org/seeratframe72`
- **Full URL**: `https://alburhan.online/frame.php?id=frame_699206c382ea65.66089756`
- **Frame ID**: `frame_699206c382ea65.66089756`

To find which frame this is:
1. Go to Admin Dashboard
2. Look at each frame's "Full URL" field
3. Check if the frame_id matches: `frame_699206c382ea65.66089756`
4. Or go to: Admin → "Short URLs" button → See all frames with their full URLs

### 📊 Managing Short URLs

**View All Frames & URLs:**
- Dashboard → Click "Short URLs" button
- See a table with:
  - Frame names
  - Full URLs (ready to copy)
  - Your created short URLs
  - Easy copy buttons

**Track Your Short Links:**
- The "Short URL" field in dashboard stores your created links
- This is just for your reference
- Helps you remember which short link goes to which frame

### 🎨 Different URL Options:

| Type | URL | Use Case |
|------|-----|----------|
| **Specific Frame** | `frame.php?id=FRAME_ID` | Direct link to one frame |
| **Gallery** | `gallery.php` | Show all frames, user chooses |
| **Smart Routing** | `/` (homepage) | Auto: 1 frame→direct, multiple→gallery |

### 💡 Pro Tips:

✅ **Use meaningful short codes:**
   - `albn.org/wedding2025` ✓
   - `albn.org/birthday` ✓
   - `albn.org/abc123` ✗

✅ **Test your links:**
   - Always click the short URL before sharing
   - Make sure it goes to the right frame

✅ **Keep them organized:**
   - Save short URLs in the dashboard
   - Use the "Short URLs" page to see everything

### 🔧 Technical Details:

**What happens when user clicks short link:**
```
User clicks: https://albn.org/seeratframe72
     ↓
albn.org redirects to: https://alburhan.online/frame.php?id=frame_699206c382ea65.66089756
     ↓
Frame editor loads with that specific frame
     ↓
User uploads photo, edits, downloads!
```

**albn.org handles all the redirection** - you don't need to configure anything on your server for the shortening to work. You just:
1. Get full URLs from your admin panel
2. Create short links at albn.org
3. Share the short links!

### ❓ FAQs:

**Q: Do I need to update DNS records?**
A: No! albn.org handles the redirection. You just create links there.

**Q: Can I see which short URL goes to which frame?**
A: Yes! Admin → "Short URLs" button shows all frames with their URLs.

**Q: What if I want to change where a short link points?**
A: Update it at albn.org. Change the destination URL there.

**Q: Can users see all frames?**
A: Yes, create a short link to `gallery.php` for that.

**Q: Do short URLs expire?**
A: Depends on albn.org's policy. Check their service terms.

---

**Need help?** See [URL_SHORTENER_SETUP.md](URL_SHORTENER_SETUP.md) for detailed documentation.
