FROM certbot/certbot

# Mark expected volumes
VOLUME /etc/letsencrypt /var/www/certbot

# Copy the runner script and set the entrypoint to it
COPY .docker/certbot/certbot-runner.sh /usr/local/bin/certbot-runner
ENTRYPOINT /bin/sh /usr/local/bin/certbot-runner
