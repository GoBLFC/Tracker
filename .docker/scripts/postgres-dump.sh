#!/bin/sh

cd "$(dirname "$0")/../.."
source .docker/postgres.env
docker compose exec postgres pg_dumpall -U $POSTGRES_USER > pgdump.sql
