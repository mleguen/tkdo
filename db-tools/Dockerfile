FROM node:10-alpine
WORKDIR /usr/local/tkdo

ENV NODE_ENV=production

RUN npm install --global mysql typeorm

COPY package*.json ./
RUN npm install

COPY dist ./
WORKDIR db-tools
