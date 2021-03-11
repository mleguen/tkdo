FROM node:12

# Install Chrome stable
RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - \
        && sh -c 'echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/chrome.list' \
        && apt-get update && apt-get install -y --no-install-recommends \
        google-chrome-stable \
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
