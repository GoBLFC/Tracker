#!/bin/sh

# Create a temporary certificate if the LE certs don't exist yet
if [ ! -d /etc/letsencrypt/live/${NGINX_HOST} ]; then
	echo 'Generating temporary certificate...'
	mkdir -p /etc/letsencrypt/live/${NGINX_HOST}
	openssl req -x509 -nodes -newkey rsa:4096 -days 1 \
		-keyout "/etc/letsencrypt/live/${NGINX_HOST}/privkey.pem" \
		-out "/etc/letsencrypt/live/${NGINX_HOST}/fullchain.pem" \
		-subj "/CN=${NGINX_HOST}" \
		-addext "subjectAltName = DNS:localhost"
fi
