# GoDaddy Deployment Guide for LookStylo E-Commerce

## Complete Setup for GoDaddy Hosting

This guide covers deploying the LookStylo website with the new shopping cart, checkout, and admin dashboard to GoDaddy hosting.

---

## Step 1: Choose GoDaddy Hosting Plan

### Recommended Plans:
- **Economy Hosting** ($2.99/month) - Good for small sites
- **Deluxe Hosting** ($5.99/month) - Recommended for e-commerce (multiple databases, more storage)
- **Ultimate Hosting** ($9.99/month) - Best for high traffic

**Requirements:**
- ✅ PHP 8.0+ support
- ✅ MySQL 5.7+ (or MariaDB)
- ✅ cPanel access
- ✅ File Manager or FTP access

### Purchase at: https://www.godaddy.com/hosting/web-hosting

---

## Step 2: Set Up Domain & SSL

1. Go to **GoDaddy Dashboard**
2. Click **Manage** next to your hosting plan
3. Under **Manage Your Websites**, select your domain
4. Ensure **SSL Certificate** is active (usually free with hosting)
5. Set domain to point to your hosting

---

## Step 3: Access GoDaddy cPanel

### Method 1: Direct cPanel Access
1. Go to https://cpanel.godaddy.com
2. Log in with your GoDaddy account
3. Click **Manage** for your hosting
4. Click **cPanel** button

### Method 2: Via GoDaddy Dashboard
1. Login to GoDaddy
2. Click **Hosting** → Select your plan
3. Click **Manage** → **Manage Websites** → **cPanel**

---

## Step 4: Create MySQL Database

### In cPanel:
1. Go to **Databases** → **MySQL® Databases**
2. Under **Create New Database:**
   - Database name: `lookstylo` (GoDaddy may prefix with your account name, e.g., `username_lookstylo`)
   - Click **Create Database**

3. Go to **MySQL® Users**
   - Username: `lookstylo_user` (or similar)
   - Password: Create a strong password
   - Click **Create User**

4. Go to **Add User to Database**
   - User: Select `lookstylo_user`
   - Database: Select `lookstylo`
   - Click **Add**
   - Check **ALL PRIVILEGES** and click **Make Changes**

**Save these credentials:**
```
Host: localhost (or your GoDaddy MySQL host)
Database: username_lookstylo
User: username_lookstylo_user
Password: [Your strong password]
```

---

## Step 5: Import Database Schema

### Option A: Using phpMyAdmin (Easiest)

1. In cPanel, go to **Databases** → **phpMyAdmin**
2. Select your database (`username_lookstylo`) from left panel
3. Click **Import** tab
4. Click **Choose File** and select `schema.sql`
5. Click **Go**
6. Wait for success message

### Option B: Via SSH (Advanced)
```bash
# Connect via SSH
ssh username@your-godaddy-domain.com

# Navigate to your domain
cd public_html

# Import the database
mysql -u username_lookstylo_user -p username_lookstylo < schema.sql
# Enter password when prompted
```

---

## Step 6: Upload Files to GoDaddy

### Option A: Using File Manager (Web-based)

1. In cPanel, click **File Manager**
2. Open **public_html** folder
3. Upload all files EXCEPT:
   - `.git` folder
   - `.gitignore` file
   - `SETUP_INSTRUCTIONS.md` (optional)
   - `HOSTING_GUIDE.html` (if exists)

**File upload order:**
- Upload all `.php` files first
- Upload all `.html` files
- Upload `config/` folder with `db.php`
- Upload `includes/` folder with functions
- Upload `admin/` folder
- Upload `uploads/` folder (create if needed)
- Upload `schema.sql`
- Upload all images (`.jpeg`, `.png`, etc.)

### Option B: Using FTP (Faster for large uploads)

1. In cPanel, go to **FTP Accounts**
2. Create FTP account:
   - FTP User: `lookstylo`
   - Password: Strong password
   - Directory: `public_html`
   - Click **Create FTP Account**

3. Use FTP client (FileZilla, WinSCP, etc.):
   ```
   Host: ftp.your-godaddy-domain.com
   Username: lookstylo@your-godaddy-domain.com
   Password: [FTP password]
   Port: 21
   ```

4. Upload files to `public_html/`

---

## Step 7: Update Database Connection

Edit `config/db.php` to use GoDaddy credentials:

```php
<?php
session_start();

function getDbConnection(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // GoDaddy Database Credentials
    $host = 'localhost';  // Usually localhost for GoDaddy
    $dbname = 'username_lookstylo';  // Replace with your actual database name
    $user = 'username_lookstylo_user';  // Replace with your MySQL user
    $pass = 'your_strong_password';  // Replace with your password

    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        throw new RuntimeException('Database connection failed: ' . $e->getMessage());
    }

    return $pdo;
}
?>
```

**Upload the updated `config/db.php` back to GoDaddy.**

---

## Step 8: Set Permissions

