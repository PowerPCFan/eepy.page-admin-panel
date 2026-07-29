# eepy.page admin panel

A Laravel/PHP panel for eepy.page administrators

## Requirements

- Recent version of Ubuntu/Debian
  - <small>note: there's a 99.9% chance other Linux distros and other OSes will work, but I haven't tested others or written instructions so it's up to you to get that working</small>
- PHP &ge; 8.4
- Composer
- Node.js + npm
  - <small>other node package managers like pnpm and yarn likely work as well, but as I said above for the operating system, no promises!</small>
- Git
- Various other tools/packages

### Development packages

First, install the apt packages:

```bash
sudo apt update
sudo apt install -y ca-certificates curl unzip git php8.4 php8.4-cli php8.4-common php8.4-curl php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-intl php8.4-zip php8.4-sqlite3 php8.4-fpm
```

Note: This command may install Apache2, which you don't want. (well actually, if you're already using it for something else, skip this section.)

Your mileage may vary, as I've been experimenting with apt commands and it only sometimes installs apache2, my theory is that it's trying to figure out what route to use for hosting/executing php projects.

Since the above command installs php8.4-fpm, there's a chance it'll figure out that you don't need Apache, but in case it doesn't, you can run the following commands to stop Apache:

Stop Apache permanently:
```bash
sudo systemctl disable --now apache2
```

If you'd like to remove it permanently:
```bash
sudo apt purge -y apache2 apache2-bin apache2-data apache2-utils libapache2-mod-php8.4

# Optional; cleans up unused packages
# you should probably do it unless you have a reason not to, but still proceed with caution
sudo apt autoremove --purge

# Remove apache2 dir
sudo rm -rf /var/lib/apache2
```

Next, install Node.js:
- Visit the [Node.js website](https://nodejs.org/en/download)
- Run the simple commands provided to install `nvm`, `node`, and `npm`
- You are all set!

Finally, install Composer using the official installer (you have to copy the command from their site as it changes regularly): https://getcomposer.org/download/

The command you're looking for is at the very top of the linked page and consists of a few lines looking something like this:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (...) { ... } else { ... }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

After that finishes running, stay in the directory you ran that in and run this command to "properly" install it:
```bash
mkdir -p /usr/local/bin && sudo mv composer.phar /usr/local/bin/composer
```

---

## Installation

From the project directory:

```bash
composer install
npm install

cp .env.example .env  # after this, fill in the blank values!
php artisan key:generate

npm run build
```

Configure your `.env`:

```dotenv
EEPY_API_URL="put your api url here"
EEPY_TURNSTILE_SITE_KEY="put your public sitekey here"
```

For production, also set:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL="put the url you are hosting the eepy.page admin panel on here"
```

---

## Running locally

Open two terminals, and follow these commands to set up the Laravel development environment:

```bash
# in terminal #1:
php artisan serve

# in terminal #2:
npm run dev
```

The application will be available at:

```
http://localhost:8000
```

---

# Production deployment

This project is intended to be served along with `PowerPCFan/eepy.page-backend` using **Caddy + PHP-FPM**.

Ensure everything is prepared for production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache

touch database/database.sqlite
php artisan migrate --force

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rw storage bootstrap/cache
```

Then build the app if you haven't already:

```bash
npm run build
```

Ensure that in your `eepy.page-backend/.env` file, the environment vars `CADDY_PANEL_DOMAIN` and `CADDY_PANEL_PATH` are set.

Then reload Caddy and enable PHP-FPM:

```bash
sudo systemctl reload caddy
sudo systemctl enable --now php8.4-fpm
```

Now, all you have to do is point a DNS record to your server (if you had the backend previously set up, use the same value) and Caddy will handle where to route requests.

---

## Updating

To deploy a new version:

```bash
# Pull latest version
git pull

# Ensure all latest packages are installed
composer install --no-dev --optimize-autoloader
npm ci

# Build app
npm run build

php artisan migrate --force
php artisan optimize

# Reload systemd services
sudo systemctl reload php8.4-fpm
sudo systemctl reload caddy
```
