# Guide du développeur

## db-tools

### Générer une nouvelle migration

Depuis la racine du projet :

```bash
$ docker-compose run -v $PWD/db-tools/migrations:/home/node/tkdo/db-tools/migrations service-db-tools typeorm migration:generate -n feature-5-consultation-tirages
```

où `feature-5-consultation-tirages` est le nom de la nouvelle migration, qui sera créée dans le répertoires db-tools/migrations.

### Appliquer les migrations

```bash
$ docker-compose run service-db-tools typeorm migration:run
```

### Installer les fixtures

```bash
$ docker-compose run service-db-tools node bin/fixtures
```
