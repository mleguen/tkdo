#!/usr/bin/env bash
rm -r front
ng new --directory front --routing --skip-git --style scss tkdo
cd front
ng generate component connexion
ng generate component liste-idees
ng generate component menu
ng generate component occasion
ng generate component profil
ng generate interceptor auth-backend
ng generate service backend
ng generate guard --implements CanActivate connexion
ng generate interceptor dev-backend
ng generate interceptor erreur-backend
