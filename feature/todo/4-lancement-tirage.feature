# language: fr
Fonctionnalité: lancement d'un tirage

  Un organisateur doit pouvoir lancer un tirage à l'état "non lancé" dont il est l'organisateur :
  - à chaque participant du tirage est alors affecté un autre participant du tirage, a qui il aura le plaisir d'offrir un cadeau
  - le tirage passe à l'état "lancé"

  L'affectation doit être la plus pertinente possible, tout en ne nécessitant pas plus de quelques secondes de calcul.
  
  La pertinence d'une affectation pour chaque participant est notée de 0 à 100%, et calculée comme une moyenne pondérée des notes :
  - d'absence de répétition :
    - du participant à qui offrir =
      - 100% si c'est sa 1ère participation
      - sinon, pour chaque tirage i auquel il a déjà participé, de 1 (dernier tirage) à n (tout 1er tirage auquel il a participé)
        - note brute (i) = 0% s'il a déjà offert au même participant à ce tirage, 100% sinon
        - note (i) = moyenne pondérée de note brute (i) et de note (i+1), avec toujours la même pondération
    - du participant de qui recevoir = même méthode de calcul

  La pertinence globale de l'affectation est la moyenne des pertinences pour chaque participant.

  Lorsque l'organisateur d'un tirage lancé le consulte, il doit voir la note de pertinence de l'affectation.
