#!/bin/bash

# Navigate to the project directory
cd /var/www/html
echo "Checking the database..."

# While the database is not available and has not been created, wait 3 seconds and retry
until php bin/console doctrine:database:create --if-not-exists; do
	>&2 echo "The database is not available at the moment, retrying in 3 seconds..."
	sleep 3
done

# Lock file to check if initial data has already been inserted
LOCKFILE=first_run_done.lock

# Check if this is the first start of the container
if [ ! -f $LOCKFILE ]; then
	# Drop the database
	echo "Dropping the database..."
	php bin/console doctrine:schema:drop --force --full-database

	# Create the database schema and tables
	echo "Creating the database schema..."
	php bin/console doctrine:schema:update --force --complete

	# Insert initial data
	echo "Inserting initial data..."
	php bin/console doctrine:fixtures:load --no-interaction

	# Create the first run lock file
	touch $LOCKFILE
	echo "The container has inserted the initial data."
else
	echo "The container has already inserted the initial data, it will not do it again."
fi

# Set cache and log directories to be writable
# (necessary for Symfony to write to these directories)
chmod -R 777 var/cache/ var/log/

# Call the entrypoint of the parent php image with the passed command
exec /usr/local/bin/docker-php-entrypoint "$@"