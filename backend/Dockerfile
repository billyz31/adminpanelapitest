FROM php:8.2-fpm

# 安裝系統依賴和PHP擴展
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# 安裝Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 設置工作目錄
WORKDIR /var/www/html

# 啟動PHP-FPM服務
CMD ["php-fpm"]
