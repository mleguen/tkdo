# Manuel du développeur

## Serveurs de développement

### Frontend uniquement

Démarrer le serveur de développement frontend seul :

```bash
npm start
```

Le backend est alors bouchonné en interceptant les requêtes qui lui sont destinées
(cf. [src/app/dev-backend.interceptor.ts](./src/app/dev-backend.interceptor.ts)).

### Frontend et backend

Commencer par démarrer le serveur de développement backend :

```bash
(cd api && composer start)
```

puis démarrer le serveur de développement frontend en mode production :

```bash
npm start -- --prod
```

Les requêtes destinées au backend sont alors redirigées
par le serveur de développement frontend vers le serveur de développement backend
(cf. [src/proxy.conf.json](./src/proxy.conf.json)).

## Tests

### Frontend

Pré-requis:
- chrome/chromium installé
- variable d'environnement CHROME_BIN pointant vers le binaire de chrome/chromium
- `npm run chrome-webdriver-update` lancé pour forcer la version de webdriver chrome de protractor
  à correspondre à celle du chrome/chromium installé

### Tests unitaires

```bash
npm test
```

### Tests de bout en bout

```bash
npm run e2e
```

## Backend

```bash
(cd api && composer test)
```
