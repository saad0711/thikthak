# ThikThak Sprint 1 — Raisa: F14 Availability & Schedule Management

From Saad. This file is everything you need for Sprint 1: what the team is doing, your feature spec, your test cases, and the AI prompts to use while you build it.

---

## The team this sprint

| Member | Work | Counted? |
|---|---|---|
| Saad | **Add-ons:** registration (customer/provider), login/logout with "remember me" and a rate limit, role-based access, account settings with profile photo, seeded admin account | No, supporting work |
| Saad | **F1 Service Catalog & Search** and **F4 Service Request Posting & Lifecycle** | Yes |
| Farhaan | F13 Professional Profile & Portfolio | Yes |
| **You (Raisa)** | **F14 Availability & Schedule Management** | Yes |
| Rubaiyat | F17 Provider Verification (KYC) | Yes |

Your branch: **`feature/f14-availability`**

Feature numbers follow the shared doc (*ThikThak — Feature List & Tech Stack*), which is still waiting for faculty approval.

---

## Before you start

1. **Set up your PC** with `ThikThak_Setup_Guide.pdf`, all the way to Step 12. Use the latest copy of the PDF, which has the Smart App Control fix.
2. **What you get from others, and when:**

   | From | What | What you can start |
   |---|---|---|
   | Saad, early in the week | Laravel project, `layouts/app`, `users` with roles, login/registration, the `EnsureRole` middleware (`role:provider`), a base `provider_profiles` table (`id`, `user_id`) with a `ProviderProfile` model | Everything except the online toggle |
   | Farhaan, early in the week | `ProviderProfile::canGoOnline()` (true at 60%+ profile completeness) | The online toggle |

   Until the first push, read this file, plan your tables, and sketch your screens on paper.
3. **Team rules for code:**
   - Work only on your own branch, and open a Pull Request into `main`. Someone else reviews it before it's merged.
   - Commit small steps with clear messages, from your own GitHub account. The faculty checks each person's commits.
   - Don't add columns to `users`. Add columns to `provider_profiles` or create your own tables, always in **your own new migration**. Never edit someone else's migration.
   - `ProviderProfile` is shared by all four of us. Put your methods in your own trait, `app/Models/Concerns/HasAvailability.php`, and add one `use` line to the model. That keeps merge conflicts small.
   - No logic in Blade views. Validation goes in Form Requests, permission checks in Policies or middleware, business rules in Models or a class in `app/Services`. Controllers stay short.
   - Ask in the group before `composer require` or `npm install <package>`. A package that does a whole feature for you needs faculty approval (rule book §7). That's why the calendar is plain Blade.
   - `php artisan test` must pass before you open a PR.
4. **Sprint rhythm:** goals go in on Saturday and the review is on Friday. If something isn't finished, push what you have on Friday and report it as partial, with the reason.

---

## Using AI (rule book §8)

The course allows AI for learning, explanations, debugging, reviewing and test ideas. It does **not** allow AI to generate a whole feature that you then submit. In the viva (10 of the 15 marks), the faculty can ask you to explain, change or debug your code live.

So the prompts in this file don't ask the AI to build your feature. They set it up as a **tutor and reviewer**: it explains each step, you write the code, it reviews what you wrote, gives hints when you're stuck, and quizzes you before the viva. You still finish the whole feature, and you understand every line.

- Any AI chat works. The free tier is enough, so don't pay for anything.
- Start a new chat with **your starter prompt**, then use the **step prompts** for each step.
- **Keep your AI-use log** in `docs/ai-use/raisa.md`, with the columns Date | Tool | What it helped with | What I did myself. The final submission needs an AI-use statement, and this is where yours comes from.
- Never let an AI write your commit messages or sprint reports, and never paste passwords or `.env` contents into a chat.

---

## Your feature: F14 Availability & Schedule Management

### What you build
- **Weekly working hours:** for each day of the week, a start and end time, or "closed".
- **Slot length:** the provider picks how long one booking slot is: 30, 60, 90 or 120 minutes.
- **Days off:** specific dates the provider isn't working (holidays, Eid and so on).
- **Online/offline toggle** for instant jobs, which F6 uses in Sprint 3.
- **Slot generator:** a class that returns the free slots for a provider on a given date. Customers will book these in F7 (Sprint 2), so it takes a list of busy times that F7 can pass in later.
- **Week calendar:** a plain Blade week grid (Saturday to Friday) showing working hours, days off and the generated slots. Booked appointments appear once F7 exists. Build it with Blade and Tailwind, not a calendar package, so it stays your own work.

