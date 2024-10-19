#!/bin/bash

# Se placer dans le répertoire du projet
cd /var/www/html
echo "Vérification de la base de données..."

# Tant que la base de données n'est pas disponible et n'a pas été créée, attendre 3 secondes et réessayer
until php bin/console doctrine:database:create --if-not-exists; do
  >&2 echo "La base de données n'est pas disponible pour le moment, nouvelle tentative dans 3 secondes..."
  sleep 3
done

# Exécuter les migrations de la base de données
echo "Exécution des migrations de la base de données..."
php bin/console doctrine:migrations:migrate --no-interaction

# Configurer les dossiers de cache et de logs pour qu'ils soient accessibles en écriture
# (nécessaire pour que Symfony puisse écrire dans ces dossiers)
chmod -R 777 var/cache/ var/log/

# Appeler l'entrypoint de l'image php parente avec la commande passée en paramètre
exec /usr/local/bin/docker-php-entrypoint "$@"