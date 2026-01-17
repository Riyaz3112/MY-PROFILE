# Shop Order Webhook

This repository adds a client-side checkout flow to `index.html.html` and a minimal Node.js webhook to receive orders and send email notifications to the merchant and the customer.

## Files added
- `index.html.html` — updated to collect email, allow UPI payment, accept transaction ID, and POST order to the webhook.
- `server.js` — Express server that accepts `POST /order` and sends emails via SMTP (nodemailer).
- `package.json` — for the server dependencies.

## Setup (server)
1. Install Node.js (16+ recommended).
2. Open a terminal in `c:/Users/HOME/Desktop/SHOP WEBSITE` and install dependencies:

```bash
npm install
```

3. Configure environment variables for SMTP and merchant email. You can use an SMTP provider or Gmail (app password). Example (Windows PowerShell):

```powershell
$env:SMTP_HOST = 'smtp.example.com'
$env:SMTP_PORT = '587'
$env:SMTP_USER = 'your-smtp-user@example.com'
$env:SMTP_PASS = 'your-smtp-password'
$env:MERCHANT_EMAIL = 'your-merchant-email@example.com'
node server.js
```

Or create a small `.env` loader if you prefer.

4. Start the server:

```bash
npm start
```

Server will listen on `http://localhost:3000` by default.

## How it works
- Customer fills name, phone, email, address and clicks Pay.
- The site attempts to open Google Pay via Android intent (or shows UPI instructions + QR fallback).
- After payment, customer inputs the UPI transaction ID and clicks "Submit transaction & place order".
- The client POSTs the order to `http://localhost:3000/order` with order details and the txn id.
- The server logs the order and (if SMTP is configured) sends an email to the merchant and the customer's email.

## Production notes
- For real payment verification, integrate with a payment gateway or a bank/UPI reconciliation API — manual "I have paid" confirmation is not secure.
- Run the server from a secured environment and use HTTPS in production.
- Verify and sanitize inputs before processing.

If you want, I can:
- Add SMS notifications (Twilio) instead of/in addition to email.
- Add persistent order storage (SQLite) and an admin view for orders.
- Integrate official Google Pay web APIs if you prefer a card/UPI checkout flow.
