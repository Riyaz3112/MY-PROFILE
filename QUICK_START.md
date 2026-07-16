# Quick Start: Deploy to Netlify + Firebase

## 5-Minute Setup

### Step 1: Create Firebase Project (2 min)
1. Go to https://console.firebase.google.com/
2. Click **"Create Project"** → Name it `LookStylo`
3. Wait for creation...
4. Go to **Settings** → Copy your Firebase config

### Step 2: Update Your Code (1 min)
1. Open `firebase-init.js`
2. Replace `YOUR_API_KEY`, `YOUR_PROJECT_ID`, etc. with your actual values from Firebase

### Step 3: Deploy to Netlify (2 min)
**Option A: Using CLI**
```bash
npm install -g netlify-cli
netlify deploy --prod
```

**Option B: Using GitHub**
- Push code to GitHub
- Go to https://netlify.com
- Click **"Add new site"** → Connect GitHub
- Select your repository
- Done! Auto-deploys on every push

---

## Verify It Works

1. Open your Netlify site: `https://YOUR_SITE.netlify.app`
2. Click **"Shop"** → Products load ✅
3. Add item to cart → Cart count updates ✅
4. Go to **Cart** → Item shown ✅
5. Click **"Checkout"** → Form appears ✅
6. Scroll down → See UPI QR code ✅

---

## Live Site URLs

| Page | URL |
|------|-----|
| Home | `https://YOUR_SITE.netlify.app/` |
| Shop | `https://YOUR_SITE.netlify.app/shop.html` |
| Cart | `https://YOUR_SITE.netlify.app/cart.html` |
| Checkout | `https://YOUR_SITE.netlify.app/checkout.html` |
| Track Order | `https://YOUR_SITE.netlify.app/track-order.html` |
| Admin Login | `https://YOUR_SITE.netlify.app/admin/login.html` |

---

## Next Steps

1. **Add Products**: Use Firebase Console to add products
2. **Enable Payments**: Integrate Razorpay/UPI
3. **Custom Domain**: Point your domain to Netlify
4. **Analytics**: Set up Google Analytics
5. **Notifications**: Add email confirmations

---

## Admin Login

**Default Credentials:**
- Username: `admin`
- Password: `admin123`

⚠️ **CHANGE THIS BEFORE GOING LIVE!** Edit `netlify/functions/admin-login.js`

---

## Need Help?

- Check **NETLIFY_FIREBASE_SETUP.md** for detailed steps
- Check **NETLIFY_MIGRATION_GUIDE.md** for what changed
- Browser Console (F12) shows errors
- Netlify Dashboard shows function logs

---

## You're All Set! 🚀

Your e-commerce system is now ready to go live on Netlify + Firebase!
