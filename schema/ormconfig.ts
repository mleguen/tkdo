import { config } from 'dotenv';
config();

import { Utilisateur } from './lib/utilisateurs';

module.exports = Object.assign(
  JSON.parse(process.env.TKDO_DATABASE),
  {
    entities: [
      Utilisateur
    ],
    migrations: [
      './migrations/*.ts'
    ],
    cli: {
      migrationsDir: "migrations"
    }
  }
);
