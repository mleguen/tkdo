# Guide du développeur

## Utiliser l'environnement de développement complet

Démarrer et initialiser l'environnement :

```bash
docker-compose up -d front
```

Puis ouvrez votre navigateur sur http://localhost:8080

Les logs sont affichés sur la sortie standard des conteneurs et collectés par docker-compose.
Pour les consulter ou fil de l'eau :

```bash
docker-compose logs -f
```

> Attention : chaque démarrage de l'environnement Docker
> réinitialisera la base de données et recréera les fixtures
> (`docker-compose run --rm slim-cli ./install-with-fixtures.sh`)

## Tests de bout en bout

```bash
docker-compose up -d front
docker-compose run --rm npm run test-e2e
```

> Note : ces tests de bout en bout sont les tests d'intégration front,
> mais exécutés sur l'environnement de développement complet,
> avec du coup un jeu de test aligné sur les fixtures de l'API.
> Ce jeu de test doit être raffraichi entre 2 exécutions des tests :
> 
> ```bash
> docker-compose up -d slim-cli
> ```

## Front

### Installation des dépendances

```sh
docker-compose run --rm npm install
```

### Tests unitaires

- tests unitaires :

  ```bash
  docker-compose run --rm npm test
  ```

- tests d'intégration :

  ```bash
  docker-compose run --rm npm run test-int
  ```

  > Notes :
  > - ces tests d'intégration s'exécutent sur le serveur de développement Angular,
  >   avec interception des requêtes destinées à l'API (voir ci-dessous)
  > - si les tests d'intégration échouent sur une erreur _"This version of ChromeDriver only supports Chrome version XX"_,
  >   forcer la reconstruction du conteneur npm pour qu'il récupère une version de Chrome plus récente :
  >   
  >     ```bash
  >     docker-compose build --no-cache npm
  >     ```

### Utiliser le serveur de développement Angular seul

```bash
docker-compose run --rm -p 4200:4200 npm start
```

Puis ouvrez votre navigateur sur http://localhost:4200/
quand l'invite vous le demande.

Comme le serveur de développement Angular est lancé sans `--prod`,
les requêtes destinées à l'API sont interceptées et bouchonnées
(cf. [front/src/app/dev-backend.interceptor.ts](./front/src/app/dev-backend.interceptor.ts)).

## API

### Tests

- tous les tests (unitaires et intégration) :

  ```bash
  docker-compose run --rm slim-cli ./run-test.sh
  ```

- ou seulement les tests d'intégration :

  ```bash
  docker-compose run --rm slim-cli ./run-test.sh --filter '/Test\\Int/'
  ```

> Attention : l'exécution des tests d'intégration
> réinitialise la base de données de l'environnement de développement
> (`docker-compose run --rm slim-cli ./install.sh`)

### Créer une nouvelle migration de base de données

Commencer par réinitialiser l'environnement de développement
et s'assurer que la base de données est au niveau de la dernière migration :

```bash
docker-compose run --rm slim-cli ./install.sh
```

Puis générer automatiquement la nouvelle migration :

```bash
docker-compose run --rm slim-cli -c '
  ./composer.phar doctrine -- orm:clear-cache:metadata &&
  ./composer.phar doctrine -- orm:clear-cache:query &&
  ./composer.phar doctrine -- orm:clear-cache:result &&
  for d in $(find var/doctrine/cache -mindepth 1 -type d); do rm -rf "$d"; done &&
  ./composer.phar doctrine -- migrations:diff'
```

Finalement, après vérification/finalisation de la migration, la tester :

```bash
docker-compose run --rm slim-cli -c '
  ./composer.phar doctrine -- migrations:migrate &&
  ./composer.phar console -- fixtures'
```
