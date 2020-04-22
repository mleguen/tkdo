# Tkdo

Tirage au sort de cadeaux, en famille ou entre amis.

## Installation sur serveur Apache

Dans les explications ci-dessous, le *répertoire cible* désigne le répertoire du serveur Apache
où sera installé tkdo.

Pré-requis :
- le module `mod_rewrite` est installé sur votre serveur Apache
- l'utilisation de fichiers `.htaccess` dans le répertoire cible est autorisée 
  (à défaut, copier le contenu de [.htaccess](./.htaccess) dans une directive `Directory` dans la configuration Apache)
- le répertoire cible correspond à la racine de votre hôte Apache
  (à défaut, ajouter `--base-href /prefixe/du/repertoire/cible` à la fin du script `build` dans [package.json](./package.json))
- votre hôte Apache est accessible en HTTPS
  (à défaut, commenter la règle de redirection vers HTTPS dans [.htaccess](./.htaccess))

Construire et packager :

```bash
npm run apache-pack
```

Copier et décompresser ensuite l'archive `tkdo.tar.gz` générée dans le répertoire cible.

## Autre documentation

- [guide du développeur](./CONTRIBUTING.md)
