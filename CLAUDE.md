# kristineskates.com

Laravel 10 booking platform for Kristine Humphrey, a figure skating coach in St. Louis, MO.

## Tech Stack
- PHP 8.3 / Laravel 10 / MySQL 8 / Apache
- Vite for frontend assets
- Twilio for SMS reminders
- Microsoft Graph for email
- Cloudflare Turnstile for bot protection

## Environments

| Env | URL | DB | Deploy |
|---|---|---|---|
| Dev (WSL) | http://localhost:8000 | kristineskates_dev | `php artisan serve` |
| Staging (EC2) | https://staging.kristineskates.com | kristineskates_staging | `bash deploy.sh staging` |
| Production (EC2) | https://kristineskates.com | kristineskates | `bash deploy.sh` |

## Development Workflow
1. Develop locally on Win11/WSL at `/home/kj-ro/dev.kristineskates.com`
2. `git push origin feature/xyz` to GitHub
3. SSH to EC2, `bash deploy.sh staging feature/xyz` to test
4. Merge to main, `bash deploy.sh` to deploy prod

## Safety
- **Mail** is set to `log` on dev and staging — no real emails sent
- **Twilio** is disabled on dev and staging — no real SMS sent
- **Never develop directly on production**
- Run tests locally and on staging, never on prod

## Local Dev
```bash
php artisan serve    # terminal 1 — app on :8000
npm run dev          # terminal 2 — Vite hot reload
```

## Common Commands
```bash
php artisan migrate              # run migrations
php artisan cache:clear          # clear app cache
php artisan config:clear         # clear config cache
php artisan route:list           # show all routes
composer install                 # install PHP deps
npm install && npm run build     # build frontend
```
