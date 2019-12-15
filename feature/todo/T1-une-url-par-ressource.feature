# language: fr
Fonctionnalité: (technique) une seule URL par ressource

  Aussi bien côté back que côté front, une même ressource doit avoir une URL unique,
  et pas une URL par représentation de cette ressource.

  Par exemple :
  - /utilisateurs/1/tirages/1
  - et /utilisateurs/2/tirages/1
  sont 2 URL pour 2 représentations de la même ressource (le tirage d'id 1)
  dont l'URL unique pourrait être /tirages/1.

  Or le contexte apporté par les headers (authentification) est suffisant pour déterminer
  quelle représentation de la ressource fournir.

  Problématique similaire avec :
  - /utilisateurs/1/tirages
  - et /utilisateurs/2/tirages
  qui font appel à 2 représentations de la liste des tirages.

  On pourrait argumenter ici que chaque liste est une ressource indépendante,
  mais alors il ne faudrait pas qu'un même tirage puisse appartenir aux 2 listes
  (ce qui est bien le cas ici).
