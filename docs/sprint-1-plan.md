# ThikThak — Sprint 1 Plan (my part)

**Stack:** Laravel (PHP 8.5) · Blade views · MariaDB (via XAMPP 8.5.12) · Git/GitHub
**My Sprint 1 work:**
- **Auth foundation:** add-ons, supporting work, *not counted* as a feature
- **F1 Service Catalog & Search:** counted feature
- **F4 Service Request Posting & Lifecycle:** counted feature

**Sprint length:** 1 week. Goals are submitted Saturday, the review is Friday (rule book §10).

> **Numbering:** Feature numbers follow [`feature-list-v2.md`](feature-list-v2.md), which is still pending faculty approval. Under §3, report auth as *supporting work*, never as a feature.
>
> **Counted features: 2 (F1, F4).** That's the pace needed for 8 over 4 sprints. The roadmap's other Sprint 1 features go to the teammates: **F13 Farhaan, F14 Raisa, F17 Rubaiyat**. That gives the team its 5 for the sprint. Each has their own file with their spec and AI prompts: [`team/Sprint1/farhaan-f13.md`](team/Sprint1/farhaan-f13.md), [`team/Sprint1/raisa-f14.md`](team/Sprint1/raisa-f14.md), [`team/Sprint1/rubaiyat-f17.md`](team/Sprint1/rubaiyat-f17.md).
>
> *Changed on 2026-11-07:* F13 moved to Farhaan and I took F4 instead.

---

## 1. Sprint goals (specific and measurable, for the Saturday submission)

| # | Goal | Done when |
|---|---|---|
| G0 | Project setup | Laravel app created, pushed to GitHub, faculty + 3 teammates added as collaborators, README has setup steps |
| G1 | Auth foundation (add-on, not counted) | A customer or provider can register, log in and log out. Each role lands on its own dashboard. Wrong-role access returns 403. Users can edit account info and change their password. Feature tests pass. |
| G2 | **F1: Service Catalog & Search** | An admin can create, edit, deactivate and delete categories and services. Customers can browse categories, search services by keyword, and filter by category and price range, with sorting and pagination. Feature tests pass. |
| G3 | **F4: Service Request Posting & Lifecycle** | A customer can post a request (service, description, budget range, deadline, location, up to 5 photos), see their requests with status, edit one while it's Open, and cancel it while it's Open or Quoted. Requests past their deadline expire automatically. Feature tests pass. |

---

## 2. Auth foundation (add-ons, supporting work, not counted)

Every feature (F1–F20) depends on this, so it comes first. It's reported in the SRS as supporting functionality.

**Scope:**
- Register as **Customer** or **Provider**: name, email, BD phone, password + confirmation, role. Provider registration also creates an empty `provider_profiles` row. The base table (`id`, `user_id`) is part of this foundation, so Farhaan (F13), Raisa (F14) and Rubaiyat (F17) can reference it from day one.
- Login with "remember me", logout, and a rate limit on repeated failed logins.
- **Role-based access:** middleware limits routes to `customer` / `provider` / `admin`, and each role is redirected to its own dashboard after login.
- **Account settings:** view and edit name, phone, area/address and profile photo, and change password (current password required).
- Admin accounts are created only by a seeder. There is no public admin sign-up.
- *Stretch, if time allows (otherwise Sprint 2):* email verification, forgot/reset password (use `MAIL_MAILER=log` locally), deactivate account.

**Acceptance criteria and test scenarios (normal / invalid / boundary, §12):**

