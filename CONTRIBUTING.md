# Guide du développeur

## Prérequis globaux

- docker et docker-compose installés
- utilisateur membre du groupe `docker` (pour exécuter docker et docker-compose sans `sudo`)
- id d'utilisateur (`id -u`) et de groupe (`id -g`) défini dans le `.env` à la racine du projet, si différents de 1000
  (pour s'assurer que les différents conteneurs s'exécutent en tant que l'utilisateur pour avoir les mêmes droits)

## Front

### Outils

Les différents outils front (cypress, npm, ng) s'exécutent dans le conteneur docker `npm`, de manière à ne pas nécessiter l'installation de dépendances sur le poste du développeur, et maîtriser les versions utilisées.

Des scripts sont disponibles à la racine du projet pour simplifier leur appel via docker : `./cypress`, `./ng`, `./npm`.
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

- tests de composants :

  ```bash
  ./npm run ct
  ```

- tests d'intégration :

  ```bash
  ./npm run e2e
  ```

  > Notes :
  > - ces tests d'intégration s'exécutent sur le serveur de développement Angular
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
