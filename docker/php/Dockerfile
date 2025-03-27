FROM bitnami/php-fpm:8.2

ARG UID=1000
ARG GID=1000

# Remove existing www-data user and group if they exist
RUN userdel www-data || true && \
    (getent group www-data && groupdel www-data || true) && \
    # Create new group and user
    groupadd -g $GID www-data && \
    useradd -u $UID -g www-data -m -s /bin/bash www-data

# Install dependencies and PHP extensions needed for the reminder service
RUN apt-get update && \
    apt-get install -y \
        php-dev \
        php-dom \
        php-mbstring \
        php-mysql \
        php-pdo \
        php-curl \
        php-xml \
        php-tokenizer \
        php-json \
        php-bcmath \
        php-zip \
        supervisor \
        cron \
        unzip \
        libzip-dev && \
    pecl install redis -n && \
    rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/reminder-service

# Create required directories and set permissions
RUN mkdir -p /var/www/reminder-service/storage /var/www/reminder-service/bootstrap/cache && \
    chown -R www-data:www-data /var/www/reminder-service/storage /var/www/reminder-service/bootstrap/cache && \
    chmod -R 777 /var/www/reminder-service/storage /var/www/reminder-service/bootstrap/cache

# Copy startup script
COPY startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

USER root

CMD ["/usr/local/bin/startup.sh"]
