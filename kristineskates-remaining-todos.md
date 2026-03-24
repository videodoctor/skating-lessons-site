# Kristine Skates Platform - Remaining To-Do's

## Recently Completed ✅
- SVG background fix (superseded by full homepage redesign)
- Phase 2 UI: polished homepage, booking flow, admin schedule/clients/export/planner-OCR
- Rinks info page with maps, directions, webcal subscribe links
- Hamburger mobile nav with hockey skater logo
- Favicon from hockey skater PNG
- Brentwood PDF scraper fixed (line-by-line parser with correct date tracking)
- Stale slot cleanup: future unbooked slots cleared before each scrape run

---

## KNOWN BUGS

- Planner scan page title renders raw PHP: `Planner Scan — <?php echo e($scan->month); ?>` — needs dynamic `@section('title')` fix in layout
- Admin schedule page: modal CSS conflict between `.modal-overlay { display:flex }` and Tailwind `hidden` class — fixed with `.active` class but worth regression testing
- Webster Groves `schedule_pdf_url` must be manually updated each month when new PDF published

---

## HIGH PRIORITY

### 1. Admin: Schedule Verification Tool (Side-by-Side)
**Goal:** Let admin compare the rink's original published schedule (image/PDF/HTML) against our rendered calendar for any given month — so errors can be spotted and corrected without juggling browser windows.

**Features:**
- Rink selector + month/year picker
- Left panel: embedded iframe or image of the rink's published schedule URL
- Right panel: our rendered monthly calendar grid showing scraped sessions
- Inline edit: click any session to edit/delete, click empty day to add session manually
- "Re-scrape this rink" button to refresh from source
- Flag sessions that differ from last scrape (highlight changed/added/removed)

**Location:** `/admin/schedule/verify` or tab within existing `/admin/schedule`

---

### 2. ✅ Public: Merged Rink Session Calendar — COMPLETED
Implemented on `/rinks` page with: "On the Ice Today" live session display grouped by rink with LIVE badges, "Subscribe to All Rinks" webcal banner (`/calendar/public-skating.ics`), per-rink calendar subscribe buttons, and Book a Lesson CTAs.

---

### 3. Finalize Scraper Deployment
- ✅ Set up cron job for automated daily scraping (all active rinks) — runs at 3am via `sudo crontab -u www-data`
- Test error handling for missing/malformed PDFs
- Add scraper status dashboard in admin (last run, sessions found, errors)
- Confirm Webster Groves and Maryville scrapers are working correctly

---

### 4. Venmo Payment Integration
- Kristine's handle: `@Kristine-Humphrey` (already in .env as `VENMO_HANDLE`)
- Admin can manually mark bookings as cash paid (with timestamp + admin name)
- Admin can manually mark bookings as Venmo paid
- Venmo deep-link on booking confirmation: `venmo://paycharge?txn=pay&recipients=Kristine-Humphrey&amount=XX&note=Booking+CODE`
- Investigate Venmo API / webhook for auto-reconciliation of incoming payments
- Flag unmatched Venmo payments for manual review
- Payment status visible on admin booking list and client dashboard

---

## MEDIUM PRIORITY

### 5a. Admin: Client Management
- "Create Client" button on admin clients page with full form (first name, last name, email, phone, notes)
- Edit existing client details inline or via modal
- View client's linked students, bookings, and payment history from one page
- UI to link orphaned students to a client/parent (student was created from planner scan without a parent account yet)
- Bulk import clients from CSV

### 5b. Admin: Student → Client Linking UI
- "Orphaned Students" view showing students with no `client_id`
- Search/select existing client to link student to
- Or create new client on the fly and link simultaneously
- Show on planner scan results page when a student has no parent linked

### 5c. Liability Waiver (E-Sign)
- Draft legally sound waiver text covering skating injury risks, coach liability, parent/guardian consent for minors
- E-sign flow: client types full name as signature, timestamp + IP recorded
- Required before first booking can be confirmed
- Versioned — if waiver text is updated, clients must re-sign
- Admin can view signed waivers per client

### 5d. Venmo Auto-Reconciliation
- Poll Venmo API or webhook to match incoming payments to bookings by amount + confirmation code
- Auto-mark booking as paid when matched
- Flag unmatched Venmo payments for manual review
- Admin can manually mark bookings as cash paid (with timestamp + admin name recorded)

### 5e. Price Override on Bookings
- Admin can set custom price on any booking (for discounts, package deals, existing client rates)
- Price override field visible when creating booking from planner scan
- Track original service price vs actual price paid

### 5f. Planner OCR — Create Booking from Scan Entry
- "Create Booking" button on unmatched/no-booking-found planner entries
- Pre-fills date, time, student, rink from scan entry
- Price override field (defaults to service price)
- Payment type selector (cash/venmo)
- Creates confirmed booking and links back to scan entry

### 5g. Video Analysis (Future)
- Use Claude Vision on video of Mick and Kristine (1080x1920 MP4 at /home/ubuntu) for lesson analysis
- Extract timestamps of key skating moments, technique observations
- Could feed into assessment reports



### 5. Enhanced Booking Interface
- Multi-rink calendar filtering (click rink to filter available slots)
- Service type icons/badges (color-code by lesson type)
- Waitlist functionality with auto-notify when slot opens
- Date range selector for browsing future weeks

### 6. Client Dashboard
- View upcoming lessons
- Reschedule/cancel with policy enforcement
- Download past .ics files
- View lesson history and progress notes

### 7. Email Template Polish
- Professional HTML email design
- Reminder emails (24hr before lesson)
- Cancellation confirmation
- Waitlist notifications
- SMS notifications via Twilio (optional, opt-in)

---

## LOW PRIORITY

### 8. Branding Transition
- Register stlskating.com
- Set up redirects from kristineskates.com → stlskating.com
- Update email addresses (kristine@stlskating.com)
- Social media handles (@sk8stl already planned)
- Business cards, professional photos, testimonials section

### 9. SEO & Local Marketing
- Google Business Profile (claim, optimize, add service areas, request reviews)
- Content marketing: blog posts, "Rink Spotlight" series, video tutorials
- Local partnerships: rinks, youth hockey leagues, school outreach

### 10. Advanced Features (Long-term)
- Progress tracking (admin lesson notes, client skill progression, video uploads)
- Group lessons (multiple participants, tiered pricing, capacity enforcement)
- Recurring bookings (weekly packages, auto-bill, season passes)
- Analytics dashboard (revenue, popular slots, retention, rink utilization)

---

## Technical Debt

- PHPUnit tests for booking logic
- Email delivery testing in staging
- Security audit (auth flows, CSRF, SQL injection)
- Database query optimization + caching
- CDN for static assets
- Admin user guide + developer docs

---

## Reference

**Git:** `git@github.com:videodoctor/skating-lessons-site.git`
**Live site:** https://kristineskates.com
**Current tag:** `phase2-complete` (af3fe7d..a0777fd)

**Color Palette:**
- Navy: #001F5B (primary)
- Red: #C8102E (CTA)
- Gold: #C9A84C (accent)
- Ice: #E8F5FB (background tint)
- Hockey Blue: #003087 (secondary buttons)

**Services:**
1. Basic Assessment (30min)
2. Premium Assessment (60min)
3. Private Lesson (30min)
4. Progress Package (120min)

**Active Rinks (priority order):**
1. Creve Coeur Ice Arena
2. Kirkwood Ice Arena (currently inactive)
3. Webster Groves Ice Arena
4. Brentwood Ice Rink
5. Maryville University Hockey Center
