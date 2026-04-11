web: bin/web
worker: php artisan queue:work --tries=3 --timeout=90 --sleep=3
release: php artisan migrate --force