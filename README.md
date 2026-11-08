# ThikThak

**ThikThak** is a local service marketplace for Bangladesh. Customers find, compare and book trusted service providers such as electricians, plumbers, AC technicians, cleaners and tutors. Providers manage their profile, schedule, quotes and earnings, and an admin verifies providers and keeps the platform safe.

It's our group project for **CSE 470 (Software Engineering)**, built with Laravel in a strict MVC structure over four one-week Scrum sprints.

---

## Team

| Member | Sprint 1 work |
|---|---|
| Saad Bin Ahsan Khan | Supporting work (registration, login, roles, account settings) · F1 Service Catalog & Search · F4 Service Request Posting & Lifecycle |
| Farhaan | F13 Professional Profile & Portfolio |
| Rubaiyat | F14 Availability & Schedule Management |
| Raisa | F17 Provider Verification (KYC) |

Each member's Sprint 1 spec is in [`docs/team/Sprint1/`](docs/team/Sprint1/).

---

## Tech stack

| Part | Choice |
|---|---|
| Framework | Laravel 13 (MVC) |
| Language | PHP 8.5 |
| Database | MariaDB 10.11, through XAMPP 8.5.12 |
| Views | Blade templates |
| Styling and assets | Tailwind CSS 4, built with Vite |
| Tests | PHPUnit 12 (feature and unit tests) |
| Version control | Git and GitHub, one branch per feature, Pull Requests into `main` |

Everything we use is free. Payments will only ever run in a gateway's free sandbox mode.

---

## Features

The 20 features below follow our revised feature list, which is still waiting for faculty approval ([`docs/feature-list-v2.md`](docs/feature-list-v2.md)).

| # | Feature | Sprint | Owner | Status |
|---|---|---|---|---|
| F1 | Service Catalog & Search | 1 | Saad | Done (in review) |
| F2 | Location-Based Provider Discovery | 2 | | Planned |
| F3 | Provider Comparison | 2 | | Planned |
| F4 | Service Request Posting & Lifecycle | 1 | Saad | Done (in review) |
| F5 | Quotation & Bidding | 2 | | Planned |
| F6 | Instant Service Booking | 3 | | Planned |
| F7 | Appointment Scheduling, Rescheduling & Cancellation | 2 | | Planned |
| F8 | Online Payments & Digital Receipts | 3 | | Planned |
| F9 | Promo Codes & Discounts | 4 | | Planned |
| F10 | Ratings & Reviews | 4 | | Planned |
| F11 | Real-Time Notification Center | 3 | | Planned |
| F12 | In-App Messaging | 2 | | Planned |
| F13 | Professional Profile & Portfolio | 1 | Farhaan | In progress |
| F14 | Availability & Schedule Management | 1 | Rubaiyat | In progress |
| F15 | Job Status Tracking | 3 | | Planned |
| F16 | Earnings & Payout Management | 4 | | Planned |
| F17 | Provider Verification (KYC) | 1 | Raisa | In progress |
| F18 | Smart Matching & Dispatch Engine | 3 | | Planned |
| F19 | Dispute & Complaint Resolution | 4 | | Planned |
| F20 | Admin Analytics & User Moderation | 4 | | Planned |

**Supporting work (not counted as features, rule book §3):** customer and provider registration, login and logout with "remember me" and a rate limit, email verification, password reset, role-based access (customer, provider, admin), account settings (details, profile photo, password, deactivation) and a seeded admin account. All done.

---

## Getting started

The full step-by-step guide for Windows, including installing every tool, is [`docs/ThikThak_Setup_Guide.pdf`](docs/ThikThak_Setup_Guide.pdf). The short version:

**You need:** XAMPP 8.5.12 (PHP 8.5 + MariaDB), Composer, Node.js 24 or newer, and Git. The PHP extensions `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql` and `zip` must be switched on in `php.ini`.

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Get the code and install its packages:
   ```bash
   git clone https://github.com/saad0711/thikthak.git
   cd thikthak
   composer install
   npm install
   copy .env.example .env
   php artisan key:generate
   ```
   On macOS or Linux, use `cp` instead of `copy`.
