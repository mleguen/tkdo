# Travaux futurs

- notifications par mail :
  - ajouter une notification quotidienne des idées créées ou supprimées :
    - ajouter un 3ème choix Q pour Quotidienne aux préférences de notification
      - dans l'api
      - dans le front
    - remplacer la suppression d'une idée par l'ajout d'une date de suppression (PUT du coup au lieu de DELETE) :
      - dans l'api
      - dans le front
    - filtrer les idées supprimées dans le GET (avec une querystring qui fait le filtre, obligatoire sauf pour les admins) :
      - dans l'api
      - dans le front
      - dans la page admin (section Idées à ajouter)
    - ajouter une commande doctrine d'envoi de la notification quotidienne (pour les utilisateurs le souhaitant)
    - documenter l'installation d'un cron pour appeler cette commande
- ajouter une route admin d'affichage des logs
- ajouter une route admin de génération du tirage au sort dans l'application (tirage au sort automatisé)
- ajouter sur la carte d'un participant le nombre d'idées qui ont été proposées pour lui (ne compter que les idées lisibles)
- ajouter la possibilité de commenter une idée en cliquant sur sa carte (ne rendre lisible que ses propres commentaires pour ses idées)
- afficher sur la carte d'une idée le nombre de commentaires qui on été faits (ne compter que les commentaires lisibles)
- ajouter la possibilité de rayer une idée quand on la commente (ne rendre visible que ses propres rayures)
- ajouter la possibilité d'éditer le titre d'une idée ou un commentaire (pour son auteur seulement)
- ajouter la possibilité de désactiver un compte utilisateur
  (toujours présent en base pour l'historique, mais plus utilisable)
- ajouter une route admin d'annulation du tirage
- ajouter une route admin de suppression d'un utilisateur d'une occasion
- ajouter une route admin de suppression d'occasion (si pas ou plus de tirage)
- enlever "doctrine" des noms de colonne auto-générés en base de données
- repartir d'un squelette slim de base pour enlever tout le superflu de l'api
