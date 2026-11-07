# ThikThak Sprint 1 — Farhaan: F13 Professional Profile & Portfolio

From Saad. This file is everything you need for Sprint 1: what the team is doing, your feature spec, your test cases, and the AI prompts to use while you build it.

---

## The team this sprint

| Member | Work | Counted? |
|---|---|---|
| Saad | **Add-ons:** registration (customer/provider), login/logout with "remember me" and a rate limit, role-based access, account settings with profile photo, seeded admin account | No, supporting work |
| Saad | **F1 Service Catalog & Search** and **F4 Service Request Posting & Lifecycle** | Yes |
| **You (Farhaan)** | **F13 Professional Profile & Portfolio** | Yes |
| Rubaiyat | F14 Availability & Schedule Management | Yes |
| Raisa | F17 Provider Verification (KYC) | Yes |

Your branch: **`feature/f13-provider-profile`**

Feature numbers follow the shared doc (*ThikThak — Feature List & Tech Stack*), which is still waiting for faculty approval.

---

## Before you start

1. **Set up your PC** with `ThikThak_Setup_Guide.pdf`, all the way to Step 12. Use the latest copy of the PDF, which has the Smart App Control fix.
2. **What you get from Saad, and when:**

   | Push | What's in it | What you can start |
   |---|---|---|
   | Early in the week | Laravel project, `layouts/app`, `users` with roles, login/registration, the `EnsureRole` middleware (`role:provider`, `role:admin`), a base `provider_profiles` table (`id`, `user_id`) with a `ProviderProfile` model, a seeded admin account | Profile details, location, completeness, portfolio, certifications |
   | Midweek | F1: `service_categories` and `services` | Services offered |

   Until the first push, read this file, plan your tables, and sketch your screens on paper.
3. **Team rules for code:**
   - Work only on your own branch, and open a Pull Request into `main`. Someone else reviews it before it's merged.
   - Commit small steps with clear messages, from your own GitHub account. The faculty checks each person's commits.
   - Don't add columns to `users`. Add columns to `provider_profiles` or create your own tables, always in **your own new migration**. Never edit someone else's migration.
   - `ProviderProfile` is shared by all four of us. Rubaiyat and Raisa put their methods in their own traits; you own the profile itself, but keep your additions tidy and pull often.
   - No logic in Blade views. Validation goes in Form Requests, permission checks in Policies or middleware, business rules in Models. Controllers stay short.
   - Ask in the group before `composer require` or `npm install <package>`. A package that does a whole feature for you needs faculty approval (rule book §7).
   - `php artisan test` must pass before you open a PR.
4. **Sprint rhythm:** goals go in on Saturday and the review is on Friday. If something isn't finished, push what you have on Friday and report it as partial, with the reason.

---

## Using AI (rule book §8)

The course allows AI for learning, explanations, debugging, reviewing and test ideas. It does **not** allow AI to generate a whole feature that you then submit. In the viva (10 of the 15 marks), the faculty can ask you to explain, change or debug your code live.

So the prompts in this file don't ask the AI to build your feature. They set it up as a **tutor and reviewer**: it explains each step, you write the code, it reviews what you wrote, gives hints when you're stuck, and quizzes you before the viva. You still finish the whole feature, and you understand every line.

- Any AI chat works. The free tier is enough, so don't pay for anything.
- Start a new chat with **your starter prompt**, then use the **step prompts** for each step.
- **Keep your AI-use log** in `docs/ai-use/farhaan.md`, with the columns Date | Tool | What it helped with | What I did myself. The final submission needs an AI-use statement, and this is where yours comes from.
- Never let an AI write your commit messages or sprint reports, and never paste passwords or `.env` contents into a chat.

---

## Your feature: F13 Professional Profile & Portfolio

### What you build
- **Profile details:** headline (e.g. "Certified AC technician"), bio, years of experience. The profile photo already comes from Saad's account settings (`users.avatar_path`).
- **Service area:** base location as latitude/longitude picked on a map (Leaflet + OpenStreetMap, both free), plus an area name and a service radius in km. If the map takes too long, use an area dropdown with seeded coordinates.
- **Services offered:** the provider picks services from the F1 catalog and sets their own price for each, fixed or per hour.
- **Certifications:** title, issuer, year, and an optional file (image or PDF). These are public. Checking the NID is Raisa's F17, not yours.
- **Portfolio gallery:** photos of past work with captions, which the provider can reorder.
- **Profile completeness score**, calculated in the model:

  | Item | Weight |
  |---|---|
  | Profile photo | 10% |
  | Headline + bio | 20% |
  | At least 1 service offered | 25% |
  | Base location + radius | 20% |
  | At least 1 portfolio item | 15% |
  | At least 1 certification | 10% |

