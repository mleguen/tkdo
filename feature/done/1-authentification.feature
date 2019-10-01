# language: fr
Fonctionnalité: authentification

  Les utilisateurs doivent s'authentifier pour pouvoir accéder à l'application.

  Scénario: Alice doit s'authentifier pour accéder à l'accueil
  
    Soit une participante Alice

    Quand elle demande à accéder à la page d'accueil
    Alors elle est redirigée vers une page lui demandant ses identifiants

    Quand elle saisit ses identifiants
    Alors elle est redirigée vers la page d'accueil
    Et elle voit son nom sur la page
    Et elle voit qu'elle est participant(e)
