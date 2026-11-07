# ThikThak — Revised Feature List (v2, draft)

> **Status: draft for team review and faculty approval.** Rule book §3.1 and §18 give the faculty final say over the feature list. Submit this as a revision of the proposal before relying on it.

**Why a revision was needed** (rule book §3):
- Login, registration and logout don't count as features, so original #1 is moved to **Add-ons**.
- The same function for different roles isn't automatically two features. Original #6 (customer bidding), #14 (provider job board) and #15 (provider quotation) are one workflow, so they're merged into **F5**.
- Original #18 and #20 both covered provider verification. #20 is split into **disputes** (F19) and **analytics/moderation** (F20).
- Two new substantive features fill the gap: **F9 Promo Codes** and **F16 Earnings & Payouts**.

Every feature below is one coherent workflow with business rules that can be tested. That's what §3 asks for.

---

## A. Customer: discovery and booking

### F1. Service Catalog & Search *(was #2)*
- **Workflow:** The admin manages a two-level catalog (category → services, each with an indicative BDT price range). Customers browse, search by keyword, filter by category and price, sort, and paginate.
- **Rules:** A category that has services can't be deleted (deactivate it instead). Inactive items are hidden from customers. Min price ≤ max price.

### F2. Location-Based Provider Discovery *(was #3)*
- **Workflow:** The customer sets a location (map pin or area). Providers whose service radius covers it are listed, sorted by distance (Haversine formula), with a category filter and a map view (Leaflet + OpenStreetMap).
- **Rules:** Only verified, active providers appear. Distance is calculated, not typed in.

### F3. Provider Comparison *(was #7)*
- **Workflow:** The customer picks 2–4 providers (from discovery or from received quotes) and sees them side by side: price/quote total, rating, completed jobs, experience, response time, next free slot, verification status.
- **Rules:** At most 4 at a time. The best value in each row is highlighted. Metrics are calculated from real booking data.

### F4. Service Request Posting & Lifecycle *(was #4)*
- **Workflow:** The customer posts a job: service, description, up to 5 photos, budget range, deadline, location. Status runs Open → Quoted → Assigned → Completed / Cancelled / Expired.
- **Rules:** A request can't be edited once the first quote arrives. It auto-expires after the deadline (Laravel scheduler). Budget min ≤ max.

### F5. Quotation & Bidding *(merges #6, #14, #15)*
- **Workflow:** Providers see a job board of open requests that match their services and area. They submit itemized quotes (labour, materials, other charges, duration, validity date) and can revise or withdraw them. The customer reviews the quotes and accepts one. That creates the booking and auto-rejects the other quotes.
- **Rules:** One active quote per provider per request. Expired quotes can't be accepted. Only verified providers can quote.

### F6. Instant Service Booking *(was #5)*
- **Workflow:** For an urgent request, the system sends it to the top-matched nearby providers who are online. The first to accept gets the job. If nobody accepts within X minutes, the next batch is tried or the radius widens.
- **Rules:** Only one provider can win the job (prevent double acceptance with a DB transaction and row lock). The customer can cancel before anyone accepts.

### F7. Appointment Scheduling, Rescheduling & Cancellation *(was #8, plus the "service history / book again" part of #12)*
- **Workflow:** The customer picks a date and slot from the provider's generated free slots. They can reschedule or cancel, and see upcoming and past bookings with a "book again" option.
- **Rules:** No double booking. Free cancellation up to 24 h before; later cancellations are flagged. Provider cancellations lower their reliability score, which F18 uses.

### F8. Online Payments & Digital Receipts *(was #11)*
- **Workflow:** After the job, the customer pays through a sandbox gateway (SSLCommerz or bKash sandbox) or chooses cash, which the provider then confirms. A PDF receipt is generated and payment history is kept.
- **Rules:** Payment states run Pending → Paid → Refunded. The platform commission is recorded on every payment. Cash needs the provider's confirmation.

### F9. Promo Codes & Discounts *(new)*
- **Workflow:** The admin creates codes (percentage or fixed amount, minimum spend, maximum discount, validity dates, category limits, total and per-user usage limits). The customer applies a code at checkout, and the discount shows on the receipt.
- **Rules:** Each limit is checked, with a clear reason when a code is rejected. Usage is tracked per user. The discount never exceeds the order total.

### F10. Ratings & Reviews *(was #12)*
- **Workflow:** After a completed, paid booking, the customer rates quality, punctuality and value, and can add a comment and photos. The provider can reply once. The provider's average rating and breakdown are recalculated.
- **Rules:** One review per booking, and only after completion. Reported reviews go to admin moderation (F20).

---

## B. Communication

### F11. Real-Time Notification Center *(was the notification part of #9)*
- **Workflow:** Events (quote received, booking confirmed, status change, new message, payment, verification result) create notifications. They're stored in a notification center with an unread count and mark-as-read, pushed live (Laravel Reverb), and emailed for key events.
- **Rules:** Per-user preferences for each channel (in-app / email). Notifications are only sent to the people involved in that booking or request.

### F12. In-App Messaging *(was #10)*
- **Workflow:** There's one conversation per request or booking between customer and provider, with real-time delivery, unread counts and image attachments.
- **Rules:** Phone numbers and emails are masked until a booking is confirmed, which keeps deals on the platform. The chat becomes read-only after the job closes.

---

## C. Service provider

### F13. Professional Profile & Portfolio *(was #13)*
- **Workflow:** The provider chooses services from the catalog and sets their own prices, plus experience, service areas/radius, certifications and a portfolio gallery. A public profile page shows jobs completed, rating and member-since date.
- **Rules:** A profile-completeness percentage is shown. A provider can't go online for jobs below a minimum completeness.

