# Tkdo

Tirage au sort de cadeaux, en famille ou entre amis.

<table><tr>
  <td width="20%"><img src="doc/connexion.png?raw=true" alt="Connexion"></td>
  <td width="20%"><img src="doc/occasion.png?raw=true" alt="Occasion"></td>
  <td width="20%"><img src="doc/idee-1.png?raw=true" alt="Liste d'idées"></td>
  <td width="20%"><img src="doc/idee-2.png?raw=true" alt="Liste d'idées (suite)"></td>
  <td width="20%"><img src="doc/menus.png?raw=true" alt="Menus"></td>
</tr></table>

## Installation sur serveur Apache

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

Construction du package d'installation :

```bash
./apache-pack
```

Décompresser ensuite dans le répertoire cible l'archive `tkdo-v*.tar.gz` générée.

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
$ php bin/doctrine.php migrations:migrate
[...]
  ------------------------
  ++ finished in xxxms
  ++ used xxxM memory
  ++ xxx migrations executed
  ++ xxx sql queries
```

Initialiser ensuite la base de données avec un premier compte administrateur,
en adaptant l'e-mail passé en paramètre à vos besoins :

```bash
$ php bin/doctrine.php fixtures:load --prod --admin-email vous@votrefai.fr
Initialisation ou réinitialisation de la base de données (production)...
xxx créés.
[...]
OK
```

Ce compte administrateur (identifiant `admin`, mot de passe `admin`)
vous permettra de vous connecter à l'application pour créer ensuite d'autres comptes.

Pour des raisons de sécurité, il est fortement recommandé
de changer le mot de passe du compte `admin` dès la fin de l'installation.

> **Note 1** : certains hébergeurs ne proposent pas d'accès ssh.
> Utiliser dans ce cas des outils comme [Web Console](http://web-console.org/) pour accéder à la console.

> **Note 2** : sur certains hébergements, le binaire php disponible dans le path est un binaire CGI et/ou en PHP 5.
> Utiliser dans ce cas `phpinfo()` pour déterminer le chemin du binaire PHP 7 utilisé pour l'exécution des pages Web,
> et exécuter `bin/doctrine.php` avec le binaire CLI correpondant (`/usr/local/php7.2/bin/php` par exemple).
> L'utilisation de l'option `-n` peut aussi être nécessaire pour éviter l'utilisation du php.ini du serveur,
> si ce dernier désactive par exemple l'affichage des exceptions.

## Administration

Une fois connecté à l'application avec un compte administrateur,
la page d'administration vous permet :
- de gérer les comptes utilisateurs (création, consultation, modification, réinitialisation du mot de passe)
- de gérer les occasions (création, consultation, modification, ajout de participants ou de résultats de tirage)

## Développement

- [Historique des changements](./CHANGELOG.md).
- [Travaux futurs](./BACKLOG.md).

### Utiliser le serveur de développement front seul

```bash
./npm-front start
```

Comme le serveur de développement front est lancé sans `--prod`,
les requêtes destinées à l'API sont interceptées et bouchonnées
(cf. [front/src/app/dev-backend.interceptor.ts](./front/src/app/dev-backend.interceptor.ts)).

### Utiliser le serveur de développement front avec l'API de développement docker

Paramétrer l'API de développement docker pour que Slim s'exécute avec l'utilisateur courant
(permet d'éviter des conflits de droits sur les fichiers de cache créés par docker) :

``` bash
echo SLIM_UID=$(id -u) >> ./api/.env
echo SLIM_GUID=$(id -g) >> ./api/.env
```

```bash
./docker-compose-api up -d
./composer-api doctrine orm:schema-tool:update
./composer-api doctrine fixtures:load
./npm-front start --prod
```

Le serveur de développement front redirige les requêtes destinées à l'API
vers l'API de développement docker
(cf. [front/src/proxy.conf.json](./front/src/proxy.conf.json)).

Les logs sont affichés sur la sortie standard des conteneurs et collectés par docker-compose.
Pour les consulter (par exemple ici pour les logs de l'API, au fil de l'eau) :

```bash
./docker-compose-api logs -f slim
```

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

### Créer une nouvelle migration de base de données

Si nécessaire, commencer par remettre la base de données de l'environnement de développement
au niveau de la dernière migration :

```bash
./docker-compose-api up -d
./composer-api doctrine orm:schema-tool:drop --force
./composer-api doctrine migrations:migrate
```

Puis :

```bash
./composer-api doctrine orm:clear-cache:metadata
./composer-api doctrine orm:clear-cache:query
./composer-api doctrine orm:clear-cache:result
for d in $(find api/var/doctrine/cache -mindepth 1 -type d); do rm -rf "$d"; done
./composer-api doctrine migrations:diff
```

Puis après vérification/finalisation de la migration, la tester :

```bash
./composer-api doctrine migrations:migrate
./composer-api doctrine fixtures:load
```
