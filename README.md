# Tkdo

Tirage au sort de cadeaux, en famille ou entre amis.

## Déploiement sur serveur Apache

Dans les explications ci-dessous, le *répertoire cible* désigne le répertoire du serveur Apache
où sera installé tkdo.

Pré-requis :
- le module `mod_rewrite` est installé sur votre serveur Apache
- l'utilisation de fichiers `.htaccess` dans le répertoire cible est autorisée 
  (à défaut, copier le contenu de [.htaccess](./.htaccess) dans une directive `Directory` dans la configuration Apache)
- le répertoire cible correspond à la racine de votre hôte Apache
  (à défaut, ajouter `--base-href /prefixe/du/repertoire/cible` à la fin du script `build` dans [package.json](./package.json))
- votre hôte Apache est accessible en HTTPS
  (à défaut, commenter la règle de redirection vers HTTPS dans [.htaccess](./.htaccess))

Installer les dépendances, construire et packager :

```bash
./npm-front install
./composer-api install
./apache-pack
```

Décompresser ensuite dans le répertoire cible l'archive `tkdo.tar.gz` générée.

## Contribution

Commencer par lire les README du [front](./front/README.md) et de l'[API](./api/README.md).

- [guide du développeur](./CONTRIBUTING.md)

### Utiliser le serveur de développement front seul

Lorsque le serveur de développement front est lancé sans `--prod` :

```bash
./npm-front start
```

alors l'API est bouchonnée en interceptant les requêtes qui lui sont destinées
(cf. [front/src/app/dev-backend.interceptor.ts](./front/src/app/dev-backend.interceptor.ts)).

### Utiliser les serveurs de développement front et api ensemble

Lorsque le serveur de développement front est lancé avec `--prod` :

```bash
./npm-front start -- --prod
```

alors les requêtes destinées à l'API sont redirigées vers le serveur de développement API
(cf. [front/src/proxy.conf.json](./front/src/proxy.conf.json)),
qui doit lui aussi être démarré :

```bash
./composer-api start
```

### Pré-requis au lancement des tests e2e front

- chrome/chromium installé
- variable d'environnement CHROME_BIN pointant vers le binaire de chrome/chromium
- `npm run chrome-webdriver-update` lancé pour forcer la version de webdriver chrome de protractor
  à correspondre à celle du chrome/chromium installé
