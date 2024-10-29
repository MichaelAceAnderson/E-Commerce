#!/bin/bash

# Navigate to the project directory
cd /var/www/html
echo "Checking the database..."

# While the database is not available and has not been created, wait 3 seconds and retry
until php bin/console doctrine:database:create --if-not-exists; do
	>&2 echo "The database is not available at the moment, retrying in 3 seconds..."
	sleep 3
done

# Execute the database migrations
echo "Executing database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Set the cache and log directories to be writable
# (necessary for Symfony to write to these directories)
chmod -R 777 var/cache/ var/log/

# Call the entrypoint of the parent php image with the passed command
exec /usr/local/bin/docker-php-entrypoint "$@"