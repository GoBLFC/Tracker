#!/bin/sh
trap exit TERM

if [ ! -f /etc/letsencrypt/installed ]; then
	echo "First run; running certbot certonly in 30 seconds"
	sleep 30s

	IFS=', ' read -r -a domains <<< "$LETSENCRYPT_DOMAINS"
	for d in "${!domains[@]}"; do
		domains[$d]="-d ${domains[$d]}"
	done

	rm -rf /etc/letsencrypt/live
	certbot certonly --non-interactive \
		${LETSENCRYPT_DRY_RUN:+--dry-run} \
		--agree-tos \
		--email ${LETSENCRYPT_EMAIL} \
		--webroot -w /var/www/certbot \
		${domains[@]} \
	&& touch /etc/letsencrypt/installed
fi

while :; do
	certbot renew
	sleep 12h & wait ${!}
done
