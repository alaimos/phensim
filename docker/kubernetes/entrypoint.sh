#!/bin/bash
set -e

check_and_create() {
    if [ ! -d "$1" ]; then
        mkdir -p "$1"
        chown -R www-data:www-data "$1"
    fi
}

check_and_link() {
    if [ ! -L "$2" ]; then
        ln -s "$1" "$2"
    fi
}

artisan() {
    /usr/bin/php /opt/apps/phensim/artisan "$@"
}

if [ "$NO_INIT" != "true" ] && [ -f "/usr/bin/php" ] && [ -f "/opt/apps/phensim/artisan" ]; then
    PW="$(pwd)"
    check_and_create "/opt/apps/phensim/storage/logs"
    check_and_create "/opt/apps/phensim/storage/framework/cache"
    check_and_create "/opt/apps/phensim/storage/framework/cache/data"
    check_and_create "/opt/apps/phensim/storage/framework/sessions"
    check_and_create "/opt/apps/phensim/storage/framework/views"
    check_and_create "/opt/apps/phensim/storage/framework/testing"
    check_and_create "/opt/apps/phensim/storage/app/public"
    check_and_create "/var/www"
    check_and_create "/opt/apps/phensim/storage/app/mithril"
    check_and_link "/opt/apps/phensim/storage/app/mithril" "/var/www/.mithril"
    artisan event:cache
    artisan route:cache
    artisan view:cache
    artisan config:cache
    artisan storage:link
    cd "$PW"
fi

exec "$@"
