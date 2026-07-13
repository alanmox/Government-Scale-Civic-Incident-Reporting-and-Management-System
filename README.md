# Government-Scale Civic Incident Reporting and Management System (GCIRMS)

An enterprise-grade, pure PHP 8.1+ Object-Oriented web application designed to manage civic infrastructure incident reporting, workflow routing, and resolution tracking for government agencies.

**Built without external frameworks (No Laravel, Symfony, or CodeIgniter)** to demonstrate mastery of software architecture, the Manual MVC pattern, and security by design.

---

## 🚀 Key Features

* **Manual MVC Architecture**: Custom Front Controller, Routing, Middleware Pipeline, and Request/Response lifecycle.
* **Role-Based Access Control (RBAC)**: Highly granular, database-driven permission system supporting Citizens, Verification Officers, Supervisors, and System Administrators.
* **State Machine Workflow Engine**: Immutable audit logging and strict transition rules for incident lifecycles (Submitted → Verified → Assigned → Resolved).
* **Automated Agency Routing**: Intelligent assignment of incidents to specific government departments based on category and SLA definitions.
* **Work Order Management**: Dedicated interfaces for officers to log percentage-based progress, material costs, and secure internal notes.
* **Bank-Grade Security**: PDO Prepared Statements, CSRF Token validation, Argon2ID password hashing, and MIME-type binary validation for file uploads.
* **REST API Layer**: Token-authenticated endpoints for mobile application integration.
* **Analytics Dashboards**: Role-specific data aggregation with Chart.js visualization.

---

## 🛠️ Technology Stack

* **Backend**: Pure PHP 8.1+ (Strict Types enforced)
* **Database**: MySQL 8.0+ (InnoDB, 3NF Normalized, `BINARY(16)` UUIDs)
* **Frontend**: HTML5, Vanilla JavaScript, Bootstrap 5.3
* **File Processing**: Native `finfo`, secure proxy download routes.
* **Package Manager**: Composer (Only for PSR-4 Autoloading and utilities like `ramsey/uuid`, `vlucas/phpdotenv`).

---

## 💻 Local Setup Instructions

### Prerequisites
* PHP 8.1 or higher (with `pdo_mysql`, `fileinfo`, `mbstring`, `curl` extensions enabled).
* MySQL 8.0+
* Composer installed globally.
* Apache Web Server (with `mod_rewrite` enabled).

### 1. Clone & Install
```bash
git clone https://github.com/your-username/gcirms.git
cd gcirms
composer install
```

### 2. Environment Configuration
Copy the environment template and update it with your local database credentials:
```bash
cp .env.example .env
```
Ensure `APP_URL` and database connection details (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) are correctly set in your `.env`.

### 3. Database Migration & Seeding
Run the custom CLI migration tool to build the database schema:
```bash
php database/migrate.php
```
*(Optional)* Seed the database with default roles, categories, and an admin user:
```bash
php database/seed.php
```

### 4. Serve the Application
You can use the built-in PHP development server for testing:
```bash
php -S localhost:8000 -t public/
```
Or configure an Apache Virtual Host pointing to the `/public` directory.

---

## 🔐 Default Credentials (If Seeded)

* **Admin Portal**: `admin@gcirms.gov.tz`
* **Password**: `Admin@2026!`

---

## 📁 Directory Structure
```
gcirms/
├── app/                  # Core Application Logic
│   ├── Controllers/      # Request handlers
│   ├── Core/             # Custom framework (Router, App, Pipeline)
│   ├── Exceptions/       # Custom Exception classes
│   ├── Middleware/       # Request filters (Auth, CSRF)
│   ├── Models/           # Data mapping and traits
│   ├── Repositories/     # Database interaction layer (PDO)
│   └── Services/         # Complex business logic
├── bootstrap/            # Application bootstrapping
├── config/               # Configuration arrays
├── database/             # SQL Migrations and Seeders
├── public/               # Document Root (index.php, CSS, JS)
├── resources/            # Lang files (Localization)
├── routes/               # Web and API route definitions
├── storage/              # Logs and secure file uploads
└── views/                # HTML layout templates
```

---

## 🛡️ License
This project was developed as a University Software Engineering capstone project. It is licensed under the MIT License for educational purposes.