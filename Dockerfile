# Étape 1 : Construction de l’application (dépendances Composer)
FROM composer:2 AS vendor-builder
WORKDIR /app
# Copie seulement les fichiers de dépendances pour profiter du cache Docker
COPY composer.json composer.lock ./
RUN composer install --no-scripts --optimize-autoloader

# Étape 2 : Construction de l’image PHP avec les dépendances système
FROM php:8.4-fpm
WORKDIR /var/www

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    git \
    libfreetype6-dev \
    libjpeg-dev \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zip \
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

# Copier le script de démarrage hors du dossier monté par un volume
COPY start.sh /start.sh
# Conversion CRLF -> LF pour compatibilité Linux
RUN apt-get update && apt-get install -y dos2unix && dos2unix /start.sh && apt-get purge -y dos2unix && apt-get autoremove -y && rm -rf /var/lib/apt/lists/*

# Attribuer les droits (optionnel à laisser dans le container ou gérer via un script de démarrage)
RUN chown -R www-data:www-data /var/www && chmod -R ug+rwX /var/www

# Rendre le script de démarrage exécutable
RUN chmod +x /start.sh

EXPOSE 9000

# Définir le script de démarrage comme point d'entrée
ENTRYPOINT ["/start.sh"]
