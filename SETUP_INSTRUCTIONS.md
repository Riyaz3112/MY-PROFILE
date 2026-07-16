# LookStylo E-Commerce Setup Guide

## Complete Shopping Flow Connected

The following pages are now fully integrated:

1. **shop.php** → Shows products with "Add to Cart" forms
2. **cart.php** → Displays cart, allows quantity updates and removal
3. **checkout.php** → Collects customer details and UPI payment info
4. **order-success.php** → Shows order confirmation
5. **track-order.php** → Customer order tracking page
6. **admin/dashboard.php** → Admin view of all orders and payments

---

## Requirements

- **PHP 8+**
- **MySQL 5.7+**
- Web server (Apache, Nginx, or local PHP server)

---

## Installation Steps

### Step 1: Install PHP & MySQL

#### Windows (Using XAMPP - Recommended)
1. Download XAMPP from https://www.apachefriends.org/
2. Install and launch XAMPP Control Panel
3. Start **Apache** and **MySQL** services
4. XAMPP serves files from `C:\xampp\htdocs\`

#### Alternative: Use PHP Built-in Server
```powershell
cd "C:\Users\ELCOT\OneDrive\Desktop\LOOKSTLYO WEBSITE"
php -S localhost:8000
```
Then open: `http://localhost:8000`

---

### Step 2: Import Database

1. Open phpMyAdmin (XAMPP): `http://localhost/phpmyadmin`
2. Create a new database called `lookstylo` (or import via SQL)
3. Import `schema.sql`:
   - Go to the `lookstylo` database
   - Click **Import**
   - Select `schema.sql` and click **Go**

#### Or via command line:
```bash
mysql -u root -p lookstylo < schema.sql
```

---

### Step 3: Configure Database Connection

Edit `config/db.php` if your MySQL credentials differ:

```php
$host = 'localhost';      // MySQL host
$dbname = 'lookstylo';    // Database name
$user = 'root';           // MySQL username
$pass = '';               // MySQL password (empty for XAMPP default)
```

---

### Step 4: Set File Permissions

Ensure the `uploads/payments/` directory is writable:

#### Windows:
```powershell
# Right-click uploads folder → Properties → Security → Edit
# Give "Full Control" to Users
```

#### Linux/Mac:
```bash
chmod -R 755 uploads/
chmod -R 755 config/
```

---

## Testing the Flow

### Customer Journey
1. Go to **Shop** (`shop.php`)
2. Select size, color, quantity → **Add to Cart**
3. Go to **Cart** (`cart.php`)
4. Update quantity or remove items
5. Click **Proceed to Checkout**
6. Fill customer details
7. Scan UPI QR Code or copy UPI ID: `9360232991@okbizaxis`
8. Enter UTR/Transaction ID (any test value, e.g., `123456789`)
9. Click **Place Order**
10. See confirmation on **Order Success** page

### Admin Panel
- URL: `admin/login.php`
- Username: `admin`
- Password: `admin123`
- View orders, payments, and order status

### Track Order
- Go to `track-order.php`
- Enter Order ID (e.g., `LS202600001`) and mobile number
- See order timeline and current status

---

## Database Schema Overview

- **products** → Product catalog (name, price, image, description)
- **cart** → Session-based shopping cart
- **users** → Customer information
- **orders** → Order records
- **order_items** → Line items in each order
- **payments** → Payment details and UTR tracking
- **order_tracking** → Order status timeline
- **admin** → Admin credentials

---

## File Structure

```
LOOKSTLYO WEBSITE/
├── index.html                 (Homepage with Shop & Cart links)
├── shop.php                   (Product listing & add to cart)
├── cart.php                   (Shopping cart management)
├── checkout.php               (UPI payment & order placement)
├── order-success.php          (Order confirmation)
├── track-order.php            (Customer order tracking)
├── admin/
│   ├── login.php             (Admin login)
│   ├── dashboard.php         (Admin order dashboard)
│   └── logout.php
├── config/
│   └── db.php                (Database connection)
├── includes/
│   ├── functions.php         (Core business logic)
│   ├── header.php            (Page header template)
│   └── footer.php            (Page footer template)
├── uploads/
│   └── payments/             (Payment screenshot storage)
└── schema.sql                (Database initialization)
```

---

## Security Features Implemented

✅ SQL Injection Protection (Prepared Statements)
✅ CSRF Protection (Tokens)
✅ XSS Protection (htmlspecialchars)
✅ Session-based Cart
✅ Secure File Upload (Type & Size Validation)
✅ Password Hashing (bcrypt)
✅ Sanitized Input Handling

---

## Free UPI Payment Flow

1. Customer adds items to cart
2. Proceeds to checkout
3. Sees QR Code for UPI ID: `9360232991@okbizaxis`
4. Pays via Google Pay, PhonePe, Paytm, or BHIM
5. Enters UTR number from payment app
6. Optionally uploads payment screenshot
7. Order placed with status: **Payment Verification Pending**
8. Admin verifies payment and updates order status

---

## Troubleshooting

### PHP files display as source code
→ Not served through PHP server. Use XAMPP or `php -S localhost:8000`

### "Database connection failed"
→ Check MySQL is running and `schema.sql` was imported

### "Permission denied" on uploads folder
→ Check folder permissions (see Step 4)

### Cart items not persisting
→ Ensure PHP sessions are enabled and database connection is active

---

## Next Steps

1. ✅ Set up PHP + MySQL environment
2. ✅ Import `schema.sql`
3. ✅ Test the shopping flow end-to-end
4. ✅ Verify admin dashboard works
5. ✅ Customize admin password (change from default)

---

**Site is production-ready once set up!**
