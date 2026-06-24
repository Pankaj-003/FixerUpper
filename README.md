# FixerUpper

FixerUpper is a secure, responsive e-commerce prototype for a hardware store. The static frontend can be deployed to Vercel, the PHP API can be deployed independently to Render, and product/order data is stored in MySQL.

## Features

- Browse products with local product artwork, descriptions, prices, and stock.
- Add, update, and remove guest cart items without logging in.
- Persist the guest cart in `localStorage`.
- Register, log in, securely confirm an order, view a success page, and log out.
- Recalculate prices and verify stock on the server during checkout.
- Return JSON from every API endpoint.
- Responsive Bootstrap 5 interface with loading states, alerts, and toast notifications.

## Project structure

```text
FixerUpper/
├── database/
│   └── fixerupper_db.sql
├── backend/
│   ├── api/
│   ├── config/
│   ├── helpers/
│   ├── middleware/
│   ├── .env.example
│   ├── .htaccess
│   ├── Dockerfile
│   └── index.php
├── frontend/
│   ├── components/
│   ├── css/
│   ├── images/
│   ├── js/
│   ├── index.html
│   ├── products.html
│   ├── cart.html
│   ├── login.html
│   ├── register.html
│   ├── checkout.html
│   ├── order-success.html
│   └── vercel.json
└── README.md
```

## Security controls

- Passwords are stored with `password_hash()` and checked with `password_verify()`.
- All database operations use PDO prepared statements with emulated prepares disabled.
- PHP sessions use strict, HttpOnly, Secure, and configurable SameSite cookies.
- The session ID is regenerated after a successful login.
- A signed HS256 JWT is stored in an HttpOnly cookie and checked together with the PHP session.
- Checkout ignores browser-supplied prices and recalculates totals from locked database rows.
- Checkout writes the order, items, and stock updates in one database transaction.
- Input is validated and sanitized; user-originated API output uses `htmlspecialchars()`.
- Dynamic frontend content is inserted with `textContent` and DOM properties rather than HTML interpolation.
- CORS uses an explicit origin allowlist and credentialed requests.
- Security headers and centralized JSON exception handling are enabled.

## Local setup

### 1. Create the database

Install MySQL 8, then import:

```bash
mysql -u root -p < database/fixerupper_db.sql
```

This creates `fixerupper_db`, the four required tables, and eight sample products.

### 2. Configure the backend

PHP does not automatically load `.env` files in this dependency-free prototype. Set the variables from `backend/.env.example` in your shell, Apache configuration, Docker environment, or hosting dashboard.

Minimum local values:

```text
APP_ENV=development
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=fixerupper_db
DB_USER=root
DB_PASSWORD=your-password
JWT_SECRET=a-long-random-development-secret-of-at-least-32-characters
ALLOWED_ORIGINS=http://localhost:5500
COOKIE_SECURE=false
COOKIE_SAMESITE=Lax
```

Start the PHP API from the project root:

```bash
php -S localhost:8080 -t backend
```

### 3. Start the frontend

Serve the static frontend on port 5500:

```bash
cd frontend
python -m http.server 5500
```

Open `http://localhost:5500`. Do not open the HTML files directly if you want all browser security and Fetch behavior to match production.

## API endpoints

| Method | Endpoint | Authentication |
|---|---|---|
| POST | `/api/register.php` | Public |
| POST | `/api/login.php` | Public |
| POST | `/api/logout.php` | Session cookie |
| GET | `/api/products.php` | Public |
| POST | `/api/checkout.php` | JWT + PHP session |
| GET | `/api/orders.php` | JWT + PHP session |

Example registration body:

```json
{
  "name": "Alex Builder",
  "email": "alex@example.com",
  "password": "StrongPass123"
}
```

Example checkout body:

```json
{
  "items": [
    { "product_id": 1, "quantity": 2 }
  ],
  "shipping": {
    "name": "Alex Builder",
    "email": "alex@example.com",
    "phone": "555-0100",
    "address": "10 Workshop Road",
    "city": "Austin",
    "postal_code": "78701"
  }
}
```

## Deploy the backend to Render

1. Push this repository to GitHub.
2. Create a new Render Web Service and select the repository.
3. Set the root directory to `backend`.
4. Choose the Docker runtime. Render uses `backend/Dockerfile`.
5. Add all production environment variables:
   - `APP_ENV=production`
   - `APP_URL=https://fixerupper-api.onrender.com`
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
   - `JWT_SECRET` with a cryptographically random value of at least 32 characters
   - `JWT_TTL=3600`
   - `SESSION_TTL=3600`
   - `ALLOWED_ORIGINS=https://your-project.vercel.app`
   - `COOKIE_SECURE=true`
   - `COOKIE_SAMESITE=None`
6. Import `database/fixerupper_db.sql` into a MySQL provider reachable by Render.
7. Confirm `https://fixerupper-api.onrender.com/` returns a JSON health response.

If the Render service has a different hostname, replace `fixerupper-api.onrender.com` in `frontend/vercel.json`.

## Deploy the frontend to Vercel

1. Import the same GitHub repository into Vercel.
2. Set the Root Directory to `frontend`.
3. Select the “Other” framework preset and leave the build command empty.
4. Deploy.
5. Add the final Vercel origin to the backend `ALLOWED_ORIGINS` variable and redeploy the backend.

`frontend/vercel.json` proxies `/api/*` to the separate Render service. This keeps authentication cookies first-party in the browser while the backend remains independently hosted.

For direct API calls instead of the Vercel proxy, set `window.FIXERUPPER_API_URL` before `config.js` loads and ensure the backend CORS/cookie settings allow that origin.

## Production checklist

- Replace every example hostname and credential.
- Use HTTPS for both deployments.
- Use a strong unique `JWT_SECRET`.
- Restrict `ALLOWED_ORIGINS` to real frontend origins only.
- Use a managed MySQL database with TLS, backups, and a least-privileged database user.
- Configure rate limiting at Render, a reverse proxy, or a WAF for login and registration.
- Keep PHP, MySQL, Bootstrap, and the container base image patched.
- Add transactional email and a payment provider only through their server-side SDKs; never handle raw card data in this prototype.
