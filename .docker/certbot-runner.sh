#!/bin/sh
trap exit TERM

if [ ! -f /etc/letsencrypt/installed ]; then
	echo "First run; running certbot certonly in 30 seconds"
	sleep 30s
	rm -rf /etc/letsencrypt/live
	certbot certonly --non-interactive \
		${LETSENCRYPT_DRY_RUN:+--dry-run} \
		--force-renewal \
		--agree-tos \
		--email ${LETSENCRYPT_EMAIL} \
		--webroot -w /var/www/certbot \
		-d ${LETSENCRYPT_DOMAIN} \
		-d www.${LETSENCRYPT_DOMAIN} \
	&& touch /etc/letsencrypt/installed
fi

while :; do
	certbot renew
	sleep 12h & wait ${!}
done
