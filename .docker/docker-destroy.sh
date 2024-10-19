#!/bin/bash

# Utilisation: ./docker-destroy.sh --env=nom
# Si aucun environnement n'est passé en paramètre de ce script,
# il sera demandé à l'utilisateur de choisir l'environnement docker-compose à utiliser

# Valeurs par défaut des options
env=""

# Analyser les arguments passés en paramètre de ce script
for arg in "$@"; do
	case $arg in
		--env=*)
			# Récupérer la valeur de l'argument après le signe égal
			env="${arg#*=}"
			;;
		*)
			printf "\nUtilisation: $(basename "$0") [--env=nom]\n"
			printf "Options:\n"
			printf "  --env			Spécifie l'environnement docker-compose à utiliser (ex: --env=dev)\n"
			exit 1
			;;
	esac
done

# Si l'environnement n'est pas spécifié
if [ -z "$env" ]; then
	printf "\nChoisissez l'environnement docker-compose à utiliser :\n"
	# Lister les fichiers dont le nom commence par docker-compose, puis printf entre parenthèses ce qu'il y a entre docker-compose. et .yml sans l'extension
	ls -1 docker-compose*.yml | awk -F 'docker-compose.' '{printf " - %s\n", $2}'

	# Demander à l'utilisateur de choisir un docker-compose
	docker_choice=""
	read -p "Environnement: " docker_choice
else
	docker_choice=$env
fi

# Si aucune configuration Docker Compose ne peut être trouvée au nom de de cet environnement
if [ ! -f "$docker_choice" ] && [ ! -f "docker-compose.$docker_choice.yml" ] && [ ! -f "docker-compose.$docker_choice" ] && [ ! -f "docker-compose$docker_choice.yml" ]; then
	printf "Il n'existe aucune composition à ce nom !\n"
	exit 1
fi
if [ -f "docker-compose.$docker_choice" ]; then
	docker_choice=docker-compose.$docker_choice
elif [ -f "docker-compose$docker_choice.yml" ]; then
	docker_choice=docker-compose$docker_choice.yml
elif [ -f "docker-compose.$docker_choice.yml" ]; then
	docker_choice=docker-compose.$docker_choice.yml
elif [ -f "$docker_choice" ]; then
	docker_choice=$docker_choice
else
	printf "Aucun fichier docker-compose n'a été trouvé pour $docker_choice\n"
	exit 1
fi


# Éteindre les conteneurs de l'environnement spécifié
# supprimer les conteneurs et les réseaux
printf "Extinction de la composition $docker_choice et suppression des volumes...\n"
sudo docker compose -f $docker_choice down --volumes
# Supprimer les conteneurs de l'environnement spécifié
printf "Suppression des conteneurs de la composition $docker_choice...\n"
sudo docker compose -f $docker_choice rm -f

exit 0