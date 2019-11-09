FROM node:10-alpine
WORKDIR /usr/local/tkdo

ENV NODE_ENV=production

RUN npm install --global mysql typeorm

USER node
RUN mkdir /home/node/tkdo
WORKDIR /home/node/tkdo

COPY --chown=node package*.json ./
RUN npm install

COPY --chown=node dist ./
WORKDIR db-tools
