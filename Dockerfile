# Start with the latest WordPress image.
FROM wordpress:latest

# Set up nodejs PPA
RUN curl -sL https://deb.nodesource.com/setup_6.x | bash

# Install server dependencies.
RUN apt-get update && apt-get install -qq -y php5-mysql nodejs build-essential pkg-config libcairo2-dev libjpeg-dev libgif-dev git subversion mysql-client zip unzip vim nano --fix-missing --no-install-recommends

# Disable PHP opcache (not great while developing)
RUN rm -rf /usr/local/etc/php/conf.d/opcache-recommended.ini
