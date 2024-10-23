#!/bin/bash

source .env

docker compose -f compose-prod.yaml build && docker compose -f compose-prod.yaml push