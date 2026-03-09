set -e

docker compose exec web sh -lc 'cd /var/www/html/api && composer install' || \
  docker compose run --rm web sh -lc 'cd /var/www/html/api && composer install'
