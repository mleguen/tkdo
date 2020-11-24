#!/usr/bin/env bash
rm -rf front
npm install
./node_modules/.bin/ng new --directory front --routing --skip-git --style scss tkdo-front
cd front
./node_modules/.bin/ng generate component admin
./node_modules/.bin/ng generate component connexion
./node_modules/.bin/ng generate component deconnexion
./node_modules/.bin/ng generate component header
./node_modules/.bin/ng generate component liste-idees
./node_modules/.bin/ng generate component liste-occasions
./node_modules/.bin/ng generate component occasion
./node_modules/.bin/ng generate component profil
./node_modules/.bin/ng generate interceptor auth-backend
./node_modules/.bin/ng generate service backend
./node_modules/.bin/ng generate guard --implements CanActivate admin
./node_modules/.bin/ng generate guard --implements CanActivate connexion
./node_modules/.bin/ng generate interceptor dev-backend
./node_modules/.bin/ng generate interceptor erreur-backend
