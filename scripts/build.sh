#!/bin/bash

source .env

docker compose -f compose-prod.yaml build
docker push ghcr.io/ame180/drm-app:latest