#!/usr/bin/env bash

WHITE='\033[1;37m'
NC='\033[0m'

# Define environment variables...
export APP_PORT=${APP_PORT:-8888}
export APP_SERVICE=${APP_SERVICE:-"phensim"}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

RANDOM_PASSWORD=test
#$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c${1:-32};echo;)

# Ensure that Docker is running...
if ! docker info >/dev/null 2>&1; then
  echo -e "${WHITE}Docker is not running.${NC}" >&2

  exit 1
fi

if [ $# -gt 0 ]; then

  # Source .env file
  if [ -f ./www/phensim/.env ]; then
    echo "Sourcing .env file"
    source ./www/phensim/.env
    export DB_PASSWORD
    export DB_DATABASE
    export DB_USERNAME
  fi

  if [ "$1" == "up" ]; then

    if [ ! -f ./.deployed ]; then
      source ./deploy.conf
      sed "s/%PHENSIM_DOMAIN%/${PHENSIM_DOMAIN}/" ./www/phensim/.env.deploy | sed "s/%THREADS%/${THREADS}/" | sed "s/%DB_PASSWORD%/${RANDOM_PASSWORD}/" >./www/phensim/.env
      if [ ! -f ./www/phensim/.env ]; then
        echo -e "${WHITE}Unable to create .env file${NC}" >&2

        exit 1
      else
        source ./www/phensim/.env
        export DB_PASSWORD
        export DB_DATABASE
        export DB_USERNAME
      fi
    fi

    if ! docker-compose up -d; then
      echo -e "${WHITE}Unable to start all containers${NC}" >&2

      exit 1
    fi

  elif [ "$1" == "deploy" ]; then
    echo -e "${WHITE}Deploying PHENSIM${NC}" >&2
    if ! docker-compose exec -u phensim "$APP_SERVICE" composer install --no-dev &&
      docker-compose exec -u phensim "$APP_SERVICE" php artisan key:generate --force &&
      docker-compose exec -u phensim "$APP_SERVICE" php artisan migrate:fresh --seed --force &&
      docker-compose exec -u phensim "$APP_SERVICE" php artisan import:database; then
      echo -e "${WHITE}Unable to deploy PHENSIM${NC}" >&2

      exit 1
    fi

    touch ./.deployed

  elif [ "$1" == "composer" ]; then
    shift 1

    docker-compose exec -u phensim "$APP_SERVICE" composer "$@"

  elif [ "$1" == "artisan" ] || [ "$1" == "art" ]; then
    shift 1

    docker-compose exec -u phensim "$APP_SERVICE" php artisan "$@"

  elif [ "$1" == "tinker" ]; then
    shift 1

    docker-compose exec -u phensim "$APP_SERVICE" php artisan

  elif [ "$1" == "shell" ] || [ "$1" == "bash" ]; then
    shift 1

    docker-compose exec -u phensim "$APP_SERVICE" bash

  elif [ "$1" == "root-shell" ]; then
    shift 1

    docker-compose exec "$APP_SERVICE" bash

  else

    docker-compose "$@"

  fi

else

  docker-compose ps

fi

## Determine if container is currently up...
#PSRESULT="$(docker-compose ps -q)"
#
#if docker-compose ps | grep 'Exit'; then
#  echo -e "${WHITE}Shutting down old processes...${NC}" >&2
#
#  docker-compose down >/dev/null 2>&1
#
#  EXEC="no"
#elif [ -n "$PSRESULT" ]; then
#  EXEC="yes"
#else
#  EXEC="no"
#fi
#
#if [ -f ./www/phensim/.env ]; then
#  source ./www/phensim/.env
#fi
#
#if [ $# -gt 0 ]; then
#  # Source the ".env" file so Laravel's environment variables are available...
#
#  # Proxy PHP commands to the "php" binary on the application container...
#
#  # Initiate a Laravel Tinker session within the application container...
#  elif [ "$1" == "tinker" ]; then
#    shift 1
#
#    if [ "$EXEC" == "yes" ]; then
#      docker-compose exec \
#        -u sail \
#        "$APP_SERVICE" \
#        php artisan tinker
#    else
#      sail_is_not_running
#    fi
#
#  # Proxy Node commands to the "node" binary on the application container...
#  elif [ "$1" == "node" ]; then
#    shift 1
#
#    if [ "$EXEC" == "yes" ]; then
#      docker-compose exec \
#        -u sail \
#        "$APP_SERVICE" \
#        node "$@"
#    else
#      sail_is_not_running
#    fi
#
#  # Proxy NPM commands to the "npm" binary on the application container...
#  elif [ "$1" == "npm" ]; then
#    shift 1
#
#    if [ "$EXEC" == "yes" ]; then
#      docker-compose exec \
#        -u sail \
#        "$APP_SERVICE" \
#        npm "$@"
#    else
#      sail_is_not_running
#    fi
#
#  # Proxy NPX commands to the "npx" binary on the application container...
#  elif [ "$1" == "npx" ]; then
#    shift 1
#
#    if [ "$EXEC" == "yes" ]; then
#      docker-compose exec \
#        -u sail \
#        "$APP_SERVICE" \
#        npx "$@"
#    else
#      sail_is_not_running
#    fi
#
#  # Proxy YARN commands to the "yarn" binary on the application container...
#  elif [ "$1" == "yarn" ]; then
#    shift 1
#
#    if [ "$EXEC" == "yes" ]; then
#      docker-compose exec \
#        -u sail \
#        "$APP_SERVICE" \
#        yarn "$@"
#    else
#      sail_is_not_running
#    fi
#
#  # Initiate a MySQL CLI terminal session within the "mysql" container...
#  elif [ "$1" == "mysql" ]; then
#    shift 1
#
#    if [ "$EXEC" == "yes" ]; then
#      docker-compose exec \
#        mysql \
#        bash -c 'MYSQL_PWD=${MYSQL_PASSWORD} mysql -u ${MYSQL_USER} ${MYSQL_DATABASE}'
#    else
#      sail_is_not_running
#    fi
#
#  # Initiate a PostgreSQL CLI terminal session within the "pgsql" container...
#  elif [ "$1" == "psql" ]; then
#    shift 1
#
#    if [ "$EXEC" == "yes" ]; then
#      docker-compose exec \
#        pgsql \
#        bash -c 'PGPASSWORD=${PGPASSWORD} psql -U ${POSTGRES_USER} ${POSTGRES_DB}'
#    else
#      sail_is_not_running
#    fi
#
#  # Initiate a Bash shell within the application container...
