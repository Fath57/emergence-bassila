web: php artisan storage:link && vendor/bin/heroku-php-nginx -C nginx_app.conf public/
worker: php artisan queue:work --tries=3 --timeout=90 --sleep=3
release: php artisan migrate --force