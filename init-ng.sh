#!/usr/bin/env bash
rm -r\
  e2e\
  src\
  .editorconfig\
  .gitignore\
  angular.json\
  browserslist\
  karma.conf.js\
  package-lock.json\
  package.json\
  README.md\
  tsconfig.app.json\
  tsconfig.json\
  tsconfig.spec.json\
  tslint.json
ng new --directory . --routing --skip-git --style scss tkdo
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
