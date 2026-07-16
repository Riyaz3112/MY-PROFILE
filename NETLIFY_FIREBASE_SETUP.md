# Netlify + Firebase Setup Guide

## Overview

Your LookStylo e-commerce system has been converted to work with **Netlify** (frontend hosting) and **Firebase** (backend + database). This eliminates the need for PHP and MySQL servers.

### Key Benefits
✅ Static hosting on Netlify (fast CDN)  
✅ Serverless functions (Netlify Functions) for backend  
✅ Cloud database (Firebase Realtime Database)  
✅ Easy scaling and maintenance  
✅ Free tier available  

---

## Step 1: Create Firebase Project

### 1.1 Go to Firebase Console
- Visit [https://console.firebase.google.com/](https://console.firebase.google.com/)
- Sign in with your Google account (create one if needed)
- Click **"Create a project"**

### 1.2 Create Project
- **Project Name**: `LookStylo` (or your choice)
- Accept terms and continue
- Disable Google Analytics (optional)
- Click **"Create project"**

Wait for project to be created (2-3 minutes)...

### 1.3 Get Firebase Config

After project is created:
1. Click the gear icon (⚙️) → **Project Settings**
2. Scroll down to **"Your apps"** section
3. Click **"Firebase SDK snippet"** or **Web** icon
4. Copy the Firebase config object

Should look like:
```javascript
const firebaseConfig = {
  apiKey: "AIzaSy...",
  authDomain: "lookstylo-xxxxx.firebaseapp.com",
  projectId: "lookstylo-xxxxx",
  storageBucket: "lookstylo-xxxxx.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:web:abc..."
};
```

### 1.4 Enable Realtime Database

1. In Firebase Console, go to **Realtime Database**
2. Click **"Create Database"**
3. Choose region: **Asia-Southeast1 (Singapore)** (closest to India)
4. Start in **Test mode** (we'll secure it later)
5. Click **"Enable"**

### 1.5 Set Up Database Rules

1. Go to **Realtime Database** → **Rules** tab
2. Replace with this secure rule:
```json
{
  "rules": {
    ".read": false,
    ".write": false,
    "products": {
      ".read": true
    },
    "orders": {
      ".write": "auth != null",
      ".read": "auth != null"
    }
  }
}
```
3. Click **"Publish"**

---

## Step 2: Update Firebase Config in Your Project

### 2.1 Update `firebase-init.js`

Open `firebase-init.js` and replace:
```javascript
const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "YOUR_PROJECT.firebaseapp.com",
  projectId: "YOUR_PROJECT_ID",
  storageBucket: "YOUR_PROJECT.appspot.com",
  messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
  appId: "YOUR_APP_ID"
};
```

With **your actual config from Firebase Console** (from Step 1.3)

### 2.2 Verify File Structure

Your project should have:
```
LOOKSTLYO WEBSITE/
├── index.html
├── shop.html
├── cart.html
├── checkout.html
├── order-success.html
├── track-order.html
├── firebase-init.js
├── netlify.toml
├── admin/
│   ├── login.html
│   └── dashboard.html
├── netlify/
│   └── functions/
│       ├── get-products.js
│       ├── create-order.js
│       ├── track-order.js
│       └── admin-login.js
├── images (all your product images)
└── [other static files]
```

---

## Step 3: Set Up Netlify

### 3.1 Create Netlify Account
- Visit [https://netlify.com](https://netlify.com)
- Sign up with GitHub, GitLab, or email
- Verify email

### 3.2 Connect GitHub Repository

If you have the code on GitHub:
1. Click **"Add new site"** → **"Import an existing project"**
2. Choose your Git provider
3. Select your repository
4. Netlify will auto-detect `netlify.toml`
5. Click **"Deploy"**

### 3.3 Deploy Without Git

If you don't have GitHub:
1. Install Netlify CLI: `npm install -g netlify-cli`
2. In your project folder, run: `netlify init`
3. Choose "Create & configure a new site"
4. Answer prompts
5. Deploy: `netlify deploy --prod`

---

## Step 4: Configure Environment Variables

### 4.1 In Netlify Dashboard

1. Go to **Site settings** → **Build & deploy** → **Environment**
2. Add environment variables for Netlify Functions:

```
FIREBASE_API_KEY = YOUR_API_KEY
FIREBASE_AUTH_DOMAIN = YOUR_PROJECT.firebaseapp.com
FIREBASE_PROJECT_ID = YOUR_PROJECT_ID
FIREBASE_STORAGE_BUCKET = YOUR_PROJECT.appspot.com
FIREBASE_MESSAGING_SENDER_ID = YOUR_MESSAGING_SENDER_ID
FIREBASE_APP_ID = YOUR_APP_ID
```

---

## Step 5: Test Locally

### 5.1 Install Dependencies
```bash
npm install
```

### 5.2 Install Netlify CLI
```bash
npm install -g netlify-cli
```

### 5.3 Run Local Dev Server
```bash
netlify dev
```

This starts:
- Frontend on `http://localhost:8888`
- Netlify Functions on `http://localhost:8888/.netlify/functions/`

### 5.4 Test Features

1. **Shop Page**: Go to `http://localhost:8888/shop.html`
2. **Add to Cart**: Add products
3. **Cart**: View cart
4. **Checkout**: Complete order
5. **Track Order**: Track by Order ID and mobile

---

## Step 6: Import Sample Data to Firebase

### 6.1 Add Products to Firebase

In Firebase Console:
1. Go to **Realtime Database**
2. Click **"+"** to add data
3. Create structure:

```
products/
├── 1/
│   ├── id: 1
│   ├── name: "Premium Oversized T-Shirt"
│   ├── price: 499
│   ├── image: "IMG101.jpeg"
│   └── description: "..."
├── 2/
│   ├── id: 2
│   ├── name: "Baggy T-Shirt Black"
│   ├── price: 599
│   └── ...
```

Or use the REST API:
```bash
curl -X PUT https://YOUR_PROJECT.firebaseio.com/products.json -d '{
  "1": {"id": 1, "name": "Premium Oversized T-Shirt", "price": 499, "image": "IMG101.jpeg"},
  "2": {"id": 2, "name": "Baggy T-Shirt Black", "price": 599, "image": "IMG102.jpeg"}
}'
```

---

## Step 7: Deploy to Production

### 7.1 Via Netlify CLI
```bash
netlify deploy --prod
```

### 7.2 Via Git Push
If using GitHub:
- Push code to `main` branch
- Netlify auto-deploys

### 7.3 Verify Deployment

1. Your site is now live at: `https://YOUR_SITE_NAME.netlify.app`
2. Test all features
3. Check console for errors (F12)

---

## Troubleshooting

### Firebase Config Not Loading
- Check `firebase-init.js` has correct values
- Open browser console (F12) for errors
- Verify Firebase project still exists

### Orders Not Creating
- Check Netlify Functions are deployed: `https://YOUR_SITE.netlify.app/.netlify/functions/create-order`
- Verify Firebase Realtime Database rules allow writes

### Images Not Showing
- Ensure image files are in root folder
- Check file names match exactly (case-sensitive)

### Admin Login Not Working
- Default credentials: `admin` / `admin123`
- Change password: Update `admin-login.js` Netlify Function

---

## Production Checklist

- [ ] Firebase project created and configured
- [ ] Netlify site deployed
- [ ] Products added to Firebase
- [ ] Shop page loads products
- [ ] Add to cart works
- [ ] Checkout processes orders
- [ ] Admin dashboard accessible
- [ ] SSL certificate enabled (Netlify auto)
- [ ] Custom domain configured (optional)
- [ ] Change admin password
- [ ] Test complete order flow

---

## Next Steps

1. **Custom Domain**: Go to **Site settings** → **Domain management** → Add custom domain
2. **SSL**: Already enabled (Netlify auto)
3. **Analytics**: Enable Google Analytics in Firebase
4. **Email Notifications**: Set up Firebase Cloud Functions for email on order
5. **Payment Gateway**: Integrate Razorpay/PayU when ready

---

## Support Resources

- [Netlify Docs](https://docs.netlify.com)
- [Firebase Docs](https://firebase.google.com/docs)
- [JavaScript ES6 Guide](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference)

---

**Questions?** Contact your developer or check the error logs in Netlify Dashboard → Functions.
