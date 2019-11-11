# language: fr
Fonctionnalité: consultation d'un tirage lancé

  Lorsqu'un participant consulte un tirage lancé auquel il participe, il voit :
  - que le tirage est lancé
  - à quel participant il aura le plaisir d'offrir un cadeau
  - mais pas le reste de l'affectation

  Scénario: Alice consulte un tirage lancé auquel elle participe
  
    Soit une participante Alice, connectée
    Et des participants Bob, Charlie, David et Eve
    Et un tirage "Noël" pour le 25/12 à venir, auquel ils participent tous
    Et ce tirage est lancé : Alice aura le plaisir d'offrir un cadeau à David

    Quand Alice va sur la page "Mes tirages"
    Alors elle voit le tirage "Noël"

    Quand elle clique sur le tirage "Noël"
    Alors elle arrive sur une page intitulée "Noël"
    Et elle voit le 25/12 à venir comme date du tirage
    Et elle voit Bob, Charlie, David et Eve dans la liste des autres participants
    Et elle voit que c'est à David qu'elle aura le plaisir d'offrir un cadeau
    Mais elle ne voit pas à qui les autres participants auront le plaisir d'offrir un cadeau
