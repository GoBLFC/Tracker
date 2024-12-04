#!/bin/bash

cd "$(dirname "$0")/../.."
source .docker/postgres.env
docker compose cp pgdump.sql postgres:/tmp/dump.sql
docker compose exec --user postgres postgres sh -c "psql -U '$POSTGRES_USER' -d '$POSTGRES_DB' < /tmp/dump.sql"
docker compose exec postgres rm /tmp/dump.sql
