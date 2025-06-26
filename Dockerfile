FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Change current user to www
USER www-data

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
RUN npm install

# Build assets
RUN npm run build

# Change back to root user
USER root

# Create startup script
RUN echo '#!/bin/bash\n\
    # Start supervisor in the background\n\
    /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &\n\
    \n\
    # Wait a moment for supervisor to start\n\
    sleep 2\n\
    \n\
    # Start PHP-FPM in the foreground\n\
    php-fpm' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Expose port 9000 and start the application
EXPOSE 9000
CMD ["/usr/local/bin/start.sh"]
