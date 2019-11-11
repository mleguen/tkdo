# language: fr
Fonctionnalité: consultation des tirages

  Un participant doit pouvoir consulter les tirages auxquels il participe.

  Pour chacun de ces tirages, il voit :
  - le titre et la date de l'événement à l'occasion duquel les cadeaux seront échangés
  - la liste des autres participants

  Scénario: Alice consulte les tirages auxquels elle participe
  
    Soit une participante Alice, connectée
    Et des participants Bob, Charlie, David et Eve
    Et un tirage "Noël" pour le 25/12 à venir auquel ils participent tous
    Et un tirage "Réveillon" pour le 31/12 à venir auquel ils participent tous

    Quand Alice va sur la page d'accueil
    Alors elle voit un lien "Mes tirages", dans la section "participant(e)" de la barre de côté

    Quand elle clique sur le lien "Mes tirages"
    Alors elle arrive sur une page intitulée "Mes tirages"
    Et elle voit le tirage "Réveillon" pour le 31/12 à venir
    Et elle voit le tirage "Noël" pour le 25/12 à venir

    Quand elle clique sur le tirage "Noël"
    Alors elle arrive sur une page intitulée "Noël"
    Et elle voit le 25/12 à venir comme date du tirage
    Et elle voit Bob, Charlie, David et Eve dans la liste des autres participants
