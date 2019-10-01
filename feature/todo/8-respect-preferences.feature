# language: fr
Fonctionnalité: respect des préférences des participants

  Un participant peut consulter et éditer les listes :
  - des particiants auxquels il préfère ne pas offrir
  - des particiants desquels il préfère ne pas recevoir

  Ces préférences sont prises en compte lors du lancement des tirages auxquels il participe,
  en ajoutant au calcul de la pertinence des affections les notes pondérées suivantes :
  - respect des souhaits :
    - d'offrir = 0% si le participant à qui il doit offrir est un des participants auxquels il préfère ne pas offrir, 100% sinon
    - de recevoir = 0% si le participant de qui il doit recevoir est un des participants desquels il préfère ne pas recevoir, 100% sinon
