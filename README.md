# Kristine Skates - Skating Lesson Booking Platform

A Laravel-based booking and scheduling platform for skating lessons with automated rink schedule scraping, client management, and calendar integrations.

**Live Site:** https://kristineskates.com

## Overview

This platform allows clients to book skating lessons with Coach Kristine across multiple ice rinks in the St. Louis area. It automatically scrapes public skating session schedules from rink websites and generates available lesson time slots.

## Features

### Booking System
- **Guest Booking** - Book lessons without creating an account
- **Client Registration** - Create account for faster booking and history tracking
- **Multi-Rink Support** - Lessons available at 4+ ice rinks
- **Real-time Availability** - Only shows available time slots
- **Confirmation Codes** - Unique codes for booking lookup
- **Email Notifications** - Automated emails for requests, approvals, and rejections

### Admin Dashboard
- Approve/reject booking requests
- View all bookings with filters
- Manage rink schedules
- Email notifications for new requests

### Automated Schedule Scraping
Scrapes public skating schedules from:
- **Creve Coeur Ice Arena** - OCR extraction from calendar images using PaddleOCR
- **Brentwood Ice Rink** - PDF parsing with text extraction
- **Webster Groves Ice Arena** - PDF parsing with text extraction
- **Maryville University Hockey Center** - HTML parsing with regex

Generates 30-minute lesson slots from public skate sessions.

