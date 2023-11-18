#!/bin/bash

cd "$(dirname "$0")/../.."
source .docker/env/postgres.env
docker compose -f docker-compose.prod.yml cp pgdump.sql postgres:/tmp/dump.sql
docker compose -f docker-compose.prod.yml exec --user postgres postgres sh -c "psql -U '$POSTGRES_USER' -d '$POSTGRES_DB' < /tmp/dump.sql"
docker compose -f docker-compose.prod.yml exec postgres rm /tmp/dump.sql
