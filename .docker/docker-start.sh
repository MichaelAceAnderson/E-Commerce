#!/bin/bash

# Utilisation: ./docker-start.sh [--env=nom] [--rebuild] [--daemon]
# Si aucun argument n'est passé en paramètre de ce script, l'utilisateur devra choisir l'environnement docker-compose à utiliser

# Se placer dans le dossier d'exécution de ce script
cd "$(dirname "$0")"

# Valeurs par défaut des options
env=""
rebuild=false
daemon=false

# Analyser les arguments passés en paramètre de ce script
for arg in "$@"; do
	case $arg in
		--env=*)
			# Récupérer la valeur de l'argument après le signe égal
			env="${arg#*=}"
			;;
		--rebuild)
			rebuild=true
			;;
		--daemon)
			daemon=true
			;;
		*)
			printf "\nUtilisation: $(basename "$0") [--env=nom] [--rebuild] [--daemon]\n"
			printf "Options:\n"
			printf "  --env			Spécifie l'environnement docker-compose à utiliser (ex: --env=dev)\n"
			printf "  --rebuild		Force la reconstruction des images et la recréation des conteneurs\n"
			printf "	NOTE: Si vous avez sélectionné un environnement différent de d'habitude, vous devrez utiliser cette option pour que les changements s'appliquent correctement.\n"
			printf "	En effet, les noms des conteneurs étant les mêmes, un changement d'environnement sans reconstruction lancera en fait les conteneurs du précédent environnement.\n"
			printf "  --daemon		Démarrer les conteneurs en arrière-plan\n"
			exit 1
			;;
	esac
done

# Si ce n'est pas fait, démarrer le service docker
# Tenter d'utiliser systemctl pour démarrer le service docker
sudo systemctl start docker >/dev/null 2>/dev/null
if [ $? -ne 0 ]; 
then
    # Si systemctl ne fonctionne pas, utiliser service
    printf "\nSystemctl a échoué, utilisation de service pour démarrer Docker\n"
    sudo service docker start
else
    # Si systemctl a fonctionné 
    printf "\nSystemctl a réussi à démarrer le service docker"
fi

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
while [ ! -f "docker-compose.$docker_choice.yml" ] && [ ! -f "docker-compose.$docker_choice" ] && [ ! -f "docker-compose$docker_choice.yml" ]; do
	printf "Il n'existe aucune composition à ce nom !\n"
	printf "Noms cherchés: docker-compose.$docker_choice.yml, docker-compose.$docker_choice, docker-compose$docker_choice.yml\n"
	read -p "Environnement: " docker_choice
done
if [ -f "docker-compose.$docker_choice" ]; then
	docker_choice=docker-compose.$docker_choice
elif [ -f "docker-compose$docker_choice.yml" ]; then
	docker_choice=docker-compose$docker_choice.yml
else
	docker_choice=docker-compose.$docker_choice.yml
fi
printf "\nVous avez choisi: $docker_choice\n"

# Si l'utilisateur souhaite forcer la reconstruction des images et la recréation des conteneurs
if [ "$rebuild" = true ]; then
	# Appeler le script de destruction des conteneurs (& volumes, ...)
	# en passant le fichier en argument
	./docker-destroy.sh --env=$docker_choice
	# Recréer les conteneurs de chaque service sans cache
	printf "Reconstruction de $docker_choice...\n"
	sudo docker compose -f $docker_choice build --no-cache
	# Démarrer le docker-compose en forçant la reconstruction/la mise à jour des images et la recréation des conteneurs
	printf "Démarrage de $docker_choice...\n"
	if [ "$daemon" = true ]; then
		sudo docker compose -f $docker_choice up --pull="missing" --build --always-recreate-deps --force-recreate --no-deps -d
	else
		sudo docker compose -f $docker_choice up --pull="missing" --build --always-recreate-deps --force-recreate --no-deps
	fi
# Si l'utilisateur ne souhaite pas forcer la reconstruction des images et la recréation des conteneurs
else
    # Script pour démarrer le conteneur compose normalement
	printf "Démarrage de $docker_choice...\n"
	if [ "$daemon" = true ]; then
		sudo docker compose -f $docker_choice up -d
	else
		sudo docker compose -f $docker_choice up
	fi
fi

exit 0