### Business rules
- End time must be after start time, and the working block must be at least one slot long.
- One working block per day of the week.
- A day off must be today or later, and the same date can't be added twice.
- Slots must fit completely inside working hours. For 09:00–17:00 with 90-minute slots, the slots are 09:00, 10:30, 12:00, 13:30 and 15:00, because a 16:30 slot would end after 17:00.
- No slots on a day off or a closed day. No slots that have already started today.
- A slot that overlaps a busy time is left out.
- A provider can only go online if `canGoOnline()` (Farhaan's F13, at least 60% complete) is true **and** they've set working hours for at least one day.
- Only the owning provider can edit their availability (a Policy).
- *Later sprints, not now:* F15 marks the provider busy during an active job.

### Your tables
```
provider_profiles        -- your migration ADDS these columns to Saad's base table
  is_online BOOL DEFAULT false, slot_minutes SMALLINT DEFAULT 60

provider_working_hours
  id, provider_profile_id FK (cascade), day_of_week TINYINT (0 = Sunday ... 6 = Saturday),
  start_time TIME, end_time TIME, timestamps
  UNIQUE (provider_profile_id, day_of_week)

provider_days_off
  id, provider_profile_id FK (cascade), date DATE, reason NULL, timestamps
  UNIQUE (provider_profile_id, date)
```

### Your MVC files
| Layer | Files |
|---|---|
| Model | `WorkingHour`, `DayOff`, the `HasAvailability` trait on `ProviderProfile`, and `app/Services/SlotGenerator.php` for the slot logic |
| View | `provider/availability/edit` (weekly hours + slot length), `provider/days-off/index`, `provider/schedule/week` (calendar) |
| Controller | `Provider/AvailabilityController`, `Provider/DayOffController`, `Provider/OnlineStatusController`, `Provider/ScheduleController` |
| Validation | `UpdateWorkingHoursRequest`, `StoreDayOffRequest` |
| Access | `AvailabilityPolicy` (or reuse Farhaan's `ProviderProfilePolicy`; agree with him) |

### Order of work
1. Working hours and slot length.
2. Days off.
3. `SlotGenerator` with unit tests. This is the heart of your feature, so test it well.
4. Week calendar.
5. Online toggle, once Farhaan's `canGoOnline()` is on `main`.

### What others need from you
- **F7 Scheduling (Sprint 2):** `SlotGenerator`, which takes a date plus busy times and returns the free slots.
- **F6 and F18 (Sprint 3):** `is_online`.

### Test cases (normal / invalid / boundary, rule book §12)
| Case | Input | Expected |
|---|---|---|
| Normal | Monday 09:00–17:00, 60-minute slots | 8 slots, first 09:00, last 16:00 |
| Normal | Same hours, 90-minute slots | 5 slots, last 15:00 |
| Business rule | Date is a day off | 0 slots |
| Business rule | Day marked closed | 0 slots |
| Business rule | Asking for today at 11:30 (use `$this->travelTo()` in the test) | No slots before 11:30 |
| Business rule | Busy time 10:00–11:00 | The 10:00 slot is left out |
| Invalid | End 09:00, start 17:00 | Validation error |
| Boundary | 30-minute block with 60-minute slots / 60-minute block | First rejected, second accepted |
| Invalid | Slot length 45 | Validation error |
| Invalid | Day off yesterday, or the same date twice | Validation error |
| Business rule | Go online at 55% completeness | Refused with a message |
| Business rule | Go online at 60%+ with no working hours | Refused with a message |
| Security | Provider B edits provider A's hours | 403 Forbidden |
| Security | Customer opens `/provider/availability` | 403 Forbidden |

---

## Your starter prompt

Paste this into a new chat once, at the start.

```text
You are my tutor and code reviewer for a university Software Engineering project (CSE 470). Read all of this before you answer.

PROJECT
ThikThak is a local service marketplace for Bangladesh: customers find and book providers such as electricians, plumbers and tutors. Four students build it. I'm Raisa, and this sprint I own feature F14. My teammate Saad builds the add-ons (registration, login, roles, account settings), F1 Service Catalog and F4 Service Request Posting. Farhaan builds F13 Provider Profile and Rubaiyat builds F17 Provider Verification.

STACK (fixed, don't suggest alternatives)
- Laravel 13, PHP 8.5 (XAMPP on Windows), MariaDB 10.11, Blade views, Tailwind CSS via Vite, Eloquent, PHPUnit tests.
- No React, Vue, Livewire or Inertia. No calendar packages: I build the calendar with Blade. No paid tools.
- Strict MVC: validation in Form Requests, permission checks in Policies or middleware, business rules in Models or a class in app/Services, short controllers, no logic in Blade.
- New Composer or npm packages need team approval first.

COURSE RULE ON AI (important)
I'm not allowed to submit an AI-generated feature. I must write the code myself and be able to explain, change and debug it live in a viva. So:
1. Don't write whole files or the whole feature for me.
2. Go one small step at a time. For each step, tell me what to build, which file it goes in, and why it belongs in that MVC layer. Then wait for me to write it.
3. When I paste my code, review it: point out bugs, security problems and MVC mistakes, and explain why. Ask me questions rather than rewriting it.
4. If I'm still stuck after a hint, show a short example (15 lines at most) of the idea, explain each line, and let me adapt it.
5. Never write git commit messages or sprint reports for me.

WHAT ALREADY EXISTS (built by my teammates)
- users with roles (customer/provider/admin), login and registration, and EnsureRole middleware (role:provider). Built by Saad.
- provider_profiles base table (id, user_id) and a ProviderProfile model. User hasOne ProviderProfile. Built by Saad.
- From Farhaan's F13: ProviderProfile::canGoOnline() returns true when the profile is at least 60% complete.
- A shared Blade layout, layouts/app.

MY FEATURE: F14 Availability & Schedule Management
- Weekly working hours: one start/end time per day of the week, or closed (table provider_working_hours, day_of_week 0-6, 0 = Sunday).
- Slot length chosen by the provider: 30, 60, 90 or 120 minutes (column slot_minutes on provider_profiles).
- Days off on specific dates (table provider_days_off).
- Online/offline toggle (column is_online on provider_profiles). Going online needs canGoOnline() AND at least one day with working hours.
- A SlotGenerator class in app/Services: given a provider, a date and a list of busy time ranges, it returns the free slot start times. Slots must fit fully inside working hours, none on days off or closed days, none that already started today, none overlapping a busy range.
- A week calendar (Saturday to Friday) in plain Blade showing hours, days off and slots.
- Rules: end after start, block at least one slot long, one block per weekday, day off today or later and not duplicated, only the owner can edit (a Policy).
- I add my columns to provider_profiles in my own new migration, and I put my ProviderProfile methods in a HasAvailability trait.

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
Here are my test cases: [paste the test table from my file]. Help me write the first one as a PHPUnit test and explain every part. I'll write the rest myself, then paste them for review.
```

**6. Review before a Pull Request**
```text
Here's the full diff for my Pull Request: [paste the output of git diff main]. Review it for bugs, missing validation or permission checks, mass assignment, N+1 queries, time and timezone mistakes, and anything in the wrong MVC layer.
```

**7. Viva practice**
```text
Quiz me like a viva examiner on my feature, one question at a time, 10 questions. Include: where each Model, View and Controller file is; why the slot logic lives in a service class and not the controller; tracing one request from route to view; and one live change, like adding a 45-minute slot length. Tell me what I got wrong.
```

---

## Checklist before you open a Pull Request

- [ ] `php artisan test` passes, with tests for the normal, invalid and boundary cases in your table.
- [ ] You merged the latest `main` into your branch, and the app still runs.
- [ ] Your table changes are in your own new migrations. You didn't edit anyone else's.
- [ ] No logic in Blade. Validation is in Form Requests, permission checks in the Policy.
- [ ] No `.env` in the commit.
- [ ] Commit messages say what changed (for example `Add weekly working hours form`, not `update`).
- [ ] `docs/ai-use/raisa.md` is up to date.
