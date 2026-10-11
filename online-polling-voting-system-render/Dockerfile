FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends git openssh-client ca-certificates procps && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql
WORKDIR /workspace
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
