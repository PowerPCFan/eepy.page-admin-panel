# eepy.page admin panel

A Laravel/PHP panel for eepy.page administrators

## Requirements

Ubuntu/Debian, PHP 8.4, Composer, Node.js, npm

System packages (apt):
```bash
sudo apt update
sudo apt install -y software-properties-common ca-certificates curl unzip git \
  php8.4 php8.4-cli php8.4-common php8.4-curl php8.4-mbstring \
  php8.4-xml php8.4-bcmath php8.4-intl php8.4-zip php8.4-sqlite3 \
  nodejs npm
```

Composer can be installed using its official installer.

## Install

From this directory:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
```

The default local configuration uses encrypted file sessions, file cache, and synchronous queues, so SQLite is not required to run the panel. If you choose `SESSION_DRIVER=database`, `CACHE_STORE=database`, or database-backed queues instead, install `php8.4-sqlite3` and run `php artisan migrate`.

Set .env keys:
```dotenv
EEPY_API_URL=https://api.eepy.page
EEPY_TURNSTILE_SITE_KEY=0x4AAAAAADviUbGPh--ynweX
```

The Turnstile site key is public and may be present in browser HTML. The corresponding Turnstile secret must remain configured only on the eepy.page backend; it must not be added to this project.

Note on prod: use a persistent session store and set `APP_ENV`, `APP_DEBUG=false`, a real `APP_URL`, and `SESSION_SECURE_COOKIE=true`.

## Run locally

```bash
php artisan serve
```

The site should be hosted at http://127.0.0.1:8000. Sign in with an eepy.page account that has admin access.

For frontend dev, run the Vite watcher:
```bash
npm run dev
```

## Checks

```bash
# PHP/Artisan
php artisan route:list
php artisan view:cache
php artisan test

# Node.js
npm run build
```
