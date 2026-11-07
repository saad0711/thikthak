# ThikThak Sprint 1 — Raisa: F17 Provider Verification (KYC)

From Saad. This file is everything you need for Sprint 1: what the team is doing, your feature spec, your test cases, and the AI prompts to use while you build it.

---

## The team this sprint

| Member | Work | Counted? |
|---|---|---|
| Saad | **Add-ons:** registration (customer/provider), login/logout with "remember me" and a rate limit, role-based access, account settings with profile photo, seeded admin account | No, supporting work |
| Saad | **F1 Service Catalog & Search** and **F4 Service Request Posting & Lifecycle** | Yes |
| Farhaan | F13 Professional Profile & Portfolio | Yes |
| Rubaiyat | F14 Availability & Schedule Management | Yes |
| **You (Raisa)** | **F17 Provider Verification (KYC)** | Yes |

Your branch: **`feature/f17-provider-verification`**

Feature numbers follow the shared doc (*ThikThak — Feature List & Tech Stack*), which is still waiting for faculty approval.

---

## Before you start

1. **Set up your PC** with `ThikThak_Setup_Guide.pdf`, all the way to Step 12. Use the latest copy of the PDF, which has the Smart App Control fix.
2. **What you get from Saad, and when:** early in the week, Saad pushes the Laravel project, `layouts/app`, `users` with roles, login/registration, the `EnsureRole` middleware (`role:provider`, `role:admin`), a base `provider_profiles` table (`id`, `user_id`) with a `ProviderProfile` model, and a seeded admin account (its login is in the README). That's all you need for your whole feature.

   Until then, read this file, plan your tables, and sketch your screens on paper.
3. **Team rules for code:**
   - Work only on your own branch, and open a Pull Request into `main`. Someone else reviews it before it's merged.
   - Commit small steps with clear messages, from your own GitHub account. The faculty checks each person's commits.
   - Don't add columns to `users`. Add columns to `provider_profiles` or create your own tables, always in **your own new migration**. Never edit someone else's migration.
   - `ProviderProfile` is shared by all four of us. Put your methods in your own trait, `app/Models/Concerns/HasVerification.php`, and add one `use` line to the model. That keeps merge conflicts small.
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
- **Keep your AI-use log** in `docs/ai-use/raisa.md`, with the columns Date | Tool | What it helped with | What I did myself. The final submission needs an AI-use statement, and this is where yours comes from.
- Never let an AI write your commit messages or sprint reports. Never paste real NID numbers or photos, passwords or `.env` contents into a chat.

---

## Your feature: F17 Provider Verification (KYC)

### What you build
- **Provider side:** a verification page showing the status: Not submitted, Pending, Approved, or Rejected with the admin's reason. A form to submit the NID number, NID front photo, NID back photo, and an optional extra document (trade licence or certificate). After a rejection, the provider can resubmit.
- **Admin side:** a queue of pending requests, oldest first. A detail page shows the documents, with **Approve** and **Reject** (a reason is required) buttons. The admin can also see each provider's past requests.
- **Verified badge:** a Blade component `<x-verified-badge :profile="$profile" />`. Farhaan shows it on the public profile, and later features reuse it.
- **Gatekeeping for later features:** `isVerified()`, a `verified()` query scope, and an `EnsureProviderIsVerified` middleware (alias `verified.provider`). F5 Quotation and F6 Instant Booking will put their routes behind it, and F2 and F18 will use the scope.

### Business rules
- NID number: digits only, 10, 13 or 17 digits long.
- NID photos: JPG/PNG/WEBP, max 2 MB each. Extra document: JPG/PNG/PDF, max 2 MB.
- **NID files are private.** Store them on the private disk (`storage/app/private`), never in `public`. Serve them through a controller that only lets the admin and the owning provider see them.
- Only one pending request at a time. A provider who is already approved can't submit again. Resubmitting after a rejection creates a new request, so the history is kept.
- Rejecting needs a reason of 10–500 characters. A request that's already been reviewed can't be reviewed again (for example by a double click).
- Approving sets `verified_at` on the provider's profile. Each review records which admin did it and when.
- Only admins can review.
- Never use real NID photos while developing or testing. Use fake images.

### Your tables
```
provider_profiles        -- your migration ADDS this column to Saad's base table
  verified_at TIMESTAMP NULL

verification_requests
  id, provider_profile_id FK (cascade), nid_number VARCHAR(17),
  nid_front_path, nid_back_path, extra_document_path NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  rejection_reason TEXT NULL,
  reviewed_by FK → users NULL (null on delete), reviewed_at NULL, timestamps
  INDEX (status)
```

