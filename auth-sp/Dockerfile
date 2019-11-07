FROM node:10-alpine
WORKDIR /usr/local/tkdo

ENV NODE_ENV=production

# Copie les templates nécessaires au module xml-encryption
COPY node_modules/xml-encryption/lib/templates /templates

# Copie et démarre le service
COPY dist ./
CMD ["node", "main.js"]:
