# Tkdo

Tirages au sort de cadeaux.

## Démarrage (développement)

> Prérequis :
> - docker
> - docker-compose
> - node et npm
> - openssl

```
$ make start
```

Après la construction des images et le démarrage des conteneurs Docker, votre navigateur s'ouvre automatiquement sur [la page d'accueil](https://localhost).

> Note : pour arrêter ensuite proprement les conteneurs, faire :
> - soit un `CTRL+C` depuis le terminal où s'exécute le `make start`
> - soit depuis un autre terminal :
>   ```bash
>   $ make stop
>   ```

## Changements

- [historique des changements](./feature/done/README.md)
- [roadmap](./feature/todo/README.md)

## Documentation

- [utilisation d'un registry npm local](./doc/registry-npm-local.md)
