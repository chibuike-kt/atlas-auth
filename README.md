# Atlas Auth — Secure PHP Authentication System

Atlas Auth is a **modern, security-first authentication system** built from scratch in **plain PHP + MySQL**, without relying on heavy frameworks.
It implements industry-best practices normally found in mature systems (Laravel, Rails, etc.), but with **full transparency and control**.

This project is suitable as:

* a production auth service
* a base for SaaS / internal tools
* a reference implementation for secure PHP authentication

---

## Features

### Core Authentication

* User registration
* Secure login & logout
* Argon2id password hashing (bcrypt fallback)
* Constant-time password verification
* Email verification flow (token-based)
* Password reset flow (single-use, expiring tokens)

### Session Security

* Session regeneration on privilege change
* **Idle session timeout**
* **Absolute session lifetime**
* **Logout everywhere (global session invalidation)**

### Abuse & Attack Protection

* CSRF protection on all state-changing requests
* Rate limiting (login, password reset, verification resend)
* Generic auth error messages (prevents email enumeration)
* Secure token handling (hashed tokens only, never stored raw)

### Audit & Observability

* Audit logs for:

  * registration
  * login success/failure
  * email verification
  * password resets
  * logout everywhere
* Dev-mode email logging for verification & reset links

### Architecture

* Clean separation of concerns:

  * Domain
  * Infrastructure
  * HTTP (Controllers + Middleware)
* PSR-4 autoloading
* Case-safe file naming (Linux-ready)
* No secrets committed to Git

---

## Tech Stack

* **PHP 8.2+**
* **MySQL / MariaDB**
* PDO (prepared statements)
* Composer (autoload + dotenv)
* No framework dependency

---

## Project Structure

```
atlas-auth/
├── app/
│   ├── Domain/
│   │   └── Auth/
│   │       ├── EmailVerification.php
│   │       ├── PasswordReset.php
│   │       └── RememberMe.php
│   │
│   ├── Http/
│   │   ├── Controllers/Auth/
│   │   └── Middleware/
│   │
│   ├── Infrastructure/
│   │   ├── Database/
│   │   ├── Logging/
│   │   └── Security/
│   │
│   └── Support/
│
├── bootstrap/
├── public/
│   └── index.php
│
├── resources/
│   └── views/
│
├── storage/
│   └── logs/
│
├── .env.example
├── composer.json
└── README.md
```

---

## Security Design (Important)

### Passwords

* Hashed using **Argon2id**
* Automatically rehashed if algorithm settings change

### Tokens

* Email verification & password reset tokens:

  * Cryptographically random
  * Stored **hashed (SHA-256)** in DB
  * Expire automatically
  * Single-use only

### Sessions

* Regenerated on login
* Idle timeout (default: 30 minutes)
* Absolute lifetime (default: 8 hours)
* Global invalidation via `session_version`

### Logout Everywhere

* Implemented via `users.session_version`
* Incrementing the version instantly invalidates **all sessions on all devices**

---

## Environment Configuration

Create `.env` from `.env.example`:

```dotenv
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=atlas_auth
DB_USERNAME=root
DB_PASSWORD=

SESSION_IDLE_TIMEOUT_SECONDS=1800
SESSION_ABSOLUTE_TIMEOUT_SECONDS=28800
```

> `.env` is **ignored by Git**.
> `.env.example` is safe to commit.

---

## Database Setup

1. Create database:

```sql
CREATE DATABASE atlas_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Run migrations:

```bash
php public/migrate.php
```

---

## Running Locally

```bash
composer install
php -S localhost:8000 -t public
```

Visit:

* `/register` — create account
* `/` — login
* `/dashboard` — protected area

---

## Email (Dev Mode)

Email verification and password reset links are logged to:

```
storage/logs/app.log
```

Example:

```
VERIFY_EMAIL user_id=1 email=test@example.com link=http://localhost:8000/verify-email?token=...
RESET_PASSWORD user_id=1 email=test@example.com link=http://localhost:8000/reset-password?token=...
```

SMTP can be added later without changing the auth flow.

---

## Tested Scenarios

* Login brute-force attempts
* Password reset replay attacks
* Session fixation
* Session hijacking mitigation
* Token theft protection
* Cross-device logout enforcement

---

## Roadmap (Optional Extensions)

* TOTP / Authenticator App 2FA
* Device/session list with per-device revoke
* WebAuthn / Passkeys
* Account activity UI
* CSP / HSTS headers for production

---

## License

MIT — free to use, modify, and deploy.

---

## Author

Built by **Chibuike**
Focused on security-first backend systems and clean architecture.

---

**This is not a demo auth.
This is a production-grade authentication foundation.**
