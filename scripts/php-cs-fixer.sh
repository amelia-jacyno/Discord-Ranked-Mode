#!/bin/bash
source scripts/utils.sh

echo "Git pre-commit: Running PHP CS Fixer"

CONTAINER_ID=$(docker compose ps -q php)
echo $CONTAINER_ID;
if [[ -z "$CONTAINER_ID" ]]; then
  color_echo $RED "> ERROR - PHP container is not running"
  exit 1
fi

docker compose exec php bin/php-cs-fixer fix --dry-run > /dev/null 2>&1

STATUS=$?

if [ $STATUS -gt 0 ]; then
  color_echo $RED "> ERROR - PHP CS Fixer found problems, applying fixes"
  docker compose exec php bin/php-cs-fixer fix
  color_echo $BLUE "> INFO - You need to commit again"
  exit 1
else
  color_echo $GREEN "> PHP CS Fixer found no problems, commiting"
  exit 0
fi