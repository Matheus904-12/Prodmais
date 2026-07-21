# Dockerfile Otimizado para Prodmais
FROM php:8.2-apache

# Instalar extensões PHP necessárias (Camada de sistema - raramente muda)
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

# Configurações do Apache
RUN a2enmod rewrite headers
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# OTIMIZAÇÃO: Instalar dependências ANTES de copiar o código todo
# Isso evita rodar composer install toda vez que uma linha de CSS/JS muda
COPY composer.json ./
RUN if [ -f composer.json ]; then \
    composer install --no-dev --no-interaction --no-autoloader --no-scripts || true; \
    fi

# Copiar o restante da aplicação
COPY . .

# Finalizar autoload e permissões
RUN if [ -f composer.json ]; then \
    composer dump-autoload --optimize --no-interaction || true; \
    fi

# Criar diretórios de dados e ajustar permissões
RUN mkdir -p data/uploads data/logs data/cache data/lattes_xml data/backups \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/data

EXPOSE 80
CMD ["apache2-foreground"]