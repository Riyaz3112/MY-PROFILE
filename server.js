const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const app = express();
app.use(cors());
app.use(express.json());

// Configuration via environment variables
const PORT = process.env.PORT || 3000;
const SMTP_HOST = process.env.SMTP_HOST || '';
const SMTP_PORT = process.env.SMTP_PORT || 587;
const SMTP_USER = process.env.SMTP_USER || '';
const SMTP_PASS = process.env.SMTP_PASS || '';
const MERCHANT_EMAIL = process.env.MERCHANT_EMAIL || '';

if(!SMTP_HOST || !SMTP_USER || !SMTP_PASS || !MERCHANT_EMAIL){
  console.warn('Warning: SMTP_HOST, SMTP_USER, SMTP_PASS and MERCHANT_EMAIL should be set as environment variables for sending emails. The server will still accept orders but cannot send emails without these.');
}

const transporter = nodemailer.createTransport({
  host: SMTP_HOST,
  port: SMTP_PORT,
  secure: SMTP_PORT==465, // true for 465, false for other ports
  auth: {
    user: SMTP_USER,
    pass: SMTP_PASS
  }
});

app.post('/order', async (req, res) => {
  try{
    const payload = req.body;
    // basic validation
    if(!payload || !payload.txnId || !payload.customer || !payload.customer.email){
      return res.status(400).json({ok:false,err:'Missing fields (txnId or customer email)'});
    }

    // prepare email bodies
    const merchantSubject = `New order received — ${payload.customer.name}`;
    const customerSubject = `Order confirmation — Thank you ${payload.customer.name}`;

    const itemsHtml = (payload.items||[]).map(i=>`<li>${i.name} x ${i.qty} — ₹${(i.price*i.qty).toFixed(2)}</li>`).join('');
    const subtotal = payload.subtotal || '0.00';

    const merchantHtml = `<p>New order received:</p>
      <p><strong>Customer:</strong> ${payload.customer.name}<br>
      <strong>Phone:</strong> ${payload.customer.phone}<br>
      <strong>Email:</strong> ${payload.customer.email}<br>
      <strong>Address:</strong> ${payload.customer.address}</p>
      <p><strong>Txn ID:</strong> ${payload.txnId}</p>
      <p><strong>Items:</strong></p><ul>${itemsHtml}</ul>
      <p><strong>Subtotal:</strong> ₹${subtotal}</p>
      <p>Paid to UPI: ${payload.merchantUpi}</p>
      <p>Received at: ${payload.ts}</p>`;

    const customerHtml = `<p>Hi ${payload.customer.name},</p>
      <p>Thank you for your order. We received your payment (Txn ID: ${payload.txnId}).</p>
      <p><strong>Order details:</strong></p><ul>${itemsHtml}</ul>
      <p><strong>Subtotal:</strong> ₹${subtotal}</p>
      <p>We will process your order and notify you when it ships.</p>`;

    // send emails (if SMTP configured)
    if(SMTP_HOST && SMTP_USER && SMTP_PASS && MERCHANT_EMAIL){
      // to merchant
      await transporter.sendMail({from: SMTP_USER, to: MERCHANT_EMAIL, subject: merchantSubject, html: merchantHtml});
      // to customer
      await transporter.sendMail({from: SMTP_USER, to: payload.customer.email, subject: customerSubject, html: customerHtml});
    }

    // log order server-side
    console.log('Order received:', JSON.stringify(payload, null, 2));

    return res.json({ok:true,message:'Order received'});
  }catch(err){
    console.error('Order handler error', err);
    return res.status(500).json({ok:false,err:err.message});
  }
});

app.listen(PORT, ()=>{
  console.log(`Order webhook listening on http://localhost:${PORT}`);
});
