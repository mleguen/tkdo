# Tkdo

Tirages cadeaux.

## Installation

### Prérequis à l'installation

- avoir installé docker et docker-compose
- disposer des droits pour exécuter la commande `docker-compose`

### Installation des dépendances

```sh
npm install
```

### Construction des services et des images docker

```sh
npm run build
```

## Démarrage

### Prérequis au démarrage

- avoir placé la clé (resp. le certificat) SSL du serveur du front dans un fichier `front.key` (resp. `front.crt`) à la racine du projet

Pour des besoins de test, il est possible de générer la clé et le certificat SSL (self-signed) avec la commande suivante :

```sh
openssl req -new -x509 -nodes -out front.crt -keyout front.key
```

### Démarrage des images docker

```sh
npm start
```

Accéder ensuite à tkdo depuis un navigateur : <https://localhost>
