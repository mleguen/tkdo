FROM node:10-alpine
WORKDIR /usr/local/tkdo/auth

# Installe d'abord les dépendances pour que ça ne soit refait que si le package.json change
COPY package*.json ./
ARG NPM_CONFIG_REGISTRY
RUN npm install --production

# Copie l'application
COPY dist ./

# Demarre le serveur
CMD ["node", "main.js"]:
