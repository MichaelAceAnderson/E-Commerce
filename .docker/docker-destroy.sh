#!/bin/bash

# Usage: ./docker-destroy.sh --env=name
# If no environment is passed as a parameter to this script,
# the user will be asked to choose the docker-compose environment to use

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
	printf "\nChoose the docker-compose environment to use:\n"
	# List files whose names start with docker-compose, then print in parentheses what is between docker-compose. and .yml without the extension
	ls -1 docker-compose*.yml | awk -F 'docker-compose.' '{printf " - %s\n", $2}'

	# Ask the user to choose a docker-compose
	docker_choice=""
	read -p "Environment: " docker_choice
else
	docker_choice=$env
fi

# If no Docker Compose configuration can be found with this environment name
if [ ! -f "$docker_choice" ] && [ ! -f "docker-compose.$docker_choice.yml" ] && [ ! -f "docker-compose.$docker_choice" ] && [ ! -f "docker-compose$docker_choice.yml" ]; then
	printf "There is no composition with this name!\n"
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
	printf "No docker-compose file was found for $docker_choice\n"
	exit 1
fi

# Shut down the containers of the specified environment
# remove containers and networks
printf "Shutting down the composition $docker_choice and removing volumes...\n"
sudo docker compose -f $docker_choice down --volumes
# Remove the containers of the specified environment
printf "Removing the containers of the composition $docker_choice...\n"
sudo docker compose -f $docker_choice rm -f

exit 0
