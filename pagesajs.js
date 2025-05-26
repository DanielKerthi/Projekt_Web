import express from 'express';
import fetch from 'node-fetch';
import 'dotenv/config';
import cors from 'cors';
import path from 'path';

const app = express();

app.use(cors());

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files (.html, style.css, script.js) from project root
app.use(express.static(process.cwd()));

// Suppress favicon requests
app.get('/favicon.ico', (req, res) => res.sendStatus(204));

// ── CONFIG ─────────────────────────────────────────────────────────────────────
const port          = process.env.PORT          || 3000;
const environment   = process.env.ENVIRONMENT   || 'sandbox';
const client_id     = process.env.CLIENT_ID;
const client_secret = process.env.CLIENT_SECRET;

const endpoint_url = environment === 'sandbox'
  ? 'https://api-m.sandbox.paypal.com'
  : 'https://api-m.paypal.com';

// ── ROUTES ─────────────────────────────────────────────────────────────────────
// Create PayPal order
app.post('/create_order', async (req, res) => {
  try {
    const access_token = await get_access_token();
    const orderData = {
      intent: req.body.intent.toUpperCase(),
      purchase_units: [{
        amount: {
          currency_code: 'USD',
          value: '100.00'
        }
      }]
    };

    const response = await fetch(`${endpoint_url}/v2/checkout/orders`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${access_token}`
      },
      body: JSON.stringify(orderData)
    });

    const data = await response.json();
    res.json(data);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: err.message });
  }
});

// Capture/complete PayPal order
app.post('/complete_order', async (req, res) => {
  try {
    const access_token = await get_access_token();
    const response = await fetch(
      `${endpoint_url}/v2/checkout/orders/${req.body.order_id}/${req.body.intent}`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${access_token}`
        }
      }
    );

    const data = await response.json();
    res.json(data);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: err.message });
  }
});

// ── HELPERS ────────────────────────────────────────────────────────────────────
/**
 * Retrieve OAuth2 access token from PayPal
 * @returns {Promise<string>} access_token
 */
function get_access_token() {
  const auth = `${client_id}:${client_secret}`;
  const body = new URLSearchParams({ grant_type: 'client_credentials' }).toString();

  return fetch(`${endpoint_url}/v1/oauth2/token`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Authorization': `Basic ${Buffer.from(auth).toString('base64')}`
    },
    body
  })
  .then(res => res.json())
  .then(json => json.access_token);
}

// ── START SERVER ───────────────────────────────────────────────────────────────
app.listen(port, () => {
  console.log(`Server listening at http://localhost:${port}`);
});