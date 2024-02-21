# Define the default target
.DEFAULT_GOAL := help

# Define colors for output
COLOR_RESET := \033[0m
COLOR_INFO := \033[32m
COLOR_COMMENT := \033[33m

# Help target (displays available targets)
help:
	@awk '/^## / \
        { if (c) {printf "$(COLOR_INFO)%s$(COLOR_RESET)\n", c}; c=substr($$0, 4); next } \
        c && /(^[[:alpha:]][[:alnum:]_-]+:)/ \
        	{ printf "  $(COLOR_COMMENT)%s$(COLOR_RESET) %s\n", $$1, c; c=0 }' $(MAKEFILE_LIST)

##
## Containers
##

## Build Docker Compose services with no cache
build:
	cd ./.docker && docker-compose  --env-file ./../source/.env.local  build --no-cache

## Start Docker Compose services, pull images and wait for them to be up
up:
	cd ./.docker && docker-compose --env-file ./../source/.env.local  up -d

## Stop and remove Docker Compose services
down:
	cd ./.docker && docker-compose --env-file ./../source/.env.local down --remove-orphans

## Show logs
logs:
	docker-compose -f ./.docker/docker-compose.yml logs

##
## Migration
##

## Migration status
migration-status:
	docker run -it -v ./source/.env.local:/config/.env -v ./db:/volume/ --network=docker_default ghcr.io/amacneil/dbmate --env-file "/config/.env" --migrations-dir "/volume/migrations" status

## Migrate DB
migration-up:
	docker run -it -v ./source/.env.local:/config/.env -v ./db:/volume/ --network=docker_default ghcr.io/amacneil/dbmate --env-file "/config/.env" --migrations-dir "/volume/migrations" migrate

## Rollback the most recent migration
migration-down:
	docker run -it -v ./source/.env.local:/config/.env -v ./db:/volume/ --network=docker_default ghcr.io/amacneil/dbmate --env-file "/config/.env" --migrations-dir "/volume/migrations" down

## Create migration (with name=<migration_name> command line argument)
migration-new:
	docker run -it -v ./db:/volume/ ghcr.io/amacneil/dbmate --migrations-dir "/volume/migrations" new $(name)