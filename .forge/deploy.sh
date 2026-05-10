#!/bin/bash

set -e

cd /home/forge/soccerdads.com.au

git pull origin main

composer install --no-interaction --prefer-dist --optimize-autoloader

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix storage symlink — Forge zero-downtime deployments recreate public/storage
# pointing at the release folder. Force it to point at shared storage instead
# so uploaded files persist across releases.
ln -sfn /home/forge/soccerdads.com.au/shared/storage/app/public \
        /home/forge/soccerdads.com.au/current/public/storage

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service php8.2-fpm reload ) 9>/tmp/fpmlock
