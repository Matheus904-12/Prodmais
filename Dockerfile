# Dockerfile para Prodmais
FROM php:8.2-apache

# Instalar extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libxml2-dev \
    libcurl4-openssl-dev \
    libsqlite3-dev \
    libonig-dev \
    && docker-php-ext-install \
    zip \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    mysqli \
    mbstring \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Configurar DocumentRoot para apontar para public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configurar AllowOverride para permitir .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar arquivos da aplicação
WORKDIR /var/www/html
COPY . .

# Instalar dependências PHP (se composer.json existir)
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --no-interaction || true; fi

# Criar diretórios necessários
RUN mkdir -p /var/www/html/data/uploads \
    /var/www/html/data/logs \
    /var/www/html/data/cache \
    /var/www/html/data/lattes_xml \
    /var/www/html/data/backups

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/data

# Expor porta 80
EXPOSE 80

# Comando padrão
CMD ["apache2-foreground"]