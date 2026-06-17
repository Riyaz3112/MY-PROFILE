# GitHub Deployment Guide

## Step-by-Step Instructions

### 1. Create a GitHub Account & Repository

1. Go to [github.com](https://github.com)
2. Sign up or log in
3. Click **New repository**
4. Name it: `billing` (or preferred name)
5. Add description: "LOOKSTYLO FASHION - Billing Management System"
6. Choose **Public** (required for GitHub Pages)
7. Click **Create repository**

### 2. Install Git

**Windows:**
- Download from [git-scm.com](https://git-scm.com/download/win)
- Install with default settings

**Mac:**
```bash
brew install git
```

**Linux:**
```bash
sudo apt install git
```

### 3. Configure Git

```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### 4. Upload Your Project

Navigate to your billing folder and run:

```bash
# Initialize git (if not already done)
git init

# Add all files
git add .

# Create first commit
git commit -m "Initial billing app commit"

# Rename branch to main
git branch -M main

# Add remote repository
git remote add origin https://github.com/YOUR_USERNAME/billing.git

# Push to GitHub
git push -u origin main
```

Replace `YOUR_USERNAME` with your actual GitHub username.

### 5. Enable GitHub Pages

1. Go to your GitHub repository
2. Click **Settings** (top right)
3. Scroll to **Pages** section (left sidebar)
4. Under "Source", select:
   - Branch: `main`
   - Folder: `/ (root)`
5. Click **Save**

GitHub will show you the URL where your app is hosted (usually in 1-2 minutes).

### 6. Access Your App

Your app will be available at:
```
https://YOUR_USERNAME.github.io/billing
```

Or with custom domain (optional):
```
https://yourdomain.com
```

## Updates & Version Control

### To make changes:

```bash
# Edit your files, then:
git add .
git commit -m "Describe your changes"
git push
```

Your changes will be live within seconds!

### View commit history:

```bash
git log
```

### Revert a change:

```bash
git revert COMMIT_HASH
git push
```

## Backup & Restore

### Export your data regularly:

1. Open your billing app
2. Go to **Settings** tab
3. Click **Download Backup**
4. Save the JSON file safely

### Restore data:

1. Go to **Settings** tab
2. Click **Restore Data**
3. Select your backup JSON file

## Performance Tips

- Data syncs to Google Sheets (optional, set in Settings)
- All data stored locally in browser
- No server required for basic functionality
- Works offline (changes sync when online)

## Troubleshooting

### App not loading?
- Clear browser cache (Ctrl+Shift+Delete)
- Hard refresh (Ctrl+F5)
- Check that JavaScript is enabled

### Data not saving?
- Check browser localStorage is enabled
- Use Chrome/Firefox for best compatibility
- Backup your data before major updates

### GitHub Pages not updating?
- Wait 1-2 minutes for deployment
- Check **Actions** tab for build status
- Hard refresh your browser cache

## Advanced: Custom Domain

1. Purchase a domain (Namecheap, GoDaddy, etc.)
2. In **Settings → Pages**, add custom domain
3. Update DNS records with GitHub's IP address
4. Wait for verification (5-10 minutes)

## Security Notes

⚠️ **Important:**
- Change default admin credentials immediately
- Don't commit sensitive data
- Use HTTPS (GitHub Pages provides this)
- Enable 2FA on your GitHub account
- Keep backups of your database

## Getting Help

- GitHub Docs: https://docs.github.com
- GitHub Pages: https://pages.github.com
- Issues: Open an issue on your repository

---

**Happy billing! 🚀**