### Calendar Integrations
- **Public Skating Calendars** - Subscribe to public skate times (webcal:// feeds)
  - All rinks combined feed
  - Individual rink feeds
- **Lesson Calendar Attachments** - .ics files attached to approval emails
- Auto-updating feeds (refreshes hourly)

### Client Features
- Dashboard showing booking history
- Auto-fill booking forms for registered users
- Email consent tracking (GDPR compliant)
- View booking status and details

## Tech Stack

- **Framework:** Laravel 11.x
- **Frontend:** Tailwind CSS, Blade templates
- **Database:** MySQL
- **OCR:** PaddleOCR (Python) for Creve Coeur schedules
- **PDF Parsing:** Smalot PdfParser
- **Calendar:** Spatie iCalendar Generator
- **Email:** Laravel Mail with Gmail SMTP
- **Server:** Ubuntu 24, Nginx, PHP 8.3

## Installation

### Prerequisites
```bash
# PHP 8.3+
# Composer
# MySQL 8.0+
# Node.js & NPM
# Python 3.x with PaddleOCR (for Creve Coeur scraper)
```

### Setup

1. Clone repository:
```bash
git clone git@github.com:videodoctor/skating-lessons-site.git
cd skating-lessons-site
```

2. Install dependencies:
```bash
composer install
npm install
npm run build
```

3. Environment configuration:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure `.env`:
```env
APP_URL=https://yourdomain.com
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

5. Run migrations:
```bash
php artisan migrate
```

6. Seed initial data:
```bash
php artisan db:seed --class=RinksSeeder
```

7. Create admin user:
```bash
php artisan tinker
>>> $user = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')]);
>>> exit
```

8. Set up PaddleOCR (for Creve Coeur scraper):
```bash
python3 -m venv /home/ubuntu/paddle-env
source /home/ubuntu/paddle-env/bin/activate
pip install paddlepaddle paddleocr
```

9. Set file permissions:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

10. Schedule scraper (add to crontab):
```bash
# Run daily at 2 AM
0 2 * * * cd /var/www/kristineskates.com && php artisan scrape:rink-schedules >> /dev/null 2>&1
```

## Usage

### Scraping Rink Schedules

Run manually:
```bash
# Scrape all rinks
php artisan scrape:rink-schedules

# Scrape specific rink
php artisan scrape:rink-schedules creve-coeur
php artisan scrape:rink-schedules brentwood
php artisan scrape:rink-schedules webster-groves
php artisan scrape:rink-schedules maryville
```

### Calendar Feeds

Public skating session feeds:
- All rinks: `webcal://kristineskates.com/calendar/public-skating.ics`
- Creve Coeur: `webcal://kristineskates.com/calendar/creve-coeur.ics`
- Brentwood: `webcal://kristineskates.com/calendar/brentwood.ics`
- Webster Groves: `webcal://kristineskates.com/calendar/webster-groves.ics`
- Maryville: `webcal://kristineskates.com/calendar/maryville.ics`

## Database Schema

### Key Tables
- `users` - Admin users
- `clients` - Client accounts (with authentication)
- `services` - Lesson types (Basic, Premium)
- `rinks` - Ice rink information (`schedule_url` = page to scrape, `schedule_pdf_url` = direct PDF for rinks with auth-gated pages e.g. Webster Groves)
- `rink_sessions` - Scraped public skating sessions
- `rink_scrape_runs` - Audit log of every scrape run with stored source file path, session counts, and errors
- `time_slots` - 30-minute bookable lesson slots
- `bookings` - Lesson bookings

## Project Structure
```
app/
├── Console/Commands/
│   └── ScrapeRinkSchedules.php    # Automated scraper
├── Http/Controllers/
│   ├── Admin/                     # Admin dashboard
│   ├── Auth/                      # Client authentication
│   ├── BookingController.php      # Public booking flow
│   └── CalendarController.php     # Calendar feed generation
├── Models/
│   ├── Booking.php
│   ├── Client.php
│   ├── Rink.php
│   ├── RinkSession.php
│   └── TimeSlot.php
└── Notifications/                 # Email notifications
    ├── NewBookingNotification.php
    ├── BookingRequestedNotification.php
    ├── BookingApprovedNotification.php
    └── BookingRejectedNotification.php

resources/views/
├── admin/                         # Admin views
├── booking/                       # Public booking flow
├── client/                        # Client portal
└── layouts/

routes/
└── web.php                        # All routes
```

## TODO / Roadmap

### Security
- [ ] Add CAPTCHA for guest bookings
- [ ] Email verification for guest bookings
- [ ] Email verification for new client registration

### Booking Management
- [ ] Booking lookup by confirmation code
- [ ] Rejection with reason selection
- [ ] Reschedule feature (admin-initiated)
- [ ] Client approval of reschedule requests
- [ ] Clickable confirmation codes in emails

### Calendar Features
- [ ] ICS downloads from client dashboard
- [ ] ICS downloads from admin dashboard
- [ ] Private admin calendar feed (all bookings)
- [ ] Personalized client calendar feeds (by preferred rinks)

### Features
- [ ] Privacy Policy page
- [ ] Admin review page (compare paper planner vs online)
- [ ] Weekly printable calendar view
- [ ] Blackout dates management
- [ ] Service duration updates
- [ ] Phone number validation/formatting
- [ ] Note on lesson prices (excludes rink admission)

## Recent Updates (March 2026)

### Services & Pricing
- **Basic Skills Assessment:** Updated to 30-minute duration
- **Premium Skills Assessment:** 60-minute duration (unchanged)
- **Private Lessons:** 30-minute duration
- **Progress Package:** 120 minutes (2×60 min premium assessments, 3 months apart)

### Email Notifications
- ✅ Rink location added to all booking emails
- ✅ Admission fee disclaimer added ("Lesson price does not include rink admission fee")
- ✅ Booking confirmation email sent to clients upon request submission
- ✅ Approval email includes .ics calendar attachment
- ✅ Rejection email notifications now working

### Booking Flow
- ✅ Cancellation policy agreement checkbox added to booking form
- ✅ Email consent tracking (GDPR compliant)
- ✅ Confirmation codes generated for all bookings

### Calendar Features
- ✅ Public skating session feeds for all rinks (webcal:// subscriptions)
- ✅ Individual rink calendar feeds (Creve Coeur, Webster Groves, Brentwood, Chesterfield/Maryville)
- ✅ Responsive grid layout for calendar subscriptions on homepage

### Rink Scrapers
- ✅ Maryville University Hockey Center (Chesterfield) scraper added
- ✅ Improved date handling for year transitions (Feb→Mar vs Dec→Jan)
- ✅ Raw source storage — PDFs, images, and HTML saved to `storage/app/scrapes/{rink}/` for audit trail
- ✅ `rink_scrape_runs` table tracks every scrape with session counts, error flags, and log output
- ✅ Brentwood PDF parser fixed — handles dates embedded with session text on same line
- ✅ Webster Groves — `schedule_pdf_url` field + curl bypass for CivicPlus 404 quirk (OAuth-gated site)
- ✅ Storage ACL fix — `setfacl` ensures www-data can write to storage across all rink subdirectories
- ✅ FK constraint safety net — scraper preserves slots referenced by bookings even if `booking_id` is NULL

### Admin Tools
- ✅ Schedule verification tool at `/admin/schedule/verify` — side-by-side view of raw source vs parsed calendar
- ✅ Inline session add/edit/delete from verify tool
- ✅ Per-rink re-scrape button with output log display
- ✅ Scrape run info bar showing last scraped time, session count, error status
- ✅ Raw source storage — PDFs, images, and HTML saved to `storage/app/scrapes/{rink}/` for audit trail
- ✅ `rink_scrape_runs` table tracks every scrape with session counts, error flags, and log output
- ✅ Brentwood PDF parser fixed — handles dates embedded with session text on same line
- ✅ Webster Groves — `schedule_pdf_url` field + curl bypass for CivicPlus 404 quirk (OAuth-gated site)
- ✅ Storage ACL fix — `setfacl` ensures www-data can write to storage across all rink subdirectories
- ✅ FK constraint safety net — scraper preserves slots referenced by bookings even if `booking_id` is NULL

### Admin Tools
- ✅ Schedule verification tool at `/admin/schedule/verify` — side-by-side view of raw source vs parsed calendar
- ✅ Inline session add/edit/delete from verify tool
- ✅ Per-rink re-scrape button with output log display
- ✅ Scrape run info bar showing last scraped time, session count, error status

### UI Improvements
- ✅ Responsive calendar subscription grid (1 col mobile, 2 col tablet, 3 col desktop)
- ✅ Equal-height cards with flex layout
- ✅ Chesterfield labeled as primary location name for Maryville rink


## Contributing

This is a private project for Kristine Skates. Contact rob@videorx.com for questions.

## License

Proprietary - All rights reserved

## Credits

- **Developer:** Rob Reinhardt (VideoRx)
- **Coach:** Kristine Humphrey
- **Framework:** Laravel
- **OCR:** PaddleOCR
