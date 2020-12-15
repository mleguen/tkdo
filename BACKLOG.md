# Travaux futurs

- test/docker-compose.yaml global:
  - remplaçant api/docker-compose.yaml et api/docker/slim
  - réutilisant api/test/docker-compose.yaml
  - incluant un service pour le front
  - documentation pour l'utiliser manuellement
    (mais pourra servir ultérieurement à des tests e2e)
- utiliser $TKDO_DEV_MODE dans fixtures, plutôt que de passer --prod
- notion d'environnement et séparation de .env en .env, .env.$TKDO_ENV et .env.local
  (ATTENTION à docker qui ne supporte que .env)
- valeurs par défaut des varibles d'environnement dans .env, plus dans le code
  (avec contrôle qu'elles sont bien définies par php-dotenv)
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
