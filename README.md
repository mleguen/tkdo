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

Dans les explications ci-dessous, le *répertoire d'installation* désigne le répertoire du serveur Apache
où sera installé tkdo.

### Pré-requis

- php 7.3 avec les extensions `dom`, `mbstring`, `pdo_mysql` et `zip`
- Apache avec le module `mod_rewrite`
- l'utilisation de fichiers `.htaccess` dans le répertoire d'installation est autorisée 
  (à défaut, copier le contenu de [.htaccess](./apache/.htaccess) dans une directive `Directory` dans la configuration Apache)
- le répertoire d'installation est le répertoire racine de l'hôte Apache
  (à défaut, ajouter `--base-href /prefixe/du/repertoire/cible` à la fin du script `build` dans [package.json](./package.json))
- l'hôte Apache est accessible en HTTPS
  (à défaut, commenter la règle de redirection vers HTTPS dans [.htaccess](./apache/.htaccess))

### Configuration

Plusieurs variables d'environnement permettent de configurer Tkdo
(voir [le fichier .env](./api/.env) pour la liste de ces variables).

Tout ou partie de ces variables peuvent être redéfinies dans un fichier `api/.env.prod`,
qui sera automatiquement intégré au package d'installation à l'étape suivante,
et/ou directement dans les variables d'environnement du serveur Apache.

### Construction du package d'installation

```bash
./apache-pack
```

### Installation

Décompresser dans le répertoire d'installation l'archive `tkdo-v*.tar.gz` obtenue.

> **Attention** : certains hébergeurs proposent des fonctions d'upload d'archives les décompressant automatiquement,
> mais qui peuvent parfois tronquer les noms de fichiers si l'arborescence est trop profonde (`api/vendor` par exemple).
> Télécharger dans ce cas plutôt l'archive en tant que simple fichier, et la décompresser manuellement.

Puis, depuis le répertoire d'installation :

```bash
$ cd api
$ ./composer.phar doctrine -- orm:generate-proxies

 Processing entity "xxx"
[...]

 Proxy classes generated to "/home/lgnby/www/tkdo/api/var/doctrine/proxy"
$ ./composer.phar doctrine -- migrations:migrate
[...]
  ------------------------
  ++ finished in xxxms
  ++ used xxxM memory
  ++ xxx migrations executed
  ++ xxx sql queries
```

### Scripts

L'envoi des notifications périodiques par mail nécessite la configuration d'une tâche planifiée
pour exécuter `api/bin/notif-quotidienne.php` une fois par jour, à l'heure de votre choix.
Par exemple :

```crontab
0 6 * * * php /var/www/api/bin/notif-quotidienne.php
```

### Création d'un premier compte administrateur

Créer le compte `admin` en spécifiant son e-mail
(à défaut, si l'option `--admin-email` est omise,
l'e-mail utilisé sera `admin@host` où `host` est le nom d'hôte de `TKDO_BASE_URI`) :

```bash
$ ./composer.phar console -- fixtures --admin-email admin@host
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
> et exécuter `bin/doctrine.php` avec le binaire CLI correpondant (`/usr/local/php7.3/bin/php` par exemple).
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

### Utiliser l'environnement de développement complet

Démarrer et initialiser l'environnement :

```bash
sudo docker-compose up -d front
```

Puis ouvrez votre navigateur sur http://localhost:8080

Les logs sont affichés sur la sortie standard des conteneurs et collectés par docker-compose.
Pour les consulter ou fil de l'eau :

```bash
sudo docker-compose logs -f
```

> Attention : chaque démarrage de l'environnement Docker
> réinitialisera la base de données et recréera les fixtures
> (`sudo docker-compose run --rm slim-cli ./install-with-fixtures.sh`)

### Tests de bout en bout

```bash
sudo docker-compose up -d front
sudo docker-compose run --rm npm run test-e2e
```

> Note : ces tests de bout en bout sont les tests d'intégration front,
> mais exécutés sur l'environnement de développement complet,
> avec du coup un jeu de test aligné sur les fixtures de l'API.

### Front

#### Installation des dépendances

```sh
sudo docker-compose run --rm npm install
```

#### Tests unitaires

- tests unitaires :

  ```bash
  sudo docker-compose run --rm npm test
  ```

- tests d'intégration :

  ```bash
  sudo docker-compose run --rm npm run test-int
  ```

  > Note : ces tests d'intégration s'exécutent sur le serveur de développement Angular,
  > avec interception des requêtes destinées à l'API (voir ci-dessous).

#### Utiliser le serveur de développement Angular seul

```bash
sudo docker-compose run --rm -p 4200:4200 npm start
```

Puis ouvrez votre navigateur sur http://localhost:4200/
quand l'invite vous le demande.

Comme le serveur de développement Angular est lancé sans `--prod`,
les requêtes destinées à l'API sont interceptées et bouchonnées
(cf. [front/src/app/dev-backend.interceptor.ts](./front/src/app/dev-backend.interceptor.ts)).

### API

#### Tests

- tous les tests (unitaires et intégration) :

  ```bash
  sudo docker-compose run --rm slim-cli ./run-test.sh
  ```

- ou seulement les tests d'intégration :

  ```bash
  sudo docker-compose run --rm slim-cli ./run-test.sh --filter '/Test\\Int/'
  ```

> Attention : l'exécution des tests d'intégration
> réinitialise la base de données de l'environnement de développement
> (`sudo docker-compose run --rm slim-cli ./install.sh`)

#### Créer une nouvelle migration de base de données

Commencer par réinitialiser l'environnement de développement
et s'assurer que la base de données est au niveau de la dernière migration :

```bash
sudo docker-compose run --rm slim-cli ./install.sh
```

Puis générer automatiquement la nouvelle migration :

```bash
sudo docker-compose run --rm slim-cli -c '
  ./composer.phar doctrine -- orm:clear-cache:metadata &&
  ./composer.phar doctrine -- orm:clear-cache:query &&
  ./composer.phar doctrine -- orm:clear-cache:result &&
  for d in $(find var/doctrine/cache -mindepth 1 -type d); do rm -rf "$d"; done &&
  ./composer.phar doctrine -- migrations:diff'
```

Finalement, après vérification/finalisation de la migration, la tester :

```bash
sudo docker-compose run --rm slim-cli -c '
  ./composer.phar doctrine -- migrations:migrate &&
  ./composer.phar console -- fixtures'
```