### F14. Availability & Schedule Management *(was #16)*
- **Workflow:** The provider sets weekly working hours and days off, toggles online/offline for instant jobs, and sees booked appointments on a calendar.
- **Rules:** The system generates bookable slots and excludes ones already booked (F7 uses these). The provider is automatically marked busy during an active job.

### F15. Job Status Tracking *(was #17, plus the tracking part of #9)*
- **Workflow:** A status state machine: Accepted → On the Way → Arrived → In Progress → Completed (or Cancelled). The customer sees a timeline with a timestamp for each stage.
- **Rules:** Only valid forward transitions are allowed. Moving to In Progress needs a 4-digit start code from the customer, which proves the provider is on site. The customer confirms completion, or it auto-confirms after 24 h.

### F16. Earnings & Payout Management *(new)*
- **Workflow:** Per-job earnings (amount − commission), with a pending vs available balance. The provider requests a payout to a bKash number, and the admin approves or rejects it. Includes earnings history and a monthly chart.
- **Rules:** A minimum payout amount applies. Earnings stay pending during a hold period. Disputed jobs are frozen until resolved (F19).

---

## D. Administration and trust

### F17. Provider Verification (KYC) *(was #18)*
- **Workflow:** The provider uploads NID front and back plus certificates. The admin reviews them and approves, or rejects with a reason. The provider can resubmit. Approved providers get a verified badge.
- **Rules:** Unverified providers can't quote, be dispatched, or appear in discovery. Status runs Pending → Approved / Rejected.

### F18. Smart Matching & Dispatch Engine *(was #19)*
- **Workflow:** Ranks eligible providers for a request with a weighted score: distance, rating, acceptance rate, response time, reliability, current workload. F6 uses it for dispatch, and F5 uses it to suggest providers.
- **Rules:** Only verified, online providers in the right category and radius are eligible. The admin can change the weights. The score breakdown can be inspected, so it's easy to explain in the viva.

### F19. Dispute & Complaint Resolution *(was the dispute part of #20)*
- **Workflow:** The customer or provider raises a complaint against a booking within 7 days, with evidence. The ticket runs Open → Under Review → Resolved / Rejected, and the admin decides on a refund (full or partial), a warning, or a suspension.
- **Rules:** One open dispute per booking. Payout is frozen while a dispute is open. Both parties are notified at each step.

### F20. Admin Analytics & User Moderation *(was the dashboard part of #20)*
- **Workflow:** KPI dashboard with a date-range filter and charts: bookings, transaction volume, commission revenue, active providers, top categories, cancellation rate. Includes user moderation (suspend/ban with a reason) and moderation of reported reviews.
- **Rules:** Suspended users can't log in or receive jobs. Every moderation action is logged with the admin and a timestamp.

---

## Add-ons: supporting functionality (built, but NOT counted as features)

Per rule book §3, these are documented as supporting functionality in the SRS:
- Customer and provider registration
- Login / logout, "remember me", login rate limiting
- Email verification
- Forgot / reset password
- Role-based access control (customer / provider / admin middleware)
- Account settings: edit basic info, change password, deactivate account
- Seeded admin account
- Shared responsive layout and navigation

These form the base that all 20 features depend on, so they're built first in Sprint 1.

---

## Backup features (if the faculty rejects any of the 20)

- **Service Warranty & Rework Claims:** the customer can request free rework within 7 days of completion. The provider must accept or escalate it to a dispute.
- **Recurring Bookings:** weekly or monthly schedules for cleaning or tutoring, with skip and pause. Each occurrence is created automatically.
- **Favourite Providers & Quick Rebooking:** a saved list of providers, with one-tap rebooking that checks they're available.

---

## Suggested sprint roadmap (5 features per sprint, ordered by dependency)

| Sprint | Features | Why this order |
|---|---|---|
| **1** | Add-ons (auth) · F1 Catalog · F4 Request Posting · F13 Profile · F14 Availability · F17 Verification | The base data: users, catalog, providers, requests |
| **2** | F2 Location Discovery · F3 Comparison · F5 Quotation · F7 Scheduling · F12 Messaging | Needs providers, requests and availability from Sprint 1 |
| **3** | F6 Instant Booking · F8 Payments · F11 Notifications · F15 Status Tracking · F18 Smart Matching | Needs bookings from Sprint 2 |
| **4** | F9 Promo Codes · F10 Reviews · F16 Earnings · F19 Disputes · F20 Analytics | Needs completed, paid jobs |

F3 Comparison shows ratings once F10 is built in Sprint 4. Until then, its rating row reads "No ratings yet".

---

## Mapping from the original proposal

| Original | Now |
|---|---|
| #1 Registration & Authentication | Add-ons (not counted) |
| #2 | F1 |
| #3 | F2 |
| #4 | F4 |
| #5 | F6 |
| #6 + #14 + #15 | F5 (one workflow across roles) |
| #7 | F3 |
| #8 | F7 |
| #9 | F11 (notifications) + F15 (tracking) |
| #10 | F12 |
| #11 | F8 |
| #12 | F10 (reviews) + F7 (history / book again) |
| #13 | F13 |
| #16 | F14 |
| #17 | F15 |
| #18 | F17 |
| #19 | F18 |
| #20 | F19 (disputes) + F20 (analytics / moderation) |
| new | F9 Promo Codes, F16 Earnings & Payouts |
