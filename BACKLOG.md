# Travaux futurs

- ajouter un conteneur angular-build qui :
  - fasse un npm install au démarrage (comme cli fait le composer install)
  - encapsule `npm --prefix front -- run build --prod --watch --delete-output-path false`
  - soit une dépendance du conteneur angular
- mettre en place des tests e2e (lancés depuis un conteneur browser avec un chrome headless)
- monter les versions des dépendances front
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
