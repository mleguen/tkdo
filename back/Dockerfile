FROM node:10-alpine

RUN apk add --no-cache git

ENV NODE_ENV=production

USER node
RUN mkdir /home/node/tkdo
WORKDIR /home/node/tkdo

COPY --chown=node package*.json ./
RUN npm install

COPY --chown=node dist ./
WORKDIR back

CMD ["node", "src/main.js"]:
