# Historique des versions

## Prochaine version

- accès à toutes les occasions auxquelles l'utilisateur participe
  ou a participé (mais seulement celles-là)
- possibilité d'appeler l'API directement en ligne de commande avec curl
  (avec `-u $token:` pour fournir le token d'authentification
  et `-d cle=valeur` pour passer un à un les paramètres)
- droits d'administration permettant :
  - côté API :
    - un accès étendu à certaines routes standard :
      - GET /api/utilisateur/:idUtilisateur pour n'importe quel id utilisateur
      - GET /api/occasion sans avoir à préciser d'idParticipant
    - un accès à de nouvelles routes réservées aux administrateurs
      (et non accessibles par le front) :
      - GET et POST /api/utilisateur
      - POST /api/utilisateur/:idUtilisateur/reinitmdp
      - POST et PUT /api/occasion
      - POST /api/occasion/:idOccasion/participation
  - côté front : l'accès à une page d'administration
    détaillant l'utilisation de l'API en ligne de commande

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
