# Expense Tracker

A PHP and MySQL expense tracker built for XAMPP. It includes user registration, login, sessions, cookies, and CRUD features for income and expense transactions.

## Features

- User registration and login
- Password hashing with `password_hash()` and `password_verify()`
- Session-protected dashboard
- Remember Me email cookie
- Add, view, edit, and delete transactions
- Income, expense, balance, and transaction count summary
- MySQL database schema included in `database.sql`

## Setup

1. Copy the `expense_tracker` folder into your XAMPP `htdocs` folder.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import `database.sql`.
4. Visit `http://localhost/expense_tracker` in your browser.

## Database Connection

The database settings are in `includes/db.php`:

```php
DB_HOST = localhost
DB_USER = root
DB_PASS = empty password
DB_NAME = expense_tracker
```

Update these values if your local MySQL setup uses different credentials.
