# language: fr
Fonctionnalité: consultation d'un tirage par un organisateur

  Un organisateur doit pouvoir consulter les tirages auxquels il participe et/ou dont il est l'organisateur.

  Lorsqu'un organisateur consulte un tirage, il voit la même chose qu'un participant, plus :
  - qu'il en est l'organisateur, si c'est le cas
  - qu'il y participe, si c'est le cas
  - mais pas l'affectation (sauf la personne à qui il doit offrir un cadeau s'il y participe)

  Scénario: Bob consulte un tirage dont il est organisateur, et auquel il participe
  
    Soit un organisateur Bob, connecté
    Et des participants Alice, Charlie, David et Eve
    Et un tirage "Noël" pour le 25/12 à venir, que Bob organise et auquel ils participent tous
    Et ce tirage est lancé : Bob aura le plaisir d'offrir un cadeau à Eve

    Quand Bob va sur la page d'accueil
    Alors il voit un lien "Les tirages que j'organise", dans la section "organisateur(trice)" de la barre de côté

    Quand Bob va sur la page "Les tirages que j'organise"
    Alors il voit le tirage "Noël"

    Quand il clique sur le tirage "Noël"
    Alors il arrive sur une page intitulée "Noël"
    Et il voit le 25/12 à venir comme date du tirage
    Et il voit qu'il en est l'organisateur
    Et il voit Alice, Charlie, David, Eve et lui-même dans la liste des participants
    Et elle voit que c'est à Eve qu'il aura le plaisir d'offrir un cadeau
    Mais elle ne voit pas à qui les autres participants auront le plaisir d'offrir un cadeau

  Scénario: Bob consulte un tirage dont il est organisateur, mais auquel il ne participe pas
  
    Soit un organisateur Bob, connecté
    Et des participants Alice, Charlie, David et Eve
    Et un tirage "Réveillon" pour le 31/12 à venir, que Bob organise et auquel tous participent sauf lui

    Quand Bob va sur la page "Les tirages que j'organise"
    Alors il voit le tirage "Réveillon"

    Quand il clique sur le tirage "Réveillon"
    Alors il arrive sur une page intitulée "Réveillon"
    Et il voit le 31/12 à venir comme date du tirage
    Et il voit qu'il en est l'organisateur
    Et il voit Alice, Charlie, David et Eve dans la liste des participants, mais pas lui-même
