#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	# Installer les dépendances si vendor/ est vide
	if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
		composer install --prefer-dist --no-progress --no-interaction
	fi

	php bin/console -V

	# Attendre que la base PostgreSQL soit prête
	echo 'Waiting for PostgreSQL to be ready...'
	ATTEMPTS_LEFT_TO_REACH_DATABASE=60
	until pg_isready -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" || [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; do
		echo "Tentative : pg_isready -h $POSTGRES_HOST -p $POSTGRES_PORT -U $POSTGRES_USER"
		sleep 2
		ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
		echo "Still waiting for PostgreSQL... $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left."
	done

	if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
		echo 'PostgreSQL is not up or not reachable.'
		exit 1
	fi
	echo 'PostgreSQL is now ready and reachable.'

	# Lancer les migrations Doctrine si elles existent
	if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
		php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
	fi

	echo 'PHP app ready!'
fi

exec docker-php-entrypoint "$@"
