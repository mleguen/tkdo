FROM php:8.1-cli
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Create the dev user with specified GID and UID
ARG USER_UID=1000
ARG USER_GID=$USER_UID
ARG USER_UNAME=composer
ARG USER_GNAME=$USER_UNAME
RUN groupadd --gid $USER_GID --non-unique $USER_GNAME && \
    useradd --create-home --gid $USER_GNAME --groups audio,video --uid $USER_UID --non-unique $USER_UNAME

RUN apt update && apt install --yes \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install -j$(nproc) \
    zip
