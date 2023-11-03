#!/usr/bin/env bash
#original: https://laravel-news.com/laravel-scheduler-queue-docker

set -e

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}
key=${APP_KEY:-empty}

if [ "$role" = "composer" ]; then
    echo "Composer install..."
    (cd /app && composer install)
    echo "key $key"
    if [ "$key" = "empty" ]; then
        echo "Generating app key"
        php artisan key:generate
    fi

    php artisan migrate --force
    php artisan config:cache
    php artisan config:clear

    exit 0
fi

if [ "$env" != "local" ]; then
    echo "Caching configuration..."
    (cd /app && php artisan config:cache && php artisan route:cache && php artisan view:cache)
fi

if [ "$role" = "queue" ]; then

    echo "Running the queue..."
    php /app/artisan queue:work --verbose --tries=3 --timeout=90

elif [ "$role" = "scheduler" ]; then

    while [ true ]
    do
      php /app/artisan schedule:run --verbose --no-interaction &
      sleep 60
    done

else
    echo "Could not match the container role \"$role\""
    exit 1
fi
