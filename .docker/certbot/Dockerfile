FROM certbot/certbot

# Mark expected volumes
VOLUME /etc/letsencrypt /var/www/certbot

# Copy the runner script and set the entrypoint to it
COPY --chmod=755 .docker/certbot/certbot-runner.sh /usr/local/bin/certbot-runner
ENTRYPOINT ["/usr/local/bin/certbot-runner"]
