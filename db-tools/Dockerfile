FROM node:10-alpine
WORKDIR /usr/local/tkdo

ENV NODE_ENV=production

RUN npm install --global typeorm

# Copie le service
COPY dist ./