- **Public profile page** (`/providers/{id}`), visible to anyone: headline, bio, services with prices, service area, certifications, portfolio, member-since date, and Raisa's verified badge. "Jobs completed" shows 0 until F15 exists, and the rating shows "No ratings yet" until F10 exists.

### Business rules
- A provider can't go online for jobs below **60% completeness**. You provide `completeness()` and `canGoOnline()`, and Rubaiyat calls `canGoOnline()`.
- A provider can only offer *active* catalog services, each one only once, at a price greater than 0.
- Service radius is 1–25 km. Experience is 0–50 years.
- At most 12 portfolio items. Each image is JPG/PNG/WEBP, max 2 MB.
- Only the owning provider can edit their profile, services, portfolio or certifications (a Policy).
- The public profile of a suspended or inactive provider returns 404.

### Your tables
```
provider_profiles   -- your migration ADDS these columns to Saad's base table
  headline NULL, bio TEXT NULL, experience_years TINYINT DEFAULT 0,
  latitude DECIMAL(10,7) NULL, longitude DECIMAL(10,7) NULL,
  area_name NULL, service_radius_km TINYINT DEFAULT 5

provider_service    -- pivot
  id, provider_profile_id FK (cascade), service_id FK → services,
  price DECIMAL(10,2), price_unit ENUM('fixed','per_hour') DEFAULT 'fixed', timestamps
  UNIQUE (provider_profile_id, service_id)

certifications
  id, provider_profile_id FK (cascade), title, issuer,
  year_obtained SMALLINT, file_path NULL, timestamps

portfolio_items
  id, provider_profile_id FK (cascade), image_path,
  caption NULL, sort_order INT DEFAULT 0, timestamps
```

### Your MVC files
| Layer | Files |
|---|---|
| Model | `ProviderProfile` additions (relationships, `completeness()`, `canGoOnline()`), `Certification`, `PortfolioItem` |
| View | `provider/profile/edit`, `provider/services/index`, `provider/portfolio/index`, `provider/certifications/index`, `providers/show` (public) |
| Controller | `Provider/ProfileController`, `Provider/ServiceOfferingController`, `Provider/PortfolioController`, `Provider/CertificationController`, `PublicProviderController` |
| Validation | `UpdateProviderProfileRequest`, `StoreServiceOfferingRequest`, `StorePortfolioItemRequest`, `StoreCertificationRequest` |
| Access | `ProviderProfilePolicy` |

### Order of work
1. Profile details and location, then `completeness()` and `canGoOnline()`. **Open a small first PR with these two methods early in the week**, because Rubaiyat needs them.
2. Portfolio and certifications (file uploads).
3. Services offered, once Saad's F1 tables are on `main`.
4. Public profile page, then the policy and tests.

### What others need from you
- **Rubaiyat (F14):** `canGoOnline()`, as early as possible.
- **Raisa (F17):** a place on the public page for `<x-verified-badge :profile="$profile" />`.
- **F2 and F18 (later sprints):** `latitude`, `longitude` and `service_radius_km`.

### Test cases (normal / invalid / boundary, rule book §12)
| Case | Input | Expected |
|---|---|---|
| Normal | Offer "AC Servicing" at ৳800 fixed | Shown on the public profile with its price |
| Invalid | Price 0 or negative | Validation error |
| Invalid | Offer the same service twice | Validation error |
| Invalid | Offer an inactive service | Validation error |
| Boundary | Radius 0 / 1 / 25 / 26 km | 0 and 26 rejected, 1 and 25 accepted |
| Boundary | Experience 0 / 50 / 51 years | 51 rejected |
| Invalid | Portfolio upload: 5 MB image, or a `.exe` | Rejected |
| Boundary | Upload the 13th portfolio item | Rejected (max 12) |
| Business rule | Photo + headline + bio + 1 service | Completeness 55%, `canGoOnline()` false |
| Business rule | Every item filled in | Completeness 100%, `canGoOnline()` true |
| Security | Provider A edits Provider B's portfolio item | 403 Forbidden |
| Security | Customer opens `/provider/profile/edit` | 403 Forbidden |
| Normal | Guest opens a provider's public profile | Visible, rating shows "No ratings yet" |
| Business rule | Suspended provider's public profile | 404 |

---

## Your starter prompt

Paste this into a new chat once, at the start.

