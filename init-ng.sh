#!/usr/bin/env bash
rm -rf front
ng new --directory front --routing --skip-git --style scss tkdo
cd front
ng generate component connexion
ng generate component deconnexion
ng generate component header
ng generate component liste-idees
ng generate component occasion
ng generate component profil
ng generate interceptor auth-backend
ng generate service backend
ng generate guard --implements CanActivate connexion
ng generate interceptor dev-backend
ng generate interceptor erreur-backend