| Case | Input | Expected |
|---|---|---|
| Normal | Valid customer registration | Account created, logged in, redirected to customer dashboard |
| Normal | Valid provider registration | Account + empty provider profile created, redirected to provider dashboard |
| Invalid | Email already registered | Validation error, no account created |
| Invalid | Phone `0123456789` (not BD format) | Validation error (rule: `^01[3-9]\d{8}$`) |
| Boundary | Password 7 chars vs 8 chars | 7 rejected, 8 accepted |
| Invalid | Wrong password at login | Generic "credentials do not match" error (doesn't reveal which field was wrong) |
| Security | 6 failed logins in a minute | Throttled (HTTP 429) |
| Security | Customer opens `/provider/dashboard` | 403 Forbidden |
| Security | Logged-out user opens `/account` | Redirected to login |
| Security | Password stored in DB | Hashed (bcrypt), never plain text |
| Invalid | Profile photo is a 5 MB PDF | Rejected (images only, max 2 MB) |

---

## 3. F1: Service Catalog & Search *(counted feature)*

**Scope:**
- Two-level catalog: **Category** (e.g. AC Repair) → **Services** (e.g. AC Servicing, Gas Refill), each service with an indicative price range in BDT. The price range supports the "transparent pricing" goal in the proposal.
- **Admin management:** create, edit and delete categories and services, toggle active/inactive, and set display order.
- **Customer browsing:** category grid → category page listing its active services.
- **Search:** keyword search over service and category names and descriptions, a category filter, a **price-range filter**, sort (name / price), pagination, and an empty-state message.
- **Price-filter rule:** a service matches when its range overlaps the filter: `price_min ≤ filter_max` AND `price_max ≥ filter_min`.
- Seeded starter data: Plumbing, Electrical, AC Repair, Cleaning, Painting, Tutoring, Photography, Event Management.

**Business rules:** A category that has services can't be deleted; deactivate it instead. Inactive items are hidden from customers. Min price ≤ max price.

**Acceptance criteria and test scenarios:**

| Case | Input | Expected |
|---|---|---|
| Normal | Search "ac" | Returns AC-related services (case-insensitive) |
| Normal | Filter category = Cleaning | Only cleaning services shown |
| Normal | Price filter 500–1500 | Only services whose price range overlaps 500–1500 |
| Boundary | Empty search box | All active services, paginated |
| Boundary | Price filter min > max | Validation error, no results query run |
| Normal | Search "xyzabc" | "No services found" empty state |
| Invalid | Duplicate category name | Validation error |
| Invalid | Service min price > max price | Validation error |
| Business rule | Delete a category that has services | Blocked, with a message suggesting deactivation |
| Business rule | Inactive category/service | Hidden from customers, still visible to admin |
| Security | Customer opens `/admin/categories` | 403 Forbidden |
| Security | Search input `' OR 1=1 --` | Treated as plain text (query parameter binding) |

**Dependency note for teammates:** The `services` table from F1 will be referenced by F2, F4, F5, F6, F13 and F18.

---

## 4. F4: Service Request Posting & Lifecycle *(counted feature)*

**Scope:**
- **Post a request:** the customer picks a service from the F1 catalog and enters a title, description, budget range in BDT (min and max), deadline date, location (area name plus a map pin for latitude/longitude) and up to 5 photos.
- **My requests:** a list of the customer's own requests with a status filter and pagination, and a detail page with the photos and current status.
- **Edit** while the request is Open. **Cancel** while it's Open or Quoted, with an optional reason.
- **Status lifecycle:** Open → Quoted → Assigned → Completed, plus Cancelled and Expired. In Sprint 1 only Open, Cancelled and Expired actually happen. F5 (Sprint 2) moves a request to Quoted on the first quote and to Assigned when a quote is accepted, and F15 (Sprint 3) completes it. The allowed moves live in the model, so those features call `transitionTo()` instead of setting the status directly.
- **Auto-expiry:** an Artisan command `requests:expire` marks Open or Quoted requests whose deadline has passed as Expired. It's scheduled hourly in `routes/console.php`. Locally, run `php artisan schedule:work`, or run the command by hand.

**Business rules:**
- Only customers can post a request. Only the owner can view, edit or cancel it (enforced with a Laravel Policy). Providers will see open requests through F5's job board in Sprint 2.
- Only *active* catalog services can be chosen.
- Budget: both amounts greater than 0, and min ≤ max.
- Deadline: at least tomorrow, at most 60 days ahead.
- Photos: at most 5, each JPG/PNG/WEBP and max 2 MB.
- Editing is allowed only while the status is Open. Once the first quote arrives, the request is locked.
- Cancelling is allowed only while Open or Quoted.
- Any other status move (for example Expired → Open, or Completed → Cancelled) is rejected by the model.

**Acceptance criteria and test scenarios:**

| Case | Input | Expected |
|---|---|---|
| Normal | Post a valid request with 2 photos | Saved as Open, photos stored, shown in "My requests" |
| Invalid | Budget min ৳2000, max ৳1000 | Validation error |
| Invalid | Budget min ৳0 | Validation error |
| Boundary | Deadline today / tomorrow / 60 days / 61 days ahead | Today and 61 days rejected, tomorrow and 60 days accepted |
| Boundary | 5 photos / 6 photos | 5 accepted, 6 rejected |
| Invalid | A 3 MB photo, or a `.pdf` | Rejected |
| Invalid | An inactive service | Validation error |
| Business rule | Edit an Open request | Saved |
| Business rule | Edit a Quoted request | Blocked with a message |
| Business rule | Cancel an Assigned request | Blocked |
| Business rule | Run `requests:expire` when one Open request's deadline was yesterday and another's is next week | First becomes Expired, second stays Open |
| Business rule | `transitionTo('open')` on an Expired request | Rejected (exception) |
| Security | Customer A opens customer B's request | 403 Forbidden |
| Security | Provider sends `POST /requests` | 403 Forbidden |

**Dependency notes for teammates:**
- **F5 (Quotation, Sprint 2):** reads Open and Quoted requests that match the provider's services and area, and calls `transitionTo('quoted')` / `transitionTo('assigned')`.
- **F2 / F18:** use the request's `latitude` and `longitude`.
- **F12 (Messaging):** one conversation per request.

---

## 5. Data model (my tables)

```
users
  id, name, email (unique), phone (unique), password (hashed),
  role ENUM('customer','provider','admin') DEFAULT 'customer',
  area NULL, address NULL, avatar_path NULL, is_active BOOL DEFAULT true,
  email_verified_at NULL, remember_token, timestamps

service_categories                        -- F1
  id, name (unique), slug (unique), description NULL, icon NULL,
  is_active BOOL DEFAULT true, sort_order INT DEFAULT 0, timestamps

services                                  -- F1
  id, service_category_id FK → service_categories (restrict on delete),
  name, slug (unique), description NULL,
  price_min DECIMAL NULL, price_max DECIMAL NULL,
  is_active BOOL DEFAULT true, timestamps
  UNIQUE (service_category_id, name)

provider_profiles                         -- base only, part of the auth foundation
  id, user_id FK → users (unique, cascade on delete), timestamps
  -- Farhaan (F13), Raisa (F14) and Rubaiyat (F17) add their own columns
  -- in their own migrations.

service_requests                          -- F4
  id, customer_id FK → users (cascade), service_id FK → services (restrict),
  title, description TEXT,
  budget_min DECIMAL(10,2), budget_max DECIMAL(10,2), deadline DATE,
  area_name, latitude DECIMAL(10,7) NULL, longitude DECIMAL(10,7) NULL,
  status ENUM('open','quoted','assigned','completed','cancelled','expired') DEFAULT 'open',
  cancelled_at NULL, cancel_reason NULL, timestamps
  INDEX (status, deadline)

service_request_photos                    -- F4
  id, service_request_id FK (cascade), path, sort_order TINYINT DEFAULT 0, timestamps
```

**Relationships:**
- `ServiceCategory hasMany Service` · `Service belongsTo ServiceCategory`
- `User hasOne ProviderProfile` · `ProviderProfile belongsTo User`
- `User hasMany ServiceRequest` (as customer) · `ServiceRequest belongsTo User` (`customer`) and `belongsTo Service`
- `ServiceRequest hasMany ServiceRequestPhoto`

**Team agreement needed:** `users` is shared by everyone. Provider-only data goes in `provider_profiles`. I create the base table, and each teammate adds columns or tables in their *own* migrations rather than adding columns to `users`.

---

## 6. MVC mapping (expect viva questions on this, §6)

| Layer | Files |
|---|---|
| **Model** | `User`, `ServiceCategory`, `Service`, `ProviderProfile` (base), `ServiceRequest` (incl. `isEditable()`, `canBeCancelled()`, `transitionTo()`, scopes `open()` and `overdue()`), `ServiceRequestPhoto`. Relationships, casts, scopes like `active()` and `priceBetween()` |
| **View** | `layouts/app`, `auth/register`, `auth/login`, `account/edit`, `admin/categories/*`, `admin/services/*`, `catalog/index`, `catalog/category`, `customer/requests/index`, `customer/requests/create`, `customer/requests/edit`, `customer/requests/show` |
| **Controller** | `Auth/RegisterController`, `Auth/LoginController`, `AccountController`, `Admin/ServiceCategoryController`, `Admin/ServiceController`, `CatalogController`, `Customer/ServiceRequestController` (index, create, store, show, edit, update, cancel) |
| **Validation** | Form Requests: `RegisterRequest`, `UpdateAccountRequest`, `StoreCategoryRequest`, `StoreServiceRequest`, `CatalogSearchRequest`, `StoreServiceRequestRequest`, `UpdateServiceRequestRequest` |
| **Access control** | `EnsureRole` middleware (alias `role` in `bootstrap/app.php`) for role checks · `ServiceRequestPolicy` for "only the owner can view, edit or cancel" |
| **Scheduled job** | `app/Console/Commands/ExpireServiceRequests` (`requests:expire`), scheduled in `routes/console.php` |
| **Routes** | `routes/web.php` grouped by `auth` + `role:...` middleware. Public catalog pages sit outside the `auth` group |

**Why hand-written auth instead of Laravel Breeze:** Breeze generates dozens of files you didn't write. You own auth and will be asked to trace a login request in the viva. Laravel's own `Auth`, `Hash`, `Password` and session features are core framework, not a third-party package (§7).

**Why the status rules live in the Model:** `transitionTo()` keeps every allowed status move in one place. The controller, the expiry command, and later F5 and F15 all go through it, so none of them can put a request into an impossible state, and the rules are easy to unit-test. This is a good example to give when the viva asks "why is this code in this layer?"

---

## 7. Day-by-day task plan

| Day | Tasks | Suggested commits |
|---|---|---|
| Sat | Submit goals · install tools · create the Laravel app · GitHub repo · add faculty + teammates | `Initialize Laravel project for ThikThak` |
| Sun | Users migration (role, phone, area) + base `provider_profiles` table, `EnsureRole` middleware, admin seeder · register + login + logout with throttling · **push so teammates can start** | `Add role and phone fields to users`, `Add base provider profiles table`, `Add role-based access middleware`, `Implement registration and login` |
| Mon | Account settings, photo upload, change password · feature tests for auth | `Add account settings page`, `Add feature tests for authentication` |
| Tue | F1: catalog migrations, models, seeder, admin management screens · **push so Farhaan can link services** | `Add service catalog models and seeder`, `Add admin category and service management` |
| Wed | F1: customer browse, search, category + price filters, sort, paginate · F1 tests · F4: migrations, models, status rules | `Add service search with category and price filters`, `Add catalog feature tests`, `Add service request tables and status rules` |
| Thu | F4: post-request form (service, budget, deadline, location, photos) · My requests list + detail · edit while Open · cancel | `Let customers post service requests with photos`, `Add my requests list and detail page`, `Allow editing and cancelling open requests` |
| Fri | F4: expiry command + schedule · `ServiceRequestPolicy` · F4 tests · review teammates' PRs · README · push everything · sprint report | `Expire overdue service requests on a schedule`, `Add service request feature tests` |

**This is a tight week.** If something has to give:
1. Auth stretch items (email verification, password reset) move to Sprint 2.
2. The Leaflet map picker for the request location can be replaced with an area dropdown plus seeded coordinates. F2 still gets lat/lng.
3. Anything left unfinished is still pushed on Friday and reported as "partial" with the reason (§10, §11). Don't hide it.

---

## 8. Sprint 1 team table

Roadmap Sprint 1 features: **F1, F4, F13, F14, F17**, plus the auth add-ons.

| Member | Work item | Counted? | Status at Friday review |
|---|---|---|---|
| Me (Saad) | Auth foundation (add-ons) | No, supporting work | |
| Me (Saad) | F1 Service Catalog & Search | Yes | |
| Me (Saad) | F4 Service Request Posting & Lifecycle | Yes | |
| Farhaan | F13 Professional Profile & Portfolio | Yes | |
| Raisa | F14 Availability & Schedule Management | Yes | |
| Rubaiyat | F17 Provider Verification (KYC) | Yes | |
