#!/bin/bash

/usr/bin/php /opt/apps/phensim/artisan config:cache
[ -f /etc/nginx/sites-available/default ] && rm /etc/nginx/sites-available/default
envsubst "\$FPM_HOST" < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default
/usr/sbin/nginx -g "daemon off;"
