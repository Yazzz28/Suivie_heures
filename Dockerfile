# Étape 1 : Construction de l’application (dépendances Composer)
FROM composer:2 as vendor-builder
WORKDIR /app
# Copie seulement les fichiers de dépendances pour profiter du cache Docker
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --optimize-autoloader

# Étape 2 : Construction de l’image PHP avec les dépendances système
FROM php:8.2-fpm
WORKDIR /var/www

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libzip-dev \
    default-mysql-client \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libpq-dev \
    libonig-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql zip xml mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier et activer la configuration d’OPcache
COPY ./opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copier Composer depuis la première étape, ou directement depuis l’image de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier les dépendances installées dans vendor depuis le build stage précédent
COPY --from=vendor-builder /app/vendor ./vendor
# Copier le reste des fichiers de l’application
COPY . .

# Attribuer les droits (optionnel à laisser dans le container ou gérer via un script de démarrage)
RUN chown -R www-data:www-data /var/www && chmod -R ug+rwX /var/www

# Rendre le script de démarrage exécutable
RUN chmod +x ./start.sh

EXPOSE 9000

# Définir le script de démarrage comme point d'entrée
ENTRYPOINT ["./start.sh"]
