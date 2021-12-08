# Historique des changements

## Prochaine version

- correctifs :
  - apache-pack :
    - n'utlise plus la version locale de php pour installer les dépendances
    - construit le front en mode prod
  - api :
    - chargement du fichier .env
    - valeur par défaut pour TKDO_MAILER_FROM
    - typo dans les mails de notifications (#12)
  - front :
    - redirection vers la page de connexion quand la session est expirée (#17)

## V1.3.0 (02/12/2021)

- refactorisation du code de l'API
- ajout de tests de bout en bout
- route admin de génération automatique du tirage au sort

## V1.2.0 (21/12/2020)

- ajout de l'email au profil utilisateur, pour le notifier :
  - à la création de son compte
  - à la réinitialisation de son mot de passe
  - à sa participation à une nouvelle occasion
  - à l'ajout d'un résultat de tirage le concernant
- ajout d'une date aux occations.
  L'occasion affichée par défaut à la connexion est maintenant la prochaine occasion à venir,
  ou à défaut la dernière occasion passée
- les idées ne sont plus supprimées, mais marquées comme telles (et toujours présentes en base de données)
- ajout de préférences de notification des créations/suppressions d'idées au profil utilisateur, pour le notifier :
  - à chaque création/suppression d'idée pour un participant d'une occasion à venir
  - une fois par jour (par un email récapitulatif)
  - ou pas du tout

## V1.1.0 (29/11/2020)

- un menu "Mes occasions" permet d'accéder à toutes les occasions
  auxquelles l'utilisateur participe ou a participé (et seulement celles-là).
  La dernière occasion créée à laquelle il participe est la page par défaut quand il se connecte
- un menu "Mes idées" permet d'accéder directement à la liste d'idées de l'utilisateur connecté.
  C'est la page par défaut quand il se connecte s'il ne participe encore à aucune occasion
- possibilité d'appeler l'API directement en ligne de commande avec curl
  (avec `-u $token:` pour fournir le token d'authentification
  et `-d cle=valeur` pour passer un à un les paramètres)
- droits d'administration permettant :
  - côté API :
    - un accès étendu à certaines routes standard :
      - GET /api/utilisateur/:idUtilisateur pour n'importe quel id utilisateur
      - GET /api/occasion pour toutes les occasions,
        ou les occasions de n'importe quel utilisateur
      - GET /api/occasion/:idOccasion pour n'importe quelle occasion
    - un accès à de nouvelles routes réservées aux administrateurs
      (et accessibles uniquement en ligne de commande) :
      - GET et POST /api/utilisateur
      - POST /api/utilisateur/:idUtilisateur/reinitmdp
      - POST et PUT /api/occasion
      - POST /api/occasion/:idOccasion/participant
      - POST /api/occasion/:idOccasion/resultat
  - côté front : l'accès à une page d'administration
    détaillant l'utilisation de ces routes en ligne de commande

## V1.0.0 (01/11/2020)

Produit viable minimum :

- connexion/déconnexion
- consultation/modification du profil de l'utilisateur connecté
- consultation de la dernière occasion à laquelle l'utilisateur connecté participe :
  - liste des autres participants
  - résultat du tirage en ce qui le concerne (à qui il doit faire un cadeau)
- consultation/ajout/suppression des idées de cadeau pour chacun des participants
  (l'utilisateur connecté ne pouvant pas voir les idées que d'autres auraient proposées pour lui)
- suppression des idées de cadeau que l'utilisateur connecté a lui-même proposées
