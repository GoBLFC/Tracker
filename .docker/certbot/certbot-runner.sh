#!/bin/sh
trap exit TERM

if [ ! -f /etc/letsencrypt/installed ]; then
	echo "First run; running certbot certonly in 30 seconds"
	sleep 30s

	args=""
	OIFS=$IFS; IFS=", "
	for domain in $LETSENCRYPT_DOMAINS; do
		args+=" -d $domain"
	done
	IFS=$OIFS

	rm -rf /etc/letsencrypt/live
	certbot certonly --non-interactive \
		${LETSENCRYPT_DRY_RUN:+--dry-run} \
		--agree-tos \
		--email ${LETSENCRYPT_EMAIL} \
		--webroot -w /var/www/certbot \
		${args} \
	&& touch /etc/letsencrypt/installed
fi

while :; do
	certbot renew
	sleep 12h & wait ${!}
done
