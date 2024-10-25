#!/bin/bash

source .env

SSH_APP_PREFIX=${SSH_APP_PREFIX:-${SSH_PATH}}

scp compose-prod.yaml $SSH_USER@$SSH_HOST:$SSH_PATH/compose.yaml \
  && ssh $SSH_USER@$SSH_HOST "cd $SSH_PATH \
    && docker compose pull \
    && docker compose down \
    && (docker volume rm ${SSH_APP_PREFIX}_assets || true) \
    && docker compose up -d --force-recreate
  "