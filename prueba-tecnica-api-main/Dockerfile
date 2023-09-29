# proyecto base
FROM ubuntu:latest
# copio directorio actual a directorio de trabajo
COPY . /var/www/html/

ENV DEBIAN_FRONTEND=noninteractive

# Instalar las dependencias de PHP necesarias
RUN apt-get update && apt-get install -y \
         apache2 \
         libpq-dev 

# modo sobreescritura
RUN a2enmod rewrite

# instalo php 8.1
RUN apt-get install -y php8.1 php8.1-cli php8.1-common php8.1-pdo php8.1-pgsql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath

# activo la configuracion necesaria para habilitar los nombres de dominio en la configuracion de apache
RUN cat /var/www/html/devops/bravus.conf > /etc/apache2/sites-available/000-default.conf

CMD service apache2 start && tail -f /var/log/apache2/access.log