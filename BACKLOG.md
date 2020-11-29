# Travaux futurs

- notifications par mail :
  - ajouter l'e-mail dans le profil (modifiable mais requis, et au bon format, mais pas d'unicité imposée - 2 participants peuvent avoir le même mail)
  - ajouter l'envoi d'un mail avec les identifiants à la création d'un compte (mdp plus fourni à l'admin à la création)
  - ajouter l'envoi d'un mail avec les nouveaux identifiants à la réinitialisation d'un mot de passe (mdp plus fourni à l'admin à la réinitialisation)
  - ajouter l'envoi d'un mail à l'ajout d'un utilisateur à une occasion pour le prévenir
  - ajouter une notification instantanée des idées créées ou supprimées (paramétrage oui/non dans le profil, non par défaut)
  - ajouter une notification quotidienne des idées créées ou supprimées (paramétrages des notifications rien/instantanées/quotidiennes dans le profil, rien par défaut)
- tirage au sort automatisé :
  - ajouter une route admin de génération du tirage au sort dans l'application
  - ajouter l'envoi d'un mail à la génération du tirage au sort pour prévenir les participants
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
