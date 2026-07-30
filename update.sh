FULL_REPO_PATH=$(git rev-parse --show-toplevel)
REPO_BASENAME=$(basename "$FULL_REPO_PATH")

cd "$FULL_REPO_PATH"

echo "Checking for updates..."

CURRENT_COMMIT=$(git rev-parse HEAD)
git fetch origin master
REMOTE_COMMIT=$(git rev-parse origin/master)

if [ "$CURRENT_COMMIT" = "$REMOTE_COMMIT" ]; then
    echo "No updates found."
    exit 0
fi

echo "Backing up..."
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
cd "../"
cp -R "$FULL_REPO_PATH" "${REPO_BASENAME}_backup_${TIMESTAMP}"

echo "Pulling..."
cd "$FULL_REPO_PATH"
git pull

echo "Reinstalling dependencies..."
composer install --no-dev --optimize-autoloader
npm ci

echo "Building frontend assets..."
npm run build

echo "Caching Laravel config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setting up DB..."
php artisan migrate --force

echo "Optimizing..."
php artisan optimize

echo "Reloading services..."
sudo systemctl reload php8.4-fpm
sudo systemctl reload caddy

echo "Update complete. If Caddy failed to reload, run \`sudo systemctl restart caddy\`."
