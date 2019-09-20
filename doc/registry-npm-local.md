# Utiliser un registry npm local

> Note : l'utilisation d'un registry npm local permet d'accélérer la construction des images Docker nécessitant un `npm install`, en mutualisant le cache entre le host et les différents conteneurs Docker.

> Note : les exemples ci-dessous utilisent verdaccio comme registry npm local (`npm install --global verdaccio`), même un autre registry devrait pouvoir être utilisé d'une manière similaire.

01. démarrer le registry npm local :
    ```bash
    $ verdaccio
    ```
01. définir la variable d'environnement `NPM_CONFIG_REGISTRY`, contenant l'URL du registry npm local, dans un fichier `.env` à la racine du projet :
    ```bash
    NPM_CONFIG_REGISTRY=http://localhost:4873
    ```
