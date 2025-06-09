# Guide du développeur

## Prérequis

- docker et son plugin compose installés
- utilisateur membre du groupe `docker` (pour exécuter `docker` et `docker compose` sans `sudo`)
- id d'utilisateur (`id -u`) et de groupe (`id -g`) défini dans le `.env` à la racine du projet, si différents de 1000
  (pour s'assurer que les différents conteneurs s'exécutent en tant que l'utilisateur pour avoir les mêmes droits)

## Utiliser l'environnement de développement complet

Démarrer et initialiser l'environnement :

```bash
docker compose up -d front
./npm install
./npm run build -- --configuration production
```

Puis ouvrez votre navigateur sur http://localhost:8080

Les logs sont affichés sur la sortie standard des conteneurs et collectés par docker compose.
Pour les consulter ou fil de l'eau :

```bash
docker compose logs -f
```

## Tests de bout en bout

```bash
docker compose up -d front
./npm install
./npm run e2e
```

> Note : ces tests de bout en bout sont les tests d'intégration front,
> mais exécutés sur l'environnement de développement complet,
> avec du coup un jeu de test aligné sur les fixtures de l'API.
> Ce jeu de test doit être raffraichi entre 2 exécutions des tests :
> 
> ```bash
> ./composer run install-fixtures
> ```

## Front

### Outils

Les différents outils front (cypress, ng, npm, npx) s'exécutent dans le conteneur docker `npm`, de manière à ne pas nécessiter l'installation de dépendances sur le poste du développeur, et maîtriser les versions utilisées.

Des scripts sont disponibles à la racine du projet pour simplifier leur appel via docker : `./cypress`, `./ng`, `./npm`, `./npx`.
Ces scripts s'exécutent directement dans le contexte du répertoire `front`.

### Installation des dépendances

```bash
./npm install
```

### Tests

- tests unitaires :

  ```bash
  ./npm test
  ```

  > *A faire* : les tests unitaires sont minimalistes (instanciation des singleton).
  > Une partie des tests faits en int/e2e devrait être repassée en tests unitaires

- tests de composants :

  ```bash
  ./npm run ct
  ```

  ou pour n'exécuter que certains fichiers de tests :

  ```bash
  ./npm run cy -- --spec '**/liste-idees.component.cy.ts'
  ```

  > *A faire* : les tests de composants sont pour la plupart minimalistes (montage du composant uniquement).
  > Une partie des tests faits en int/e2e devrait être repassée en tests de composants

- tests d'intégration :

  ```bash
  ./npm run int
  ```

  ou pour n'exécuter que certains fichiers de tests :

  ```bash
  ./npm run int -- --spec '**/liste-idees.cy.ts'
  ```

  (pour n'exécuter qu'une partie des tests au sein d'un fichier,
  remplacer `it(...)` par `it.only()` dans le fichier pour n'exécuter que certains tests,
  ou `it.skip()` pour au contraire passer certains tests)

  > Notes :
  > - ces tests d'intégration s'exécutent sur le serveur de développement Angular,
  >   avec interception des requêtes destinées à l'API (voir ci-dessous)

### Utiliser le serveur de développement Angular seul

```bash
./npm start
```

Puis ouvrez votre navigateur sur http://localhost:4200/
quand l'invite vous le demande.

Comme le serveur de développement Angular est lancé sans `--prod`,
les requêtes destinées à l'API sont interceptées et bouchonnées
(cf. [front/src/app/dev-backend.interceptor.ts](./front/src/app/dev-backend.interceptor.ts)).

### Montées de version de node, angular et des autres dépendances

Procédure à suivre pour que le projet continue d'utiliser angular de la manière la plus standard possible de version en version :

- passer sur la branche `ngskel`
- monter la version de l'image `node` sur laquelle est basée le conteneur `npm` à la dernière version stable
- reconstruire le conteneur `npm`
- lancer `./init-ng`, en l'adaptant si nécessaire aux nouveautés de node et d'angular
- commit & push sur `ngskel`
- cherry-pick `ngskel` dans `master` et :
  - supprimer le répertoire `node_modules` et le `package.lock`
  - repartir du `package.json` de la branche `ngskel`
  - réinstaller les autres dépendances avec `./npm install` (pour qu'elles soient dans la dernière version disponible avec cette version de node)
  - résoudre les conflits pour adapter le code du front aux nouveautés de node, d'angular et des autres dépendances

## API

### Outils

`composer` et les autres outils back (doctrine, console, etc.) s'exécutent dans le conteneur docker `composer`, de manière à ne pas nécessiter l'installation de dépendances sur le poste du développeur, et maîtriser les versions utilisées.

Des scripts sont disponibles à la racine du projet pour simplifier leur appel via docker : `./composer`, `./doctrine`, `./console`.
Ces scripts, bien que placés à la racine du projet pour être plus facilement accessibles, s'exécutent directement dans le contexte du répertoire `api-v1`.

### Tests

- tous les tests (unitaires et intégration) :

  ```bash
  ./composer test
  ```

- ou seulement les tests d'intégration :

  ```bash
  ./composer test -- --filter '/Test\\Int/'
  ```

> Attention : entre 2 exécution des tests d'intégration,
> réinitialiser la base de données de l'environnement de développement
> (`./composer run reset-doctrine`)

### Créer une nouvelle migration de base de données

Commencer par réinitialiser l'environnement de développement
et s'assurer que la base de données est au niveau de la dernière migration :

```bash
./composer run reset-doctrine
```

Puis générer automatiquement la nouvelle migration :

```bash
./doctrine orm:clear-cache:metadata
./doctrine orm:clear-cache:query
./doctrine orm:clear-cache:result
for d in $(find api-v1/var/doctrine/cache -mindepth 1 -type d); do rm -rf "$d"; done
./doctrine migrations:diff
```

Finalement, après vérification/finalisation de la migration, la tester :

```bash
./doctrine migrations:migrate
./console fixtures
```
