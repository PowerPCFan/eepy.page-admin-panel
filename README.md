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
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions

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

To deploy a new version: run ./update.sh

## Troubleshooting
This is not an extensive troubleshooting guide, but I'll try my best to add the fixes to issues that I encounter, if I remember.

### 403 when trying to visit admin panel
Ended up being a Caddy permissions issue for me. Fix:

Install ACL:
```bash
sudo apt install -y acl
```

Then, allow Caddy to have the `x` permission on every dir leading up to your `/public` dir. This allows for path traversal. For example, if your app was at /path/to/app/public:
```bash
sudo setfacl -m u:caddy:x /path
sudo setfacl -m u:caddy:x /path/to
sudo setfacl -m u:caddy:x /path/to/app
```

On the `/public` dir, set both `r` and `x` so Caddy can not only enter the dir, but also list its contents.
```bash
sudo setfacl -m u:caddy:rx /path/to/app/public
```

Finally, recursively give Caddy `r` permission on all files in `/public`, and give `x` on directories only (which is what the uppercase `X` means).
```bash
sudo setfacl -R -m u:caddy:rX /path/to/app/public
```

If you were curious why you need to give it the `x` permission on all parent dirs, this is because Linux path traversal requires the user/group accessing the path to be able to access all parent dirs, so even though Caddy only needs to access /public, it must have access to all parents as well.

But why specifically these permissions? Something that trips people up is that the letters `r`, `w`, and `x` mean totally different things for files and directories. In this case, we are setting:
- `x` on parent dirs (e.g. `/path`, `/path/to`, etc) so Caddy can traverse
- `rx` on `/path/to/app/public` so Caddy can list contents
- `rX` recursively on `/path/to/app/public`'s contents so Caddy can read files and traverse subdirectories

Test that your changes worked:
```bash
sudo -u caddy ls -A /path/to/app/public
```

### 404 when trying to visit admin panel
This could obviously be caused by many things but for me it was yet another permissions issue. Now I'm starting to realize that all of these issues would be gone if I had just put the site in /var/www... 

Anyways, I might as well document my fix here. I ran commands similar to the ones I used to fix the Caddy permissions:

```bash
# Set parents' perms
sudo setfacl -m u:www-data:x /path
sudo setfacl -m u:www-data:x /path/to

# Set a baseline (recursively deny r/w, allow x on dirs only)
sudo setfacl -R -m u:www-data:--X /path/to/app

# Give necessary files/dirs rX
for dir in app bootstrap config public resources routes vendor; do
    sudo setfacl -R -m u:www-data:rX /path/to/app/$dir
done

# Set perms for writable dirs
for dir in storage bootstrap/cache database; do
    sudo setfacl -R -m u:www-data:rwX /path/to/app/$dir
done
```

Test that your changes worked:
```bash
sudo -u www-data ls -A /path/to/app
```
