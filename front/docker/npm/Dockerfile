FROM node:24

# Install Chrome stable & Cypress dependencies
RUN wget -O- https://dl-ssl.google.com/linux/linux_signing_key.pub | gpg --dearmor | tee /etc/apt/trusted.gpg.d/google.gpg > /dev/null \
    && sh -c 'echo "deb [arch=amd64 signed-by=/etc/apt/trusted.gpg.d/google.gpg] https://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google.list' \
    && apt-get update && apt-get install -y --no-install-recommends \
    google-chrome-stable \
    libasound2 \
    libgbm-dev \
    libgtk-3-0 \
    libgtk2.0-0 \
    libnotify-dev \
    libnss3 \
    libxss1 \
    libxtst6 \
    xauth \
    xvfb \
    && rm -rf /var/lib/apt/lists/*

# Create the dev user with specified GID and UID
ARG DEV_GID
ARG DEV_UID
RUN groupadd --gid $DEV_GID --non-unique dev && \
    useradd --create-home --gid dev --groups audio,video --uid $DEV_UID --non-unique dev
USER dev

RUN mkdir ~/reports

WORKDIR /mnt/tkdo/front
ENTRYPOINT ["npm"]
