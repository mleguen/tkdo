# Guide du développeur

## db-tools

### Générer une nouvelle migration

Depuis la racine du projet :

```bash
$ docker-compose run -v $PWD/db-tools/migrations:/usr/local/tkdo/db-tools/migrations db-tools typeorm migration:generate -n feature-5-consultation-tirages
```

où `feature-5-consultation-tirages` est le nom de la nouvelle migration, qui sera créée dans le répertoires db-tools/migrations.

### Appliquer les migrations

```bash
$ docker-compose run db-tools typeorm migration:run
```
