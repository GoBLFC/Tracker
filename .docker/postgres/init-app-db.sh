#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
	CREATE USER tracker;
	CREATE DATABASE tracker;
	GRANT ALL PRIVILEGES ON DATABASE tracker TO tracker;
EOSQL
