#!/bin/bash

# Utilisation: ./docker-stop.sh [--env=nom]
# Si aucun environnement n'est passé en paramètre de ce script, 
# tous les conteneurs potentiellement en cours d'exécution pour cette application seront arrêtés

# Se placer dans le dossier d'exécution de ce script
cd "$(dirname "$0")"

CONTAINER_PREFIX="ecommerce-"
DEFAULT_CONTAINERS=("nginx-c" "symfony-c" "adminer-c" "mariadb-c")

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
	# Arrêter les conteneurs spécifiques à cette application
	# Couper d'abord l'accès des clients aux applications avant de stopper les conteneurs de données
	# pour éviter des erreurs potentielles de corruption de données
	for container in "${DEFAULT_CONTAINERS[@]}"; do
		# Si le conteneur est en cours d'exécution
		if [ "$(sudo docker ps -q -f name=$CONTAINER_PREFIX$container)" ]; then
			# Arrêter le conteneur
			sudo docker stop $CONTAINER_PREFIX$container
		# Sinon, si le conteneur est arrêté
		elif [ "$(sudo docker ps -aq -f status=exited -f name=$CONTAINER_PREFIX$container)" ]; then
			printf "Le conteneur $CONTAINER_PREFIX$container est déjà arrêté.\n"
		fi
	done
	exit 0
else
	# Si un environnement est passé en paramètre de ce script
	# l'utiliser pour arrêter les conteneurs de l'environnement spécifié
	docker_choice=$env
fi

# Si aucune configuration Docker Compose ne peut être trouvée au nom de de cet environnement
while [ ! -f "docker-compose.$docker_choice.yml" ] && [ ! -f "docker-compose.$docker_choice" ] && [ ! -f "docker-compose$docker_choice.yml" ]; do
	printf "Il n'existe aucune composition à ce nom !\n"
	printf "Noms cherchés: docker-compose.$docker_choice.yml, docker-compose.$docker_choice, docker-compose$docker_choice.yml\n"
	exit 1
done
if [ -f "docker-compose.$docker_choice" ]; then
	docker_choice=docker-compose.$docker_choice
elif [ -f "docker-compose$docker_choice.yml" ]; then
	docker_choice=docker-compose$docker_choice.yml
else
	docker_choice=docker-compose.$docker_choice.yml
fi
printf "Vous avez choisi: $docker_choice\n"

# Arrêter les conteneurs de l'environnement spécifié
printf "Extinction de la composition $docker_choice...\n"
sudo docker compose -f $docker_choice down

exit 0