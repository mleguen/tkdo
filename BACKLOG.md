# Travaux futurs

- monter la version d'angular (>= 20) pour corriger les vulnérabilités modérées
- faire une PR dans rpkamp/mailhog-client pour corriger deprecation "str_getcsv(): the $escape parameter must be provided as its default value will change"
- se débarrasser de mhsendmail
- passer à mysql 8
- rendre personnalisable la signature des emails, pour faire un peu moins "machine"
- séparer PageOccasionComponent/OccasionComponent/ParticipantComponent sur le modèle de PageIdeesComponent/ListeIdeesComponent/IdeeComponent
- trier les participants par ordre alphabétique
- couper proprement les noms de participants trop longs
- support de AWS en serverless avec ansible, et 2 stacks dev/prod
- remplacer apache-pack par un outil de build digne de ce nom ou le supprimer complètement
- renommer fixtures en install, et renseigner un email admin par défaut
  (l'admin pourra ensuite le modifier par lui-même)
- ajouter sur la carte d'un participant le nombre d'idées qui ont été proposées pour lui (ne compter que les idées lisibles)
- ajouter la possibilité de commenter une idée en cliquant sur sa carte (ne rendre lisible que ses propres commentaires pour ses idées)
- afficher sur la carte d'une idée le nombre de commentaires qui on été faits (ne compter que les commentaires lisibles)
- ajouter la possibilité de rayer une idée quand on la commente (ne rendre visible que ses propres rayures)
- ajouter la possibilité d'éditer le titre d'une idée ou un commentaire (pour son auteur seulement)
- ajouter la possibilité de désactiver un compte utilisateur
  (toujours présent en base pour l'historique, mais plus utilisable)
- ajouter une route admin d'annulation du tirage
- ajouter une route admin de suppression d'un utilisateur d'une occasion
- ajouter une route admin de suppression d'exclusion
- ajouter une route admin de suppression d'occasion (si pas ou plus de tirage)
- enlever "doctrine" des noms de colonne auto-générés en base de données
