# Meta WhatsApp Cloud API Setup Guide

## Step 1: Create Meta Business Account
1. Go to [business.facebook.com](https://business.facebook.com)
2. Click "Create Account"
3. Fill in business details (LookStylo Clothing)
4. Verify email

## Step 2: Create WhatsApp Business Account
1. In Business Manager, go to **All Tools** → **WhatsApp Manager**
2. Click **"Start"**
3. Choose **"Create new WhatsApp Business Account"**
4. Enter:
   - Business name: **LookStylo Clothing**
   - Phone number: **+91 86808 57511** (Your business number)
5. Click **Create**

## Step 3: Verify Phone Number
1. WhatsApp will send verification code to your number
2. Verify using SMS or call
3. Once verified, get your **Phone Number ID** (appears in WhatsApp Manager)

## Step 4: Get API Credentials
1. Go to [developers.facebook.com](https://developers.facebook.com)
2. **Create an App** or use existing one
3. Add **WhatsApp** product to your app
4. Go to **Settings** → **Basic**:
   - Copy **App ID**
   - Copy **App Secret**
5. Generate **User Access Token**:
   - Go to **Tools** → **Access Token Generator**
   - Select your app
   - Generate token with `whatsapp_business_messaging` scope
   - Copy token (valid for 60 days)

## Step 5: Update Configuration
Add these to your server environment variables or `.env` file:

```env
META_BUSINESS_ACCOUNT_ID=your_business_account_id
META_PHONE_NUMBER_ID=your_phone_number_id
META_ACCESS_TOKEN=your_access_token
META_WHATSAPP_API_VERSION=v18.0
```

Or create `.env` file in root directory:
```
META_BUSINESS_ACCOUNT_ID=123456789
META_PHONE_NUMBER_ID=987654321
META_ACCESS_TOKEN=EAABsZCXXXXXXXXXXX
META_WHATSAPP_API_VERSION=v18.0
```

## Step 6: Test API Connection
Run this PHP script to test:

```php
<?php
require_once __DIR__ . '/includes/functions.php';

$result = testWhatsAppConnection();
if ($result) {
    echo "✅ WhatsApp API connected successfully!";
} else {
    echo "❌ Connection failed. Check credentials.";
}
?>
```

## Step 7: Send Test Message
Use admin dashboard or test manually:

```php
$phone = '918680857511';
$message = 'Test message from LookStylo!';
sendWhatsAppMessage($phone, $message);
```

## API Rate Limits (Free Tier)
- **1,000 messages per day** to numbers without templates
- Use templates for unlimited messages
- Recommended for order notifications

## Troubleshooting

| Error | Solution |
|-------|----------|
| 401 Unauthorized | Check ACCESS_TOKEN validity (60-day expiration) |
| 400 Bad Request | Verify PHONE_NUMBER_ID format |
| 403 Forbidden | Ensure WhatsApp API access in app settings |
| Invalid number | Phone must be in E.164 format: +91XXXXXXXXXX |

## More Resources
- [Meta WhatsApp Cloud API Docs](https://developers.facebook.com/docs/whatsapp/cloud-api)
- [Verify Phone Number](https://developers.facebook.com/docs/whatsapp/on-premises/phone-number-migration)
- [Message Templates](https://developers.facebook.com/docs/whatsapp/message-templates)

---

**Once configured, order notifications will automatically send via WhatsApp!**
