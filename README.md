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

> **Attention** : certains hébergeurs proposent des fonctions d'upload d'archives les décompressant automatiquement,
> mais qui peuvent parfois tronquer les noms de fichiers si l'arborescence est trop profonde (`api/vendor` par exemple).
> Télécharger dans ce cas plutôt l'archive en tant que simple fichier, et la décompresser manuellement.

Puis, sur le serveur, depuis le répertoire cible :

```bash
$ cd api
$ php bin/doctrine.php orm:generate-proxies

 Processing entity "xxx"
[...]

 Proxy classes generated to "/home/lgnby/www/tkdo/api/var/doctrine/proxy"
$ php bin/doctrine.php orm:migrations:migrate
[...]
  ------------------------
  ++ finished in xxxms
  ++ used xxxM memory
  ++ xxx migrations executed
  ++ xxx sql queries
# Si vous souhaitez charger des données de test
php bin/doctrine.php fixtures:load
Purge et chargement des fixtures...
xxx créés.
[...]
OK
```

> **Note** : certains hébergeurs ne proposent pas d'accès ssh.
> Utiliser dans ce cas des outils comme [Web Console](http://web-console.org/) pour accéder à la console.

> **Note** : sur certains hébergements, le binaire php disponible dans le path est un binaire CGI et/ou en PHP 5.
> Utiliser dans ce cas `phpinfo()` pour déterminer le chemin du binaire PHP 7 utilisé pour l'exécution des pages Web,
> et exécuter `bin/doctrine.php` avec le binaire CLI correpondant (`/usr/local/php7.2/bin/php` par exemple).
> L'utilisation de l'option `-n` peut aussi être nécessaire pour éviter l'utilisation du php.ini du serveur,
> si ce dernier désactive par exemple l'affichage des exceptions.

## Contribution

Commencer par lire les README du [front](./front/README.md) et de l'[API](./api/README.md).

- [guide du développeur](./CONTRIBUTING.md)

### Utiliser le serveur de développement front seul

```bash
./npm-front start
```

Comme le serveur de développement front est lancé sans `--prod`,
les requêtes destinées à l'API sont interceptées et bouchonnées
(cf. [front/src/app/dev-backend.interceptor.ts](./front/src/app/dev-backend.interceptor.ts)).

### Utiliser le serveur de développement front avec l'API de développement docker

```bash
./docker-compose-api up -d
./composer-api doctrine orm:schema-tool:update
./composer-api doctrine fixtures:load
./npm-front start --prod
```

Le serveur de développement front redirige les requêtes destinées à l'API
vers l'API de développement docker
(cf. [front/src/proxy.conf.json](./front/src/proxy.conf.json)).

Pour initialiser la base de données de l'API de développement docker
(une fois qu'elle a fini de démarrer) :

```bash
./composer-api doctrine orm:schema-tool:create
```

ou pour la mettre à jour :

```bash
./composer-api doctrine orm:schema-tool:update
```

Et pour la peupler de données de test :

```bash
./composer-api doctrine fixtures:load
```

### Pré-requis au lancement des tests e2e front

- chrome/chromium installé
- variable d'environnement CHROME_BIN pointant vers le binaire de chrome/chromium
- `npm run chrome-webdriver-update` lancé pour forcer la version de webdriver chrome de protractor
  à correspondre à celle du chrome/chromium installé
