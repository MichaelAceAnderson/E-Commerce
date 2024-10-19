#!/bin/bash

# Se placer dans le répertoire du projet
cd /var/www/html
echo "Vérification de la base de données..."

# Tant que la base de données n'est pas disponible et n'a pas été créée, attendre 3 secondes et réessayer
until php bin/console doctrine:database:create --if-not-exists; do
  >&2 echo "La base de données n'est pas disponible pour le moment, nouvelle tentative dans 3 secondes..."
  sleep 3
done

# Fichier de verrouillage pour vérifier si les données initiales ont déjà été insérées
LOCKFILE=first_run_done.lock

# Vérifier s'il s'agit du premier démarrage du conteneur
if [ ! -f $LOCKFILE ]; then
	# Vider la base de données
	echo "Suppression de la base de données..."
	php bin/console doctrine:schema:drop --force --full-database

	# Créer le schéma et les tables de la base de données
	echo "Création du schéma de la base de données..."
	php bin/console doctrine:schema:update --force --complete

	# Insérer les données initiales
	echo "Insertion des données initiales..."
	php bin/console doctrine:fixtures:load --no-interaction

	# Créer le fichier de premier démarrage
	touch $LOCKFILE
	echo "Le conteneur a inséré les données initiales."
else
	echo "Le conteneur a déjà inséré les données initiales, il ne le fera pas à nouveau."
fi

# Configurer les dossiers de cache et de logs pour qu'ils soient accessibles en écriture
# (nécessaire pour que Symfony puisse écrire dans ces dossiers)
chmod -R 777 var/cache/ var/log/

# Appeler l'entrypoint de l'image php parente avec la commande passée en paramètre
exec /usr/local/bin/docker-php-entrypoint "$@"