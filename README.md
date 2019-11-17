# Tkdo

Tirages au sort de cadeaux.

## Installation

> Prérequis :
> - docker
> - docker-compose
> - node et npm
> - openssl

```
$ git clone https://github.com/mleguen/tkdo
$ cd tkdo
$ make install
```

## Utilisation

Depuis la racine du projet (répertoire tkdo) :

```
$ make start
```

Après la construction des images et le démarrage des conteneurs Docker, vous pouvez [accéder à l'application](https://localhost).

> Note : pour arrêter ensuite proprement les conteneurs, faire :
> - soit un `CTRL+C` depuis le terminal où s'exécute le `make start`
> - soit depuis un autre terminal :
>   ```bash
>   $ make stop
>   ```

Dans la configuration par défaut, vous pourrez vous connecter avec les identifiants :

- alice:alice (participante)
- ou bob:bob (organisateur)

## Documentations

- [historique des changements](./CHANGELOG.md)
- [roadmap](./ROADMAP.md)
- [guide du développeur](./CONTRIBUTING.md)
- autre documents :
  - [utilisation d'un registry npm local](./doc/registry-npm-local.md)