3. `.env.example` is already set up for XAMPP: database `thikthak`, user `root`, no password. Create the tables and test data:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```
   If the `thikthak` database doesn't exist yet, `migrate` offers to create it. Answer **yes**.
   To wipe the database and start again with fresh test data, run `php artisan migrate:fresh --seed`.
4. Run the app in two terminals:
   ```bash
   php artisan serve
   ```
   ```bash
   npm run dev
   ```
   Then open **http://127.0.0.1:8000**.

**Test accounts** (created by the seeder, local only). Every one uses the password `password`:

| Role | Email |
|---|---|
| Admin | `admin@thikthak.test` |
| Customer | `customer@thikthak.test` (has a few demo service requests) |
| Provider | `provider@thikthak.test` |

**Emails while developing:** nothing is really sent. Verification and password-reset emails are written to `storage/logs/laravel.log`, so copy the link from the newest message there.

**Expiring old requests (F4):** requests whose deadline has passed are expired by `php artisan requests:expire`, which the scheduler runs every hour. Locally, keep `php artisan schedule:work` running, or run the command by hand.

**Run the tests:**
```bash
php artisan test
```
Tests use an in-memory SQLite database (set in `phpunit.xml`), so they never touch your real data.

---

## Where things live (MVC)

| Layer | Folder |
|---|---|
| **Models** (data and business rules) | `app/Models` |
| **Views** (Blade templates) | `resources/views` |
| **Controllers** (handle requests) | `app/Http/Controllers` |
| Validation | `app/Http/Requests` (Form Requests) |
| Permission checks | `app/Policies` and `app/Http/Middleware` |
| Routes | `routes/web.php`, plus the hourly schedule in `routes/console.php` |
| Database tables | `database/migrations`, with test data in `database/seeders` |
| Fixed lists (statuses, roles) | `app/Enums` |
| Scheduled jobs | `app/Console/Commands` |
| App settings (areas, photo limits) | `config/thikthak.php` |
| Tests | `tests/Feature` and `tests/Unit` |

Controllers stay short, validation lives in Form Requests, and business rules live in Models. Blade views only display data.

---

## How we work

- **Sprints:** four one-week sprints. Goals go in on Saturday and the review is on Friday.
- **Branches:** each feature gets its own branch, for example `feature/f13-provider-profile`. Nobody pushes straight to `main`.
- **Pull Requests:** every change goes into `main` through a Pull Request that another teammate reviews. `php artisan test` must pass first.
- **Commits:** small steps, clear messages, each person from their own GitHub account.
- **Database changes:** always a new migration. Never edit a migration that's already on `main`.
- **Packages:** ask the team before adding one. A package that provides a whole feature needs faculty approval (rule book §7).
- **Secrets:** this repo is public. Never commit `.env`, passwords, API keys, or real NID or personal photos.

---

## Documentation

| Document | What it is |
|---|---|
| [`docs/feature-list-v2.md`](docs/feature-list-v2.md) | The 20 features with workflows and business rules, plus the sprint roadmap |
| [`docs/sprint-1-plan.md`](docs/sprint-1-plan.md) | Sprint 1 goals, specs, test cases and data model (supporting work, F1, F4) |
| [`docs/team/Sprint1/`](docs/team/Sprint1/) | Sprint 1 specs for F13, F14 and F17 |
| [`docs/ThikThak_Setup_Guide.pdf`](docs/ThikThak_Setup_Guide.pdf) | Step-by-step setup guide for Windows |
| [`docs/ai-use-log.md`](docs/ai-use-log.md) | Saad's AI-use log (each member keeps their own in `docs/ai-use/`) |

---

## AI use

The course (rule book §8) allows AI for learning, explanations, debugging, reviews, test ideas and documentation, but not for generating whole features. Every team member logs how they used AI, and those logs become our AI-use statement in the final submission.

---

## Third-party packages

| Package | Why |
|---|---|
| `laravel/framework`, `laravel/tinker` | The framework, plus its interactive console |
| `phpunit/phpunit`, `mockery/mockery`, `fakerphp/faker` | Testing, with fake data for tests and seeders (dev only) |
| `laravel/pint`, `laravel/pail`, `nunomaduro/collision` | Code formatting, log viewing and readable error output (dev only) |
| `tailwindcss`, `@tailwindcss/vite`, `vite`, `laravel-vite-plugin` | Building CSS and JavaScript |
| `concurrently` | Comes with Laravel; runs several dev processes in one terminal |

New packages will be added to this table as features need them (for example Leaflet with OpenStreetMap for maps, and a payment gateway's sandbox).
