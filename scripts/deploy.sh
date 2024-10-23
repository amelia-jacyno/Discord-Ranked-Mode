#!/bin/bash

source .env

scp compose-prod.yaml $SSH_USER@$SSH_HOST:$SSH_PATH/compose.yaml \
  && ssh $SSH_USER@$SSH_HOST "cd $SSH_PATH && docker compose pull && docker compose up -d --force-recreate"