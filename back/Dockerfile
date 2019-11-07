FROM node:10-alpine
WORKDIR /usr/local/tkdo

ENV NODE_ENV=production

# Copie le service
COPY dist ./

# Demarre le serveur
CMD ["node", "main.js"]:
