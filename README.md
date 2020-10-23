# Tkdo

Tirage au sort de cadeaux, en famille ou entre amis.

## Déploiement sur serveur Apache

### Installation

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

### Exploitation

#### Création de comptes utilisateurs

Les comptes utilisateurs doivent pour l'instant être créés directement en base de données,
dans la table `tkdo_utilisateur`. Par exemple :

```sql
INSERT INTO tkdo_utilisateur (identifiant, nom, mdp)
VALUES ('alice@tkdo.org', 'Alice', 'mdpalice');
```

#### Création d'une occasion de s'offrir des cadeaux

Les occasions doivent pour l'instant être créées directement en base de données,
dans la table `tkdo_occasion`. Par exemple :

```sql
INSERT INTO tkdo_occasion (titre)
VALUES ('Noël 2020');
```

Une première occasion doit impérativement être créée pour que tkdo fonctionne.

Mais si plusieurs occasions existent, seule la dernière occasion créée
(celle dont l'id est le plus élevé) sera prise en compte par tkdo.

#### Ajout de participants à une occasion

Un compte utilisateur doit avoir préalablement été créé pour chaque participant (voir plus haut).
Ces comptes utilisateurs doivent ensuite être déclarés comme participant à la dernière occasion créée,
là encore pour l'instant directement en base de données, dans la table `tkdo_participation`.
Par exemple :

```sql
INSERT INTO tkdo_participation (doctrineoccasion_id, doctrineutilisateur_id)
SELECT o.lastid, u.id
FROM (SELECT MAX(id) lastid FROM tkdo_occasion) o
INNER JOIN tkdo_utilisateur u
WHERE u.identifiant IN ('alice@tkdo.org', 'bob@tkdo.org')
```

#### Tirage au sort

Le tirage au sort pour la dernière occasion créée doit pour l'instant être fait en dehors de l'application,
et injecté ensuite directement en base de données, dans la table `tkdo_resultat_tirage`,
participant par participant.
Par exemple :

```sql
INSERT INTO tkdo_resultat_tirage (occasion_id, quiOffre_id, quiRecoit_id)
SELECT o.lastid, offre.id, recoit.id
FROM (SELECT MAX(id) lastid FROM tkdo_occasion) o
INNER JOIN tkdo_utilisateur offre ON offre.identifiant = 'alice@tkdo.org'
INNER JOIN tkdo_utilisateur recoit ON recoit.identifiant = 'bob@tkdo.org'
```

Chaque participant à l'occasion doit offrir et recevoir une fois et une seule.

## Développement

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