```text
You are my tutor and code reviewer for a university Software Engineering project (CSE 470). Read all of this before you answer.

PROJECT
ThikThak is a local service marketplace for Bangladesh: customers find and book providers such as electricians, plumbers and tutors. Four students build it. I'm Farhaan, and this sprint I own feature F13. My teammate Saad builds the add-ons (registration, login, roles, account settings), F1 Service Catalog and F4 Service Request Posting. Rubaiyat builds F14 Availability and Raisa builds F17 Provider Verification.

STACK (fixed, don't suggest alternatives)
- Laravel 13, PHP 8.5 (XAMPP on Windows), MariaDB 10.11, Blade views, Tailwind CSS via Vite, Eloquent, PHPUnit feature tests.
- No React, Vue, Livewire or Inertia. No paid tools or paid packages.
- Strict MVC: validation in Form Requests, permission checks in Policies or middleware, business rules in Models, short controllers, no logic in Blade.
- New Composer or npm packages need team approval first.

COURSE RULE ON AI (important)
I'm not allowed to submit an AI-generated feature. I must write the code myself and be able to explain, change and debug it live in a viva. So:
1. Don't write whole files or the whole feature for me.
2. Go one small step at a time. For each step, tell me what to build, which file it goes in, and why it belongs in that MVC layer. Then wait for me to write it.
3. When I paste my code, review it: point out bugs, security problems and MVC mistakes, and explain why. Ask me questions rather than rewriting it.
4. If I'm still stuck after a hint, show a short example (15 lines at most) of the idea, explain each line, and let me adapt it.
5. Never write git commit messages or sprint reports for me.

WHAT ALREADY EXISTS (built by Saad)
- users: id, name, email, phone, password, role (customer/provider/admin), area, address, avatar_path, is_active. Login and registration work.
- EnsureRole middleware, used in routes as role:provider or role:admin.
- provider_profiles base table (id, user_id unique, timestamps) and a ProviderProfile model. User hasOne ProviderProfile. Each provider gets an empty profile row when they register.
- service_categories and services from F1 (services have name, price_min, price_max, is_active).
- A shared Blade layout, layouts/app.

MY FEATURE: F13 Professional Profile & Portfolio
- Profile details: headline, bio, years of experience. Profile photo already exists as users.avatar_path.
- Service area: latitude/longitude from a Leaflet + OpenStreetMap map pin, area name, service radius in km.
- Services offered: pick active services from the catalog with my own price, fixed or per hour (pivot table provider_service with price and price_unit).
- Certifications: title, issuer, year, optional image or PDF file.
- Portfolio: up to 12 images with captions, reorderable.
- Completeness score in the model: photo 10%, headline + bio 20%, at least 1 service 25%, location + radius 20%, at least 1 portfolio item 15%, at least 1 certification 10%. canGoOnline() is true at 60% or more.
- Public page /providers/{id}: everything above plus member-since date and a verified badge component from F17. Suspended or inactive providers return 404.
- Rules: radius 1-25 km, experience 0-50 years, price > 0, each service offered once, only active services, images JPG/PNG/WEBP up to 2 MB, only the owner can edit (a Policy).
- I add my columns to provider_profiles in my own new migration.

Start by asking me what I've already built. Then give me step 1 only, with the reasoning. Wait for my code before moving on.
```

---

## Step prompts

Use these in the same chat after your starter prompt. Replace the parts in `[brackets]`.

**1. Check a plan before coding**
```text
Before I write any code for [step], here's my plan: [your plan in a few lines]. What's missing or wrong? Don't write the code.
```

**2. Review my code**
```text
Here's my [migration / model / controller / Form Request / view] for [step]:
[paste your code]
Review it against my feature rules. List each problem with the reason, and ask me to fix it. Don't rewrite the file.
```

**3. Stuck**
```text
I'm stuck on [what you're trying to do]. Here's what I tried: [paste code], and here's what happened: [result or error]. Give me a hint first. If I'm still stuck after that, show a short example and explain each line.
```

**4. Error message**
```text
I get this error when I [what you did]:
[paste the full error, including the first lines of the stack trace]
Here is the code involved: [paste]. Explain what the error means and how I could have found the cause myself, then tell me the fix.
```

**5. Tests**
```text
Here are my test cases: [paste the test table from my file]. Help me write the first one as a PHPUnit feature test and explain every part. I'll write the rest myself, then paste them for review.
```

**6. Review before a Pull Request**
```text
Here's the full diff for my Pull Request: [paste the output of git diff main]. Review it for bugs, missing validation or permission checks, file-upload security, mass assignment, N+1 queries, and anything in the wrong MVC layer.
```

**7. Viva practice**
```text
Quiz me like a viva examiner on my feature, one question at a time, 10 questions. Include: where each Model, View and Controller file is; why a rule lives in that layer; tracing one request from route to view; and one live change, like changing the portfolio limit from 12 to 10. Tell me what I got wrong.
```

---

## Checklist before you open a Pull Request

- [ ] `php artisan test` passes, with tests for the normal, invalid and boundary cases in your table.
- [ ] You merged the latest `main` into your branch, and the app still runs.
- [ ] Your table changes are in your own new migrations. You didn't edit anyone else's.
- [ ] No logic in Blade. Validation is in Form Requests, permission checks in the Policy.
- [ ] No `.env` or personal photos in the commit.
- [ ] Commit messages say what changed (for example `Add portfolio image upload`, not `update`).
- [ ] `docs/ai-use/farhaan.md` is up to date.
