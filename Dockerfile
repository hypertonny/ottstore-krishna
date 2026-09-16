FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo_mysql \
        gd \
        mbstring \
        curl \
        xml \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules including SSL
RUN a2enmod rewrite headers ssl socache_shmcb

# Generate SSL certificate for ottbuy.io & local fallback
RUN mkdir -p /etc/ssl/certs /etc/ssl/private && \
    openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout /etc/ssl/private/apache-selfsigned.key \
        -out /etc/ssl/certs/apache-selfsigned.crt \
        -subj "/C=IN/ST=Maharashtra/L=Mumbai/O=OTTStore/CN=ottbuy.io"

# Configure Apache VirtualHost for Backend Service
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@ottbuy.io\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html/>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Security-hardened Apache configuration
RUN echo '<Directory /var/www/html/>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
\n\
# Block sensitive files\n\
<FilesMatch "(\\.sql|\\.env|\\.git|\\.bak|auth\\.php|db\\.php)">\n\
    Require all denied\n\
</FilesMatch>\n\
\n\
# Global Security Headers\n\
Header always set X-Frame-Options "SAMEORIGIN"\n\
Header always set X-Content-Type-Options "nosniff"\n\
Header always set X-XSS-Protection "1; mode=block"\n\
Header always set Referrer-Policy "strict-origin-when-cross-origin"\n\
ServerSignature Off\n\
ServerTokens Prod' > /etc/apache2/conf-available/security-custom.conf \
    && a2enconf security-custom

# Set working directory
WORKDIR /var/www/html

EXPOSE 80

