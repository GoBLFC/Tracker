#!/bin/sh

cd "$(dirname "$0")/../.."
source .docker/env/postgres.env
docker compose -f docker-compose.prod.yml exec postgres pg_dumpall -U $POSTGRES_USER > pgdump.sql
