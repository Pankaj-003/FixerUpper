# README

## Project Title

**FixerUpper - Hardware Store E-Commerce Website**

## Student Details

**Name:** 
**Student ID:** [Enter Your Student ID]

---

## Software Used

* Visual Studio Code
* PHP
* MySQL
* Bootstrap 5
* XAMPP
* Git & GitHub

---

## Project Installation Steps

### Step 1: Clone or Download the Project

Download the project files or clone the repository from GitHub.

### Step 2: Place the Project in XAMPP

Copy the project folder into the `htdocs` directory:

```text
C:\xampp\htdocs\FixerUpper
```

### Step 3: Start XAMPP Services

Open XAMPP Control Panel and start:

* Apache
* MySQL

---

## Database Import Instructions

1. Open phpMyAdmin.
2. Create a new database named:

```text
fixerupper_db
```

3. Click on the **Import** tab.
4. Select the file:

```text
database/fixerupper_db.sql
```

5. Click **Go** to import the database.

---

## Default Test Account

**Email:** [Demo3@gmail.com](mailto:test@example.com)
**Password:** Demoo@1234

*(Create this account through registration if not already available.)*

---

## Features Implemented

* User Registration and Login
* Product Listing
* Shopping Cart Management
* Guest Cart Support
* Secure Checkout Process
* Order Confirmation
* Order History
* Responsive User Interface
* Logout Functionality

---

## Security Measures Implemented

### 1. Password Hashing

User passwords are securely stored using PHP `password_hash()` and verified using `password_verify()`.

### 2. SQL Injection Prevention

All database queries use PDO prepared statements to prevent SQL injection attacks.

### 3. XSS Protection

User inputs and outputs are sanitized using `htmlspecialchars()` and safe DOM manipulation techniques.

### 4. Session Hijacking Protection

Secure PHP sessions are implemented with:

* Session ID regeneration after login
* HttpOnly cookies
* Secure cookie settings
* Session validation during authenticated requests

---

## Folder Structure

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