### Your MVC files
| Layer | Files |
|---|---|
| Model | `VerificationRequest` (incl. `approve()` and `reject()`), the `HasVerification` trait on `ProviderProfile` (`isVerified()`, `scopeVerified()`) |
| View | `provider/verification/show`, `admin/verifications/index`, `admin/verifications/show`, `components/verified-badge` |
| Controller | `Provider/VerificationController`, `Admin/VerificationController` (index, show, approve, reject), `VerificationDocumentController` (serves the private files) |
| Validation | `StoreVerificationRequest`, `RejectVerificationRequest` |
| Access | `VerificationRequestPolicy` · `EnsureProviderIsVerified` middleware |

### Order of work
1. Migration, model and the provider's submit form, with private file storage.
2. Admin queue, detail page, approve and reject.
3. Document download controller and policy.
4. Badge component, `isVerified()`, scope and middleware.
5. Tests.

### What others need from you
- **Farhaan (F13):** the badge component, for the public profile page.
- **F2, F5, F6, F18 (later sprints):** `verified()`, `isVerified()` and the `verified.provider` middleware.

### Test cases (normal / invalid / boundary, rule book §12)
| Case | Input | Expected |
|---|---|---|
| Normal | Valid NID number + front + back photos | Request saved as Pending |
| Business rule | Submit again while Pending | Blocked with a message |
| Boundary | NID with 9 / 10 / 17 / 18 digits | 9 and 18 rejected, 10 and 17 accepted |
| Invalid | NID with letters | Validation error |
| Invalid | 3 MB photo, or a `.exe` | Rejected |
| Normal | Admin approves | `verified_at` set, badge appears on the public profile |
| Invalid | Admin rejects with no reason, or "bad" | Validation error (10 characters minimum) |
| Normal | Admin rejects with a reason | Provider sees the reason and can resubmit |
| Business rule | Approve a request that's already approved | Blocked |
| Security | Provider A opens provider B's NID photo URL | 403 Forbidden |
| Security | Guest opens a document URL | Redirected to login |
| Security | Customer or provider opens `/admin/verifications` | 403 Forbidden |
| Business rule | Unverified provider hits a route behind `verified.provider` (register a test route inside the test) | 403 Forbidden |

---

## Your starter prompt

Paste this into a new chat once, at the start.

```text
You are my tutor and code reviewer for a university Software Engineering project (CSE 470). Read all of this before you answer.

PROJECT
ThikThak is a local service marketplace for Bangladesh: customers find and book providers such as electricians, plumbers and tutors. Four students build it. I'm Raisa, and this sprint I own feature F17. My teammate Saad builds the add-ons (registration, login, roles, account settings), F1 Service Catalog and F4 Service Request Posting. Farhaan builds F13 Provider Profile and Rubaiyat builds F14 Availability.

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
- users with roles (customer/provider/admin), login and registration, EnsureRole middleware (role:provider, role:admin), and a seeded admin account.
- provider_profiles base table (id, user_id) and a ProviderProfile model. User hasOne ProviderProfile.
- A shared Blade layout, layouts/app.

MY FEATURE: F17 Provider Verification (KYC)
- Provider submits NID number (digits only, 10, 13 or 17 long), NID front and back photos (JPG/PNG/WEBP, max 2 MB) and an optional extra document (JPG/PNG/PDF, max 2 MB).
- Files are stored on Laravel's private disk and served only to the admin and the owning provider, through a controller plus a Policy.
- Table verification_requests: status pending/approved/rejected, rejection_reason, reviewed_by (admin user id), reviewed_at. Column verified_at added to provider_profiles in my own migration.
- One pending request at a time; no new request once approved; resubmitting after a rejection creates a new row so history is kept.
- Admin queue (oldest pending first), detail page, approve, reject with a required reason of 10-500 characters. A reviewed request can't be reviewed again.
- Blade component <x-verified-badge :profile="$profile" />.
- For later features: ProviderProfile::isVerified(), a verified() query scope (both in a HasVerification trait), and an EnsureProviderIsVerified middleware with the alias verified.provider.

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
Here's the full diff for my Pull Request: [paste the output of git diff main]. Review it for bugs, missing validation or permission checks, file-upload and private-file security, mass assignment, N+1 queries, and anything in the wrong MVC layer.
```

**7. Viva practice**
```text
Quiz me like a viva examiner on my feature, one question at a time, 10 questions. Include: where each Model, View and Controller file is; why NID files go on the private disk; tracing the approve request from route to database; and one live change, like requiring the extra document. Tell me what I got wrong.
```

---

## Checklist before you open a Pull Request

- [ ] `php artisan test` passes, with tests for the normal, invalid and boundary cases in your table.
- [ ] You merged the latest `main` into your branch, and the app still runs.
- [ ] Your table changes are in your own new migrations. You didn't edit anyone else's.
- [ ] No logic in Blade. Validation is in Form Requests, permission checks in the Policy and middleware.
- [ ] No `.env` and no real NID photos in the commit. Uploaded files stay in `storage/app/private`, which Git ignores.
- [ ] Commit messages say what changed (for example `Add admin verification queue`, not `update`).
- [ ] `docs/ai-use/raisa.md` is up to date.
