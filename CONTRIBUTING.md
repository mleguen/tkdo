# Guide du développeur

## Front

### Prerequisites

- docker & docker-compose installed
- user member of docker group (to run docker & docker-compose without sudo)

### Install

If not running with user id 1000 & group ID 1000, first set `DEV_UID` & `DEV_GID` variables in .env to your user (eg `id -u`) & group ID (eg `id -g`).

Then:

```
docker-compose build
./npm install
```

### Test

#### Unit tests

```
./npm test
```

#### Components tests

```
./npm run ct
```

#### End-to-end tests

```
./npm run e2e
```

### Upgrade angular

The following upgrade pattern helps ensuring the project keeps useing Angular as standard as possible across versions:

- switch to the `ngskel` branch
- run `./init-ng`, adapting it when necessary to the new angular version
- commit & push on `ngskel`
- merge `ngskel` into `master` and resolve conflicts to adapt the front code to the new angular version's ways
