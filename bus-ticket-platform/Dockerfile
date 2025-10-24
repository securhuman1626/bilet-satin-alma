FROM php:8.1-apache

# Gerekli PHP eklentilerini kur
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite

# ServerName uyarısını kapat
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 1. DATABASE KLASÖRÜNÜ OLUŞTUR (Build aşamasında var olmalı ki chmod çalışsın)
RUN mkdir -p /var/www/html/database

# 2. İZİNLERİ AYARLA: Sahipliği ve yazma iznini DB klasörüne ver (GEREKLİ)
RUN chown -R www-data:www-data /var/www/html/database \
    && chmod -R 775 /var/www/html/database

# 3. APACHE HTACCESS İZİNLERİ: (Kırılmaz boşluk hatası çözüldü)
RUN echo "<Directory /var/www/html>" >> /etc/apache2/apache2.conf \
    && echo "    AllowOverride All" >> /etc/apache2/apache2.conf \
    && echo "</Directory>" >> /etc/apache2/apache2.conf

EXPOSE 80