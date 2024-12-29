#!/bin/bash

# Usage: ./docker-stop.sh [--env=name]
# If no environment is passed as a parameter to this script,
# all potentially running containers for this application will be stopped

# Move to the execution folder of this script
cd "$(dirname "$0")"

CONTAINER_PREFIX="ecommerce-"
DEFAULT_CONTAINERS=("nginx-c" "symfony-c" "adminer-c" "mariadb-c")

# Default option values
env=""

# Parse the arguments passed to this script
for arg in "$@"; do
	case $arg in
		--env=*)
			# Get the value of the argument after the equal sign
			env="${arg#*=}"
			;;
		*)
			printf "\nUsage: $(basename "$0") [--env=name]\n"
			printf "Options:\n"
			printf "  --env			Specifies the docker-compose environment to use (e.g., --env=dev)\n"
			exit 1
			;;
	esac
done

# If the environment is not specified
if [ -z "$env" ]; then
	# Stop the containers specific to this application
	# First cut off client access to the applications before stopping the data containers
	# to avoid potential data corruption errors
	for container in "${DEFAULT_CONTAINERS[@]}"; do
		# If the container is running
		if [ "$(sudo docker ps -q -f name=$CONTAINER_PREFIX$container)" ]; then
			# Stop the container
			sudo docker stop $CONTAINER_PREFIX$container
		# Otherwise, if the container is stopped
		elif [ "$(sudo docker ps -aq -f status=exited -f name=$CONTAINER_PREFIX$container)" ]; then
			printf "The container $CONTAINER_PREFIX$container is already stopped.\n"
		fi
	done
	exit 0
else
	# If an environment is passed as a parameter to this script
	# use it to stop the containers of the specified environment
	docker_choice=$env
fi

# If no Docker Compose configuration can be found with this environment name
while [ ! -f "docker-compose.$docker_choice.yml" ] && [ ! -f "docker-compose.$docker_choice" ] && [ ! -f "docker-compose$docker_choice.yml" ]; do
	printf "There is no composition with this name!\n"
	printf "Names searched: docker-compose.$docker_choice.yml, docker-compose.$docker_choice, docker-compose$docker_choice.yml\n"
	exit 1
done
if [ -f "docker-compose.$docker_choice" ]; then
	docker_choice=docker-compose.$docker_choice
elif [ -f "docker-compose$docker_choice.yml" ]; then
	docker_choice=docker-compose$docker_choice.yml
else
	docker_choice=docker-compose.$docker_choice.yml
fi
printf "You have chosen: $docker_choice\n"

# Stop the containers of the specified environment
printf "Shutting down the composition $docker_choice...\n"
sudo docker compose -f $docker_choice down

exit 0