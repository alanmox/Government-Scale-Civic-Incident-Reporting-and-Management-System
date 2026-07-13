# AGENTS.md — AI Coding Agent Mission & Operating Manual

> **Document Type:** Principal Software Architecture Directive  
> **Project:** Government-Scale Civic Incident Reporting and Management System (GCIRMS)  
> **Audience:** All AI Coding Agents (Claude Code, OpenAI Codex, Cursor AI, GitHub Copilot, Cline, Roo Code, Gemini CLI, Continue.dev, Windsurf, Aider, and any autonomous coding agent)  
> **Status:** Binding — Read before generating any code.  
> **Version:** 1.0.0

---

## Table of Contents

1. [AI Mission](#1-ai-mission)
2. [Project Overview](#2-project-overview)
3. [Technology Stack](#3-technology-stack)
4. [Architecture Rules](#4-architecture-rules)
5. [Coding Standards](#5-coding-standards)
6. [OOP Rules](#6-oop-rules)
7. [Design Patterns](#7-design-patterns)
8. [Security Policies](#8-security-policies)
9. [Database Standards](#9-database-standards)
10. [Directory Structure](#10-directory-structure)
11. [Development Workflow](#11-development-workflow)
12. [AI Operating Rules](#12-ai-operating-rules)
13. [Git Workflow](#13-git-workflow)
14. [Documentation Rules](#14-documentation-rules)
15. [Testing Strategy](#15-testing-strategy)
16. [Deployment Checklist](#16-deployment-checklist)
17. [Code Review Checklist](#17-code-review-checklist)
18. [Definition of Done](#18-definition-of-done)
19. [Common Mistakes to Avoid](#19-common-mistakes-to-avoid)
20. [Future Extensibility Guidelines](#20-future-extensibility-guidelines)
21. [Module Specifications](#21-module-specifications)

---

## 1. AI Mission

Every AI agent contributing to GCIRMS has one mission:

**Build a secure, scalable, maintainable, government-grade civic incident reporting platform using pure PHP 8+ Object-Oriented Programming — without any PHP framework.**

You are not a code generator. You are a **software engineer** responsible for architectural integrity, security, performance, and code quality. Every line you write must be production-ready, auditable, and maintainable by human developers.

---

## 2. Project Overview

GCIRMS is a dual-purpose system:

1. **Production System** — A government-grade platform for citizens to report infrastructure and service delivery issues, and for government institutions to manage, route, escalate, and resolve those incidents through configurable workflows.

2. **University Software Engineering Project** — Demonstrates mastery of enterprise-grade OOP, architectural patterns, security best practices, and full-stack development.

### Incident Types (Examples)
- Water shortages
- Broken roads
- Electricity outages
- Waste management failures
- Flooding and drainage
- Corruption reports
- Environmental hazards
- Public safety issues
- Health facility problems
- School infrastructure issues
- Any government service delivery failure

### User Roles
- **Citizen** — Reports incidents, tracks progress, receives notifications
- **Municipal Worker** — Receives assignments, updates status
- **Supervisor** — Routes incidents, manages workflow, escalates
- **Administrator** — Manages users, roles, permissions, system settings
- **Government Institution Admin** — Manages institution-specific workflow and users
- **System Administrator** — Full system control, audit access
- **API Consumer** — Third-party integration access with rate-limited tokens

---

## 3. Technology Stack

### Mandatory (MUST USE)
| Technology | Version | Purpose |
|---|---|---|
| PHP | 8.1+ | Server-side language — pure OOP only |
| MySQL | 8.0+ | Relational database — 3NF, InnoDB |
| PDO | PHP 8.1+ | Database access — prepared statements only |
| HTML5 | — | Semantic markup |
| CSS3 | — | Styling |
| Bootstrap | 5.x | UI framework |
| JavaScript | ES6+ | Client-side interactivity |
| AJAX (Fetch API) | — | Asynchronous operations |
| Apache | 2.4+ | Web server with mod_rewrite |

### Mandatory (MUST NOT USE)
| Technology | Reason |
|---|---|
| Laravel | No frameworks allowed — pure PHP only |
| CodeIgniter | No frameworks allowed |
| Symfony | No frameworks allowed |
| CakePHP | No frameworks allowed |
| Yii | No frameworks allowed |
| Slim | No frameworks allowed |
| WordPress | Not an enterprise incident system |
| Drupal | Not an enterprise incident system |
| Joomla | Not an enterprise incident system |
| Any PHP framework | Violates project requirements |
| jQuery | Use vanilla JS / Fetch API instead |
| MySQLi | Use PDO only |

### Permitted Libraries
- PHPMailer (email)
- FPDF/TCPDF (PDF reports)
- PhpSpreadsheet (Excel exports)
- vlucas/phpdotenv (.env configuration)
- monolog/monolog (structured logging)
- ramsey/uuid (UUID generation)

---

## 4. Architecture Rules

### Manual MVC Pattern

The application MUST follow a **manual MVC** architecture — no framework routing, no framework ORM, no framework dependency injection.

```
Request -> Front Controller (index.php) -> Router -> Controller -> Service -> Repository -> Model (PDO)
                                                                       |
                                                                  View (HTML/Bootstrap)
```

### Architectural Constraints

1. **No procedural PHP** — except the bootstrap files (`index.php`, `config/bootstrap.php`).
2. **Controllers MUST be thin** — max 15 lines of logic; delegate to Services.
3. **Business logic MUST live in Services** — never in Controllers or Views.
4. **Data access MUST use Repository Pattern** — never direct PDO in Controllers or Services.
5. **Views MUST NOT contain PHP business logic** — only `echo`, `htmlspecialchars`, and loops/conditionals for display.
6. **Every public method MUST have a return type declaration**.
7. **Every method parameter MUST have a type declaration**.
8. **Every class MUST declare strict types** (`declare(strict_types=1)`).
9. **Namespaces MUST follow directory structure** — `App\Controllers`, `App\Services`, `App\Repositories`, etc.
10. **No hardcoded configuration values** — all configuration in `.env` or `config/` files.

### Layer Responsibilities

| Layer | Responsibility | Forbidden |
|---|---|---|
| **Router** | Parse URL, match route, dispatch to controller | Business logic, DB access |
| **Controller** | Handle request, call service, return response | Business logic, SQL, HTML rendering |
| **Service** | Business logic, orchestration, validation | HTTP concerns, SQL, HTML rendering |
| **Repository** | Data access only (PDO queries) | Business logic, HTTP concerns |
| **Model** | Data structure, getters/setters, lightweight logic | SQL, business logic |
| **View** | HTML rendering, Bootstrap components | Business logic, SQL, complex conditionals |
| **Middleware** | Request filtering (auth, CSRF, roles) | Business logic, data access |
| **Validator** | Input validation rules | Business logic, data access |
| **Helper/Utility** | Stateless reusable functions | Business logic, state |

---

## 5. Coding Standards

### Naming Conventions

| Element | Convention | Example |
|---|---|---|
| **Classes** | PascalCase | `IncidentService`, `UserRepository` |
| **Interfaces** | PascalCase with Interface suffix | `IncidentRepositoryInterface` |
| **Traits** | PascalCase with Trait suffix | `LoggableTrait`, `AuditableTrait` |
| **Methods** | camelCase | `getIncidentById()`, `createUser()` |
| **Properties** | camelCase | `$incidentId`, `$userName` |
| **Constants** | UPPER_SNAKE_CASE | `MAX_FILE_SIZE`, `STATUS_OPEN` |
| **Files** | PascalCase matching class name | `IncidentService.php` |
| **Namespaces** | PascalCase matching directory | `App\Services\IncidentService` |
| **Routes** | kebab-case | `/report-incident`, `/manage-users` |
| **Database tables** | snake_case plural | `incidents`, `users`, `incident_categories` |
| **Database columns** | snake_case | `created_at`, `updated_by` |
| **JS functions** | camelCase | `submitIncident()`, `loadMap()` |
| **CSS classes** | BEM methodology | `incident-card__title--urgent` |

### File Structure

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\IncidentRepositoryInterface;
use App\Validators\IncidentValidator;
use App\Exceptions\ValidationException;

final class IncidentService
{
    public function __construct(
        private readonly IncidentRepositoryInterface $incidentRepository,
        private readonly IncidentValidator $validator
    ) {
    }

    public function createIncident(array $data): array
    {
        // ...
    }
}
```

### Formatting Rules
- **Indentation:** 4 spaces (no tabs)
- **Line length:** max 120 characters
- **Braces:** Allman style for classes/interfaces/traits; 1TBS (K&R) for control structures
- **Blank line after** `namespace` declaration, `use` statements, and between methods
- **Always use** `readonly` for constructor-injected dependencies
- **Always use** `final` for classes not designed for inheritance
- **Always use** `private` unless a parent class needs `protected`
- **No `public` properties** — use getters/setters

```php
// Allman style for class/interface/trait
class IncidentService
{
    // K&R style for control structures
    public function process(array $data): array {
        if (empty($data)) {
            throw new ValidationException('Data cannot be empty');
        }

        return $this->repository->save($data);
    }
}
```

### Comments
- **PHPDoc on every class, interface, trait, and public method**
- **Inline comments only** to explain *why*, never *what* (code should be self-documenting)
- **No commented-out code** — delete it
- **`@throws` annotations** on all methods that throw exceptions

---

## 6. OOP Rules

All code MUST demonstrate mastery of the following OOP principles:

### Encapsulation
- All properties `private` or `protected` — never `public`
- Use getters/setters with validation
- Defensive copying in getters returning arrays/objects

### Inheritance
- Use abstract base classes for shared behavior (e.g., `BaseRepository`, `BaseController`)
- Maximum inheritance depth: 3 levels
- Prefer composition over inheritance

### Polymorphism
- Interface-based polymorphism for interchangeable implementations
- Service methods should accept interfaces, not concrete classes

### Abstraction
- Program to interfaces, not implementations
- Define interfaces in `App\Contracts\` namespace

### Interfaces
- One primary responsibility per interface (Interface Segregation Principle)
- Suffix: `Interface` (e.g., `IncidentRepositoryInterface`)

### Traits
- Use for cross-cutting concerns only: logging, auditing, caching, soft deletes
- Never use traits to share business logic between unrelated classes

### Dependency Injection
- Constructor injection exclusively (no setter injection, no service locator)
- Dependencies typed with interfaces, not concrete classes
- Use PHP 8+ constructor promotion with `readonly`

```php
public function __construct(
    private readonly IncidentRepositoryInterface $incidentRepository,
    private readonly LoggerInterface $logger,
    private readonly NotificationService $notificationService
) {
}
```

### SOLID Principles Enforcement

| Principle | Rule |
|---|---|
| **S** Single Responsibility | Each class has exactly one reason to change |
| **O** Open/Closed | Open for extension, closed for modification — use interfaces |
| **L** Liskov Substitution | Subtypes must be substitutable for their base types |
| **I** Interface Segregation | Small, focused interfaces — no fat interfaces |
| **D** Dependency Inversion | Depend on abstractions, not concretions |

---

## 7. Design Patterns

The following design patterns MAY be used where appropriate:

| Pattern | When to Use |
|---|---|
| **Singleton** | Only for database connection (PDO wrapper) — never for business classes |
| **Factory** | Creating complex objects or resolving dependencies based on configuration |
| **Repository** | ALL data access — always |
| **Service Layer** | ALL business logic — always |
| **Strategy** | Different routing/escalation/notification algorithms |
| **Observer** | Event-driven notifications and audit logging |
| **Template Method** | Shared workflow processes with customizable steps |
| **Chain of Responsibility** | Middleware pipeline for request filtering |
| **DTO (Data Transfer Object)** | Data transfer between layers (immutable objects) |
| **Front Controller** | Single entry point (`index.php`) |
| **Dependency Injection Container** | Simple DI container (custom, not framework) |

---

## 8. Security Policies

Security is **non-negotiable**. Every AI agent MUST enforce the following:

### Password Security
- Hash passwords with `password_hash(PASSWORD_ARGON2ID)`
- Verify with `password_verify()`
- Minimum password length: 12 characters
- Enforce password complexity (uppercase, lowercase, number, special character)
- Never log, echo, or store plain-text passwords

### SQL Injection Prevention
- **ALWAYS** use PDO prepared statements — never interpolate variables into SQL
- Use named parameters (`:id`) over positional (`?`) for clarity
- Validate and sanitize all input before passing to repository

### CSRF Protection
- Every state-changing request (POST, PUT, DELETE) requires a CSRF token
- Token generated per session, stored in `$_SESSION`
- Token included in all forms and AJAX requests as `X-CSRF-Token` header
- Token validated in middleware before reaching controller

```php
// CSRF Token Generation
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// CSRF Validation Middleware
if (!hash_equals($_SESSION['csrf_token'], $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
    throw new CsrfException('Invalid CSRF token');
}
```

### XSS Prevention
- Escape ALL output with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`
- Use a helper function: `e($value)` for convenience
- Never echo user input directly
- Set Content-Security-Policy headers
- Use `X-Content-Type-Options: nosniff`

### Session Security
- Regenerate session ID after login with `session_regenerate_id(true)`
- Set session cookie flags: `HttpOnly`, `Secure` (production), `SameSite=Strict`
- Session timeout after 30 minutes of inactivity
- Destroy session on logout
- Store only user ID and role in session — never sensitive data

### RBAC (Role-Based Access Control)
- Three-level permission model: **Role → Permission → Resource**
- Middleware checks permissions before controller dispatch
- Default deny — explicitly grant access
- Cache permissions per session for performance

### Authorization Middleware
```php
class AuthorizationMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $userId = $request->getSession()->get('user_id');
        $requiredPermission = $request->getRoute()->getRequiredPermission();

        if (!$this->authService->hasPermission($userId, $requiredPermission)) {
            throw new ForbiddenException('Insufficient permissions');
        }

        return $next($request);
    }
}
```

### Secure File Uploads
- Validate file MIME type server-side (never trust client-side)
- Restrict file extensions: `jpg`, `jpeg`, `png`, `gif`, `pdf`, `doc`, `docx`
- Maximum file size: 10MB (configurable in `.env`)
- Store outside web root (e.g., `/var/data/uploads/`)
- Serve files through a PHP proxy script (never direct URL)
- Scan for malware if possible
- Generate random filenames — never use user-supplied names

### Encryption of Sensitive Data
- Encrypt PII (personal data) at rest using `openssl_encrypt()` with AES-256-GCM
- Store encryption keys in `.env` — never in the database or code
- Decrypt only when needed for display
- Never log decrypted data

### Data Validation Rules
- Validate ALL input server-side — client-side validation is UX only
- Use a `Validator` class with chainable rules
- Whitelist validation (allow known good) over blacklist (block known bad)
- Validate: type, length, format, range, existence in DB

### Audit Logging
- Log all state-changing actions: who, what, when, IP, user agent
- Log failed authentication attempts
- Never log passwords, tokens, or PII
- Store audit logs in a separate `audit_logs` table
- Audit logs are append-only — never update or delete
- Designate a database user with INSERT-only access to audit_logs

### Security Headers (Production)
```
Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 0
Strict-Transport-Security: max-age=31536000; includeSubDomains
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(self), camera=()
```

---

## 9. Database Standards

### MySQL Configuration
- Engine: InnoDB (all tables)
- Charset: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`
- Storage engine must support transactions

### Normalization
- All tables MUST be in **Third Normal Form (3NF)**
- No duplicate columns across tables
- No composite columns (e.g., comma-separated tags — use junction tables)
- Every non-key column must be functionally dependent on the primary key

### Naming Conventions
- Table names: `snake_case`, plural (e.g., `incidents`, `incident_categories`)
- Column names: `snake_case` (e.g., `created_at`, `assigned_to`)
- Primary keys: `id` (auto-increment or UUID binary)
- Foreign keys: `singular_table_name_id` (e.g., `user_id`, `category_id`)
- Junction tables: `table1_table2` (e.g., `role_permissions`, `user_roles`)
- Index names: `idx_column_name` or `idx_table1_table2` for composite

### UUID Support
- Store UUIDs as `BINARY(16)` for performance
- Use `ramsey/uuid` library or `UUID()`
- Conversion functions in a Helper:
  ```php
  public static function uuidToBin(string $uuid): string
  public static function binToUuid(string $binary): string
  ```

### Indexing Rules
- Primary key on every table
- Foreign key columns MUST be indexed
- Index columns used in `WHERE`, `JOIN`, `ORDER BY`, `GROUP BY`
- Composite indexes for multi-column queries (order by selectivity)
- Avoid over-indexing — no more than 5-7 indexes per table
- Use `EXPLAIN ANALYZE` to verify query plans
- Full-text indexes on searchable text columns

### Soft Deletes
- Every table (except junction/audit) MUST have:
  - `deleted_at` TIMESTAMP NULL DEFAULT NULL
  - `deleted_by` BINARY(16) NULL (UUID of user who deleted)
- All queries MUST include `WHERE deleted_at IS NULL` (unless admin querying deleted records)
- Repository base class handles soft delete filtering

### Audit Columns
Every table MUST include:
```sql
created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
created_by     BINARY(16) NOT NULL
updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
updated_by     BINARY(16) NOT NULL
deleted_at     TIMESTAMP NULL DEFAULT NULL
deleted_by     BINARY(16) NULL
```

### Migration Scripts
- Each migration: `YYYYMMDD_HHMMSS_description.sql`
- Migration runner reads directory, tracks executed migrations in `migrations` table
- Migrations are transactional (all-or-nothing per file)
- Never edit an existing migration — create a new one

```sql
-- Example: 20260101_000000_create_users_table.sql
CREATE TABLE IF NOT EXISTS `users` (
    `id` BINARY(16) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    -- ...
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Seed Data
- Seed scripts in `database/seeds/`
- Idempotent — can be run multiple times without duplicates
- Use `INSERT IGNORE` or check existence first
- Include: default admin user, base roles, base permissions, sample categories

### Query Rules
- No `SELECT *` — always specify columns
- Use meaningful table aliases
- Paginate all list queries (`LIMIT` / `OFFSET` or cursor-based)
- Use `EXPLAIN` on complex queries
- Avoid `LIKE '%term%'` on large tables — use Full-Text Search or external search
- Wrap related operations in transactions
- Lock tables only when absolutely necessary

### PDO Usage
```php
// CORRECT — prepared statement with named parameters
$stmt = $this->pdo->prepare(
    'SELECT id, title, status FROM incidents WHERE category_id = :categoryId AND status = :status LIMIT :limit OFFSET :offset'
);
$stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
$stmt->bindValue(':status', $status, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
return $stmt->fetchAll(PDO::FETCH_ASSOC);

// WRONG — string interpolation in SQL
$stmt = $this->pdo->query("SELECT * FROM incidents WHERE id = $id");
```

---

## 10. Directory Structure

```
project-root/
├── assets/
│   ├── css/
│   │   ├── app.css
│   │   └── bootstrap.min.css
│   ├── js/
│   │   ├── app.js
│   │   ├── modules/
│   │   │   ├── incidents.js
│   │   │   ├── maps.js
│   │   │   ├── notifications.js
│   │   │   └── dashboard.js
│   │   └── vendor/         # Third-party JS (via CDN fallback)
│   ├── images/
│   └── favicon.ico
├── config/
│   ├── app.php             # Application configuration
│   ├── database.php        # Database connection config
│   ├── mail.php            # Email configuration
│   ├── permissions.php     # Permission definitions
│   └── bootstrap.php       # Autoloader, error handler, session init
├── database/
│   ├── migrations/
│   │   ├── 20260101_000000_create_users_table.sql
│   │   ├── 20260101_000001_create_roles_table.sql
│   │   └── ...
│   ├── seeds/
│   │   ├── admin_user.sql
│   │   ├── roles.sql
│   │   └── categories.sql
│   └── schema.sql          # Full schema reference (auto-generated)
├── logs/
│   ├── app.log
│   ├── error.log
│   └── audit.log
├── public/
│   ├── index.php           # Front controller (entry point)
│   ├── .htaccess           # Apache rewrite rules
│   └── uploads/            # Symlink to storage/uploads (served via PHP)
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── IncidentController.php
│   │   ├── DashboardController.php
│   │   ├── AdminController.php
│   │   ├── ApiController.php
│   │   └── BaseController.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── IncidentService.php
│   │   ├── NotificationService.php
│   │   ├── WorkflowService.php
│   │   ├── EscalationService.php
│   │   ├── AnalyticsService.php
│   │   ├── FileUploadService.php
│   │   ├── MapService.php
│   │   └── BaseService.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   ├── IncidentRepository.php
│   │   ├── NotificationRepository.php
│   │   ├── AuditLogRepository.php
│   │   └── BaseRepository.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Incident.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── Category.php
│   │   ├── Comment.php
│   │   ├── Attachment.php
│   │   └── BaseModel.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   ├── AuthorizationMiddleware.php
│   │   ├── RateLimitMiddleware.php
│   │   └── GuestMiddleware.php
│   ├── Validators/
│   │   ├── IncidentValidator.php
│   │   ├── UserValidator.php
│   │   └── BaseValidator.php
│   ├── Contracts/
│   │   ├── IncidentRepositoryInterface.php
│   │   ├── UserRepositoryInterface.php
│   │   ├── NotificationServiceInterface.php
│   │   └── MiddlewareInterface.php
│   ├── Traits/
│   │   ├── LoggableTrait.php
│   │   ├── AuditableTrait.php
│   │   └── SoftDeletesTrait.php
│   ├── Exceptions/
│   │   ├── AuthenticationException.php
│   │   ├── AuthorizationException.php
│   │   ├── ValidationException.php
│   │   ├── NotFoundException.php
│   │   ├── ForbiddenException.php
│   │   └── BaseException.php
│   ├── Helpers/
│   │   ├── SecurityHelper.php
│   │   ├── DateHelper.php
│   │   ├── FileHelper.php
│   │   └── StringHelper.php
│   ├── Utilities/
│   │   ├── Paginator.php
│   │   ├── Router.php
│   │   ├── Session.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Database.php       # PDO Singleton wrapper
│   │   └── Container.php       # Simple DI Container
│   ├── Cache/
│   │   └── CacheService.php
│   └── Logging/
│       └── Logger.php
├── storage/
│   ├── uploads/               # File uploads (outside web root)
│   ├── cache/                 # Cache files
│   └── export/                # Generated exports (PDF, Excel)
├── templates/
│   ├── layouts/
│   │   ├── main.php
│   │   └── admin.php
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   └── reset-password.php
│   ├── incidents/
│   │   ├── create.php
│   │   ├── show.php
│   │   ├── edit.php
│   │   └── list.php
│   ├── dashboard/
│   │   ├── citizen.php
│   │   ├── worker.php
│   │   └── admin.php
│   ├── admin/
│   │   ├── users.php
│   │   ├── roles.php
│   │   └── settings.php
│   └── partials/
│       ├── header.php
│       ├── footer.php
│       ├── navbar.php
│       ├── sidebar.php
│       └── pagination.php
├── tests/
│   ├── Unit/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Validators/
│   ├── Integration/
│   ├── Feature/
│   └── bootstrap.php
├── vendor/                     # Composer dependencies (gitignored)
├── .env                        # Environment variables (gitignored)
├── .env.example                # Environment template (committed)
├── .gitignore
├── .htaccess                   # Root-level rewrite rules
├── AGENTS.md                   # This file
├── CHANGELOG.md
├── composer.json
├── DATABASE.md
├── DEPLOYMENT.md
├── LICENSE
├── README.md
├── SECURITY.md
├── TESTING.md
└── USER_GUIDE.md
```

---

## 11. Development Workflow

### How New Features Are Built

1. **Create a feature branch** from `develop`: `feature/GCI-XXX-short-description`
2. **Review AGENTS.md** — ensure compliance
3. **Check existing schema** — never invent conflicting tables
4. **Implement database migration** (if schema change needed)
5. **Implement Repository** (contract first, then concrete class)
6. **Implement Service** (business logic)
7. **Implement Controller** (thin — validate, call service, respond)
8. **Implement View** (Bootstrap 5, escaped output)
9. **Add JavaScript** (vanilla JS, Fetch API)
10. **Add routes** in `routes/web.php` or `routes/api.php`
11. **Write tests** (unit + integration)
12. **Update CHANGELOG.md**
13. **Create Pull Request** to `develop`
14. **Code review** (see checklist)
15. **Merge after approval**

### How Bugs Are Fixed

1. **Create a bugfix branch**: `bugfix/GCI-XXX-bug-description`
2. **Write a test that reproduces the bug** (failing test)
3. **Fix the bug** — minimal change, no scope creep
4. **Verify test passes**
5. **Run existing tests** — ensure no regression
6. **Update CHANGELOG.md** under `### Fixed`
7. **Create Pull Request** to `develop`

### Branch Strategy

```
main (production)
  └── develop (integration)
        ├── feature/GCI-XXX-*
        ├── bugfix/GCI-XXX-*
        ├── hotfix/GCI-XXX-*      (from main, merged to both main and develop)
        └── release/v*.*.*         (from develop, merged to main and back to develop)
```

### Commit Message Style

```
type(scope): Short description (max 72 chars)

Optional longer description. Wrap at 72 characters.

Closes GCI-XXX
```

**Types:** `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `style`, `perf`, `security`, `db`

**Examples:**
```
feat(incidents): add incident escalation workflow

Implements configurable escalation rules based on priority and SLA.
Includes notification triggers at each escalation level.

Closes GCI-142
```

```
fix(auth): prevent session fixation after password change

Regenerate session ID when password is changed to prevent
session fixation attacks.

Closes GCI-67
```

---

## 12. AI Operating Rules

### Mandatory Behaviors

1. **Read AGENTS.md first** — you MUST read this document before generating any code.
2. **Never violate architecture** — no frameworks, no procedural PHP (except bootstrap).
3. **Never introduce frameworks** — no Laravel, Symfony, or any PHP framework.
4. **Never duplicate code** — if logic is repeated, extract it to a Service, Trait, or Helper.
5. **Prefer reusable services** — always ask "will this logic be needed elsewhere?"
6. **Keep controllers thin** — max 15 lines; call a Service and return.
7. **Keep business logic inside Services** — never in Controllers or Views.
8. **Use Repository Pattern** — ALL database access through Repositories.
9. **Explain architectural decisions** — when you deviate from patterns, explain why in a comment or commit message.
10. **Never remove security** — no exceptions for "simplicity" or "speed."
11. **Never ignore validation** — validate ALL input, always.
12. **Never break backward compatibility** — deprecate, don't delete.
13. **Always maintain naming consistency** — follow the conventions in this document.
14. **Never invent database tables that conflict with existing schema** — read existing migrations first.
15. **Never bypass the Service layer** — Controllers call Services, not Repositories directly.
16. **Always declare `strict_types=1`** — every PHP file.
17. **Always return typed responses** — every public method has a return type.
18. **Never hardcode credentials** — use `.env` and configuration files.
19. **No `var_dump`, `print_r`, `die`, `exit`** in committed code — use the Logger.
20. **No `@` operator** — handle errors properly.
21. **Always use `nullsafe` and `match` expressions** where appropriate (PHP 8+).
22. **Prefer `match` over `switch`** — always.
23. **Prefer `enum` over class constants** for fixed value sets.

### Decision-Making Priority

When making implementation decisions, follow this priority:

1. **Security** — never compromised
2. **Correctness** — works as specified
3. **Maintainability** — readable, testable, extensible
4. **Performance** — fast enough, optimized
5. **Developer Experience** — ergonomic, well-documented

### What to Do When Unsure

1. Check existing code for patterns
2. Check this AGENTS.md for rules
3. Check `database/migrations/` for existing schema
4. Check `config/` for existing configuration
5. Ask the user for clarification if still unsure

---

## 13. Git Workflow

### Rules
- **Never commit secrets** — check `.env`, credentials, tokens
- **Never commit generated files** — `vendor/`, `node_modules/`, compiled assets
- **Never commit to `main` directly** — always use Pull Requests
- **Never force push** to shared branches
- **Never amend published commits**
- **Squash merge** feature branches (keep `develop` history clean)
- **Rebase only** on local branches before creating PR
- **Atomic commits** — one logical change per commit
- **Signed commits** preferred (GPG)

### Pre-Commit Checklist

- [ ] No debug code (`var_dump`, `dd`, `print_r`, `echo` for debugging)
- [ ] No credentials or secrets
- [ ] No commented-out code
- [ ] No `TODO` without a ticket reference (`TODO [GCI-XXX]`)
- [ ] Code follows AGENTS.md standards
- [ ] Tests pass
- [ ] CHANGELOG.md updated
- [ ] No warnings or errors in logs
- [ ] `.env.example` updated if new config keys added

### `.gitignore` Requirements

```
vendor/
.env
.env.local
logs/*.log
storage/uploads/*
storage/cache/*
storage/export/*
!storage/uploads/.gitkeep
!storage/cache/.gitkeep
!storage/export/.gitkeep
.DS_Store
Thumbs.db
*.log
```

---

## 14. Documentation Rules

### Every Code Change MUST Include Documentation Updates

| Document | When to Update |
|---|---|
| **README.md** | New features, changed setup, updated requirements |
| **CHANGELOG.md** | Every change — follow Keep a Changelog format |
| **API.md** | New/modified API endpoints |
| **DATABASE.md** | Schema changes, new tables/columns |
| **SECURITY.md** | Security policy changes, vulnerability disclosures |
| **DEPLOYMENT.md** | Changed deployment process, new dependencies |
| **TESTING.md** | New test categories, changed test process |
| **USER_GUIDE.md** | New user-facing features, changed UI |

### Changelog Format (Keep a Changelog)

```markdown
## [1.2.0] - 2026-07-13

### Added
- Incident escalation workflow (GCI-142)
- Map-based incident tracking (GCI-158)

### Changed
- Upgraded Bootstrap 4.6 to 5.3 (GCI-201)

### Fixed
- Session timeout not redirecting to login (GCI-67)
- XSS in incident description field (GCI-89)

### Security
- CSRF protection added to all forms (GCI-12)
```

### API Documentation Format
```markdown
## POST /api/v1/incidents

Create a new incident report.

### Headers
- `Authorization: Bearer <token>`
- `X-CSRF-Token: <token>`
- `Content-Type: application/json`

### Request Body
```json
{
  "category_id": "uuid",
  "title": "string (max 255)",
  "description": "string (max 5000)",
  "latitude": "float (optional)",
  "longitude": "float (optional)",
  "attachments": ["base64 encoded files"]
}
```

### Response (201 Created)
```json
{
  "id": "uuid",
  "reference_number": "GCI-2026-00001",
  "status": "submitted",
  "created_at": "2026-07-13T10:30:00Z"
}
```

### Errors
- `400 Bad Request` — Validation failed
- `401 Unauthorized` — Invalid or missing token
- `403 Forbidden` — Insufficient permissions
- `422 Unprocessable Entity` — Invalid data
```

---

## 15. Testing Strategy

### Testing Requirements

| Test Type | Coverage Target | Tool |
|---|---|---|
| **Unit Tests** (Services, Validators, Helpers, Models) | 80%+ | PHPUnit 10+ |
| **Integration Tests** (Repositories with test DB) | 70%+ | PHPUnit 10+ |
| **Feature Tests** (Full request-response cycles) | 60%+ | PHPUnit 10+ |
| **Security Tests** (CSRF, XSS, SQLi, Auth bypass) | 100% of security features | PHPUnit + OWASP ZAP (manual) |
| **API Tests** (All endpoints) | 100% of endpoints | PHPUnit + Postman collection |
| **Performance Tests** (Critical paths) | Key workflows | Custom scripts |

### Test Structure

```
tests/
├── Unit/
│   ├── Services/
│   │   └── IncidentServiceTest.php
│   ├── Validators/
│   │   └── IncidentValidatorTest.php
│   └── Helpers/
│       └── SecurityHelperTest.php
├── Integration/
│   └── Repositories/
│       └── IncidentRepositoryTest.php
├── Feature/
│   ├── Auth/
│   ├── Incidents/
│   └── Workflow/
├── bootstrap.php
└── phpunit.xml
```

### Test Naming Conventions
- Class: `{ClassName}Test`
- Method: `test_{scenario}_{expectedBehavior}`
- Example: `test_createIncident_withValidData_returnsIncidentArray()`

### Test Requirements

1. **Each bug fix MUST include a test that reproduces the bug**
2. **Each new feature MUST include tests for happy path and error paths**
3. **Tests MUST be independent** — can run in any order
4. **Use a test database** — never run tests on production
5. **Clean up test data** after each test (transactions or truncation)
6. **Mock external services** (email, SMS, maps)

### What to Test

- **Authentication**: Login, logout, registration, password reset, session timeout, remember me
- **CRUD**: Create, read, update, soft delete, restore for all entities
- **Workflow**: Status transitions, assignment, escalation, SLA breaches
- **Notifications**: Email, SMS, in-app — delivery and formatting
- **Reports**: Report generation, filtering, export (PDF, Excel, CSV)
- **Search**: By keyword, category, date range, location, status
- **Permissions**: Each role sees only permitted actions
- **API**: All endpoints, rate limiting, authentication methods
- **Security**: CSRF, XSS, SQL injection, file upload, RBAC bypass attempts
- **Performance**: Response time under load, query optimization

---

## 16. Deployment Checklist

### Pre-Deployment

- [ ] All tests pass (`vendor/bin/phpunit`)
- [ ] Security scan complete (no critical vulnerabilities)
- [ ] CHANGELOG.md updated with release version
- [ ] Version bumped in `config/app.php`
- [ ] Database migrations run and verified
- [ ] Seed data applied (if first deployment)
- [ ] `.env` configured for production
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Error logging configured
- [ ] PHP OPcache enabled
- [ ] All caches cleared
- [ ] Assets minified (CSS, JS)
- [ ] Upload directories exist with correct permissions
- [ ] Log directories exist with correct permissions
- [ ] SSL certificate valid and auto-renew configured
- [ ] Apache mod_rewrite enabled
- [ ] `.htaccess` files in place

### Server Requirements

- PHP 8.1+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `json`, `fileinfo`, `gd`, `xml`, `zip`, `intl`
- MySQL 8.0+
- Apache 2.4+ with mod_rewrite, mod_headers, mod_expires
- Composer 2.x
- SSL certificate (Let's Encrypt or equivalent)
- Backup solution (database + files)
- Monitoring (uptime, error rate, response time)

### Performance Checklist (Production)

- [ ] MySQL query cache configured
- [ ] Slow query log enabled (1 second threshold)
- [ ] PHP OPcache enabled (`opcache.enable=1`, `opcache.memory_consumption=128`)
- [ ] Asset compression (Gzip/Brotli) enabled
- [ ] Browser caching configured (`Cache-Control`, `ETag`)
- [ ] CDN for Bootstrap, JS libraries (with fallback)
- [ ] Image optimization for uploads
- [ ] Database indexes reviewed with `EXPLAIN`
- [ ] Pagination implemented for all list views
- [ ] API rate limiting configured

### Post-Deployment

- [ ] Smoke test all critical flows
- [ ] Monitor error logs for 24 hours
- [ ] Verify database backups running
- [ ] Verify cron jobs running (e.g., escalation checks, report generation)
- [ ] Verify email delivery
- [ ] Verify file uploads work

---

## 17. Code Review Checklist

### Security
- [ ] No SQL injection risks (prepared statements used everywhere)
- [ ] No XSS vulnerabilities (output escaped)
- [ ] CSRF protection on all state-changing requests
- [ ] Input validation on all user input
- [ ] Authorization check on every protected route
- [ ] No hardcoded secrets or credentials
- [ ] File upload validation (type, size, name)
- [ ] Session security (regeneration, timeout, flags)

### Architecture
- [ ] Follows manual MVC pattern
- [ ] Controller is thin (no business logic)
- [ ] Business logic in Service layer
- [ ] Data access in Repository layer
- [ ] No framework violations
- [ ] Namespace matches directory structure
- [ ] Proper use of interfaces and dependency injection

### Code Quality
- [ ] `declare(strict_types=1)` present
- [ ] Type hints on all parameters and return types
- [ ] Follows naming conventions (PascalCase, camelCase, snake_case)
- [ ] No dead code or commented-out code
- [ ] No `TODO` without ticket reference
- [ ] Docblocks on all public methods
- [ ] No debug output (`var_dump`, `print_r`, `echo` for debugging)
- [ ] Error handling (try/catch, custom exceptions)
- [ ] No code duplication (DRY principle)
- [ ] SOLID principles followed

### Database
- [ ] Migration is reversible (has `DOWN` or is additive)
- [ ] New columns/indexes documented in DATABASE.md
- [ ] Foreign keys and indexes defined
- [ ] Soft delete columns included (if applicable)
- [ ] Audit columns included

### Testing
- [ ] Tests written for new code
- [ ] Tests pass
- [ ] Edge cases covered (null, empty, invalid input)
- [ ] Error paths tested

### Documentation
- [ ] CHANGELOG.md updated
- [ ] API.md updated (if API changed)
- [ ] DATABASE.md updated (if schema changed)
- [ ] DOCUMENTATION.md updated (if user-facing change)

---

## 18. Definition of Done

A feature, fix, or change is **Done** only when ALL of the following are true:

### Code
- [ ] Code is implemented per AGENTS.md standards
- [ ] No framework violations
- [ ] No security vulnerabilities
- [ ] All type hints and return types declared
- [ ] All input validated and output escaped
- [ ] CSRF protection in place for state changes
- [ ] Code follows naming conventions

### Database
- [ ] Migration written (if schema change)
- [ ] Seed data updated (if needed)
- [ ] DATABASE.md updated (if schema change)

### Architecture
- [ ] Controller is thin
- [ ] Business logic in Service
- [ ] Data access through Repository
- [ ] SOLID principles applied

### Testing
- [ ] Unit tests written and passing
- [ ] Integration tests written and passing
- [ ] Edge cases tested
- [ ] No regression in existing tests

### Documentation
- [ ] CHANGELOG.md updated
- [ ] API.md updated (if applicable)
- [ ] USER_GUIDE.md updated (if applicable)
- [ ] Inline documentation/PHPDoc complete

### Git
- [ ] Commits are atomic and well-described
- [ ] No secrets, passwords, or tokens committed
- [ ] Branch follows naming convention
- [ ] Branch is up to date with target branch
- [ ] No merge conflicts

### Review
- [ ] Code review completed (see checklist)
- [ ] All review comments addressed
- [ ] CI pipeline passes

### Deployment
- [ ] Feature flag added (if risky change)
- [ ] Migration is reversible
- [ ] Rollback plan documented (if needed)

---

## 19. Common Mistakes to Avoid

| Mistake | Why It's Wrong | Correct Approach |
|---|---|---|
| Putting SQL in Controllers | Violates MVC; couples HTTP to data access | Use Repository + Service |
| Not using prepared statements | SQL injection vulnerability | Always use PDO prepared statements |
| Using `$_GET`/`$_POST` directly | Unsanitized input; security risk | Use `Request` utility class with validation |
| Echoing user input directly | XSS vulnerability | Always use `htmlspecialchars()` or `e()` helper |
| Skipping CSRF for "internal" forms | CSRF vulnerability exists everywhere | Always include CSRF token |
| Making every class `public` | Breaks encapsulation | Use `private` by default |
| Putting business logic in views | Unmaintainable; hard to test | Move to Service layer |
| Using array instead of typed object | No type safety; harder to refactor | Create a Model/DTO class |
| Not validating file uploads | Security risk (arbitrary file upload) | Validate MIME, size, extension |
| Over-indexing | Slows writes; wastes storage | Test with `EXPLAIN`; add only needed indexes |
| Multiple queries in loops (N+1) | Massive performance hit | Use JOIN or batch queries |
| Not using transactions for related operations | Data inconsistency | Wrap in `beginTransaction`/`commit`/`rollback` |
| Hardcoding configuration | Security risk; environment-specific | Use `.env` and config files |
| Ignoring soft delete in queries | Shows deleted data to users | Always add `WHERE deleted_at IS NULL` |
| Using `require`/`include` manually | No autoloading; error-prone | Use Composer autoloader (PSR-4) |
| Using global state (`global` keyword) | Breaks encapsulation; untestable | Use Dependency Injection |
| Not regenerating session after login | Session fixation vulnerability | Always call `session_regenerate_id(true)` |

---

## 20. Future Extensibility Guidelines

### Extensibility Principles

1. **Interface-driven design** — always depend on abstractions
2. **Plugin architecture** — use Strategy pattern for pluggable behaviors
3. **Event-driven** — emit events for extensibility points
4. **Configurable workflows** — workflow definitions in config, not hardcoded
5. **Multi-tenancy ready** — design services to accept tenant context
6. **API-first** — all features accessible via REST API
7. **i18n ready** — all user-facing strings through language files
8. **Theme system** — views should support theme overrides
9. **Modular** — each module has its own Controllers, Services, Views

### Future Modules (Design Headroom)

- **AI/ML Integration** — predictive analytics, automated routing, fraud detection
  - Currently: keep analytics service flexible, use Strategy pattern
- **Mobile App API** — first-class API for native mobile apps
  - Currently: separate API routes, token-based auth
- **Real-time Notifications** — WebSocket/polling for live updates
  - Currently: keep notification service behind interface
- **Federation** — cross-jurisdiction incident sharing
  - Currently: use UUIDs globally, include jurisdiction in schema
- **Blockchain Audit** — immutable audit trail
  - Currently: append-only audit logs, cryptographic hashing
- **Advanced GIS** — GeoJSON, shapefile import, heat maps
  - Currently: latitude/longitude columns, GeoJSON support
- **SLA Management** — configurable service level agreements
  - Currently: priority system with SLA fields on categories
- **Payment Integration** — for service fees or fines
  - Currently: abstract payment service interface
- **Open311 Integration** — standard civic issue reporting protocol
  - Currently: API designed with Open311 compatibility in mind

### Migration Path for Breaking Changes
1. Deprecate in CHANGELOG with migration guide
2. Keep old API for 2 minor versions
3. Remove in next major version
4. Provide upgrade script

---

## 21. Module Specifications

### Authentication Module
- Login (email + password)
- Registration (with email verification)
- Password reset (email-based)
- "Remember Me" (secure token)
- Session management
- Multi-factor authentication (future)

### Users Module
- CRUD operations
- Profile management
- Account settings
- Activity log per user
- Account lockout after failed attempts

### Roles Module
- CRUD roles
- Assign permissions to roles
- Hierarchical roles (inherit permissions)

### Permissions Module
- Fine-grained permissions (CRUD per entity)
- Route-permission mapping in config
- Permission caching

### Citizens Module
- Citizen registration (self or admin)
- Citizen profile
- Incident history per citizen
- Communication preferences

### Incidents Module
- Incident reporting (with map, photos, categories)
- Reference number generation
- Status tracking
- Priority assignment
- SLA tracking
- History/activity timeline

### Incident Categories Module
- Hierarchical categories
- Configurable per institution
- SLA defaults per category

### Assignments Module
- Assign incidents to workers/teams
- Auto-assignment rules
- Reassignment workflow
- Load balancing

### Workflow Module
- Configurable status transitions
- State machine pattern
- Validation per transition
- Notifications on transitions

### Routing Module
- Auto-route to appropriate institution/department
- Rule-based routing
- Fallback routing
- Override capability

### Escalation Module
- Time-based escalation
- Priority-based escalation
- Escalation chain configuration
- Notification on escalation

### Notifications Module
- Email notifications
- SMS notifications (future)
- In-app notifications
- Notification preferences
- Templates

### Comments Module
- Add comments to incidents
- Internal vs public comments
- File attachments in comments
- @mentions

### Attachments Module
- File upload with validation
- Image thumbnails
- Document preview
- Secure file serving

### Maps Module
- Interactive map (Leaflet.js or Google Maps)
- Pin incident locations
- Cluster markers
- Geocoding (address to coordinates)

### Analytics Module
- Incident statistics
- Trends and patterns
- Institution performance metrics
- Response time analytics
- Custom date ranges

### Reports Module
- PDF report generation
- Excel/CSV export
- Scheduled reports
- Custom report builder

### Dashboards Module
- Citizen dashboard (my incidents)
- Worker dashboard (my assignments)
- Supervisor dashboard (team performance)
- Admin dashboard (system-wide metrics)
- Widget-based layout

### Audit Logs Module
- Automatic logging of all state changes
- Search/filter audit logs
- Audit report export
- Immutable log entries

### Settings Module
- System configuration
- Institution configuration
- Notification templates
- Workflow configuration
- SLA configuration

### Administration Module
- User management
- Role management
- Permission management
- System health monitoring
- Cache management
- Log viewer

### API Module
- RESTful API
- Token-based authentication
- Rate limiting
- API documentation
- Webhook support (future)

---

## Final Directive

> This AGENTS.md is the **single source of truth**. Every AI agent working on GCIRMS MUST comply with all rules, standards, and patterns defined herein. Violations undermine security, architecture, and maintainability. When in doubt, re-read this document. When still in doubt, ask. Never guess. Never compromise.

**Principal Software Architect**  
*Government-Scale Civic Incident Reporting and Management System*  
*Version 1.0.0 — July 2026*
