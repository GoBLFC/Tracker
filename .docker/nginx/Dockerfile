# Install NPM dependencies and run Vite
FROM node:22 AS build-npm
WORKDIR /app
COPY . .
RUN npm install
ENV NODE_ENV=production
RUN npm run build

# Configure nginx
FROM nginx
COPY .docker/nginx/snippets /etc/nginx/snippets
COPY .docker/nginx/templates /etc/nginx/templates
COPY .docker/nginx/generate-temp-cert.sh /docker-entrypoint.d/98-generate-temp-cert.sh
COPY .docker/nginx/autoreload.sh /docker-entrypoint.d/99-autoreload.sh

# Copy static files over
WORKDIR /var/www/html
COPY --chown=www-data:www-data public public
COPY --chown=www-data:www-data --from=build-npm /app/public/build ./public/build

# Mark expected volumes
VOLUME /etc/letsencrypt /var/www/certbot

# Set up basic healthcheck
HEALTHCHECK --interval=10s --timeout=3s \
  CMD curl -f http://localhost/ping || exit 1