In cPanel **File Manager**:

1. Right-click `uploads/` folder → **Permissions**
   - Set to `755` (or `775` if needed)
   
2. Right-click `uploads/payments/` → **Permissions**
   - Set to `755` or `775`

3. Right-click `config/` folder → **Permissions**
   - Set to `755`

```bash
# Via SSH:
chmod -R 755 uploads/
chmod -R 755 config/
chmod -R 755 includes/
```

---

## Step 9: Verify Installation

### Test the Sites:
1. **Homepage:** `https://your-domain.com/`
2. **Shop:** `https://your-domain.com/shop.php`
3. **Cart:** `https://your-domain.com/cart.php`
4. **Admin Login:** `https://your-domain.com/admin/login.php`
   - Username: `admin`
   - Password: `admin123` (change this immediately!)

---

## Step 10: Security - Change Admin Password

### Update Admin Password:

1. Connect via SSH or use GoDaddy **Terminal** in cPanel
2. Run this command:
   ```bash
   cd public_html
   php -r "echo password_hash('your_new_secure_password', PASSWORD_BCRYPT);"
   ```
3. Copy the output hash
4. Go to phpMyAdmin → `admin` table
5. Find the `admin` user and update the `password` field with the new hash

---

## Step 11: Enable HTTPS (SSL)

GoDaddy usually provides free SSL, but ensure it's active:

1. In cPanel, go to **SSL/TLS Status**
2. Find your domain and click **Install** if not active
3. Go to cPanel → **AutoSSL**
4. Verify SSL is installed

Your site will now be `https://your-domain.com/`

---

## Testing Checklist

- [ ] Homepage loads at `https://your-domain.com/`
- [ ] Shop page displays products
- [ ] Add to cart works
- [ ] Cart page shows items
- [ ] Checkout form accepts data
- [ ] UPI QR code displays
- [ ] Admin login works
- [ ] Admin dashboard shows orders
- [ ] Track order page functions
- [ ] Images load properly
- [ ] No broken links

---

## Troubleshooting

### "Database connection failed"
- Check database name, user, and password in `config/db.php`
- Ensure schema.sql was imported successfully
- Verify database user has correct privileges

### "Permission denied" on uploads
- Check folder permissions (755 or 775)
- Ensure PHP can write to `uploads/` directory

### "500 Internal Server Error"
- Check PHP error logs in cPanel → **Metrics** → **Errors**
- Verify PHP version is 8.0+
- Check `config/db.php` syntax

### "SSL Certificate issues"
- Wait 24-48 hours for SSL to propagate
- Or manually install via AutoSSL in cPanel

### "Images not loading"
- Verify image files were uploaded to correct path
- Check image file permissions (644)
- Verify correct file paths in database

---

## GoDaddy-Specific Notes

### PHP Version
- Default is usually PHP 8.0+
- To change: cPanel → **Select PHP Version**
- Ensure extensions enabled: PDO, PDO MySQL, GD (for images)

### Database Limits
- Economy: 1 database
- Deluxe+: Multiple databases

### Storage & Bandwidth
- Check your plan limits
- Monitor via cPanel **Bandwidth** tool

### Backups
- GoDaddy provides automatic backups
- Download backups: cPanel → **Backup**

---

## Performance Optimization (Optional)

1. **Enable Caching:** cPanel → **Cache Manager**
2. **Optimize Images:** Use tool like TinyPNG before upload
3. **Enable Gzip:** Usually auto-enabled
4. **CDN:** GoDaddy offers MaxCDN integration

---

## Regular Maintenance

### Weekly:
- Check admin dashboard for new orders
- Verify payments received
- Update order statuses

### Monthly:
- Review sales reports
- Check for security updates
- Download backup

### Quarterly:
- Review customer data
- Optimize database (if needed)
- Update admin password

---

## Support Resources

- **GoDaddy Help:** https://www.godaddy.com/help
- **cPanel Tutorials:** https://documentation.cpanel.net/
- **PHP Documentation:** https://www.php.net/docs.php
- **MySQL Reference:** https://dev.mysql.com/doc/

---

## Go Live Checklist

✅ Domain registered and pointing to GoDaddy  
✅ SSL certificate installed and active  
✅ Database created with schema imported  
✅ All files uploaded to `public_html/`  
✅ Database credentials updated in `config/db.php`  
✅ Permissions set correctly (755/775)  
✅ Shop, cart, checkout pages tested  
✅ Admin login works (password changed)  
✅ UPI payment flow verified  
✅ Order tracking functional  
✅ Homepage links to shop/cart pages  

---

## After Going Live

1. **Promote:** Share shop link on social media
2. **Monitor:** Check for orders and payment verifications
3. **Support:** Have WhatsApp ready for customer queries
4. **Updates:** Keep PHP and WordPress (if used) updated
5. **Security:** Regular backups and monitoring

**Your LookStylo e-commerce site is now LIVE! 🎉**
