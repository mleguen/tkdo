import { config } from 'dotenv';
config();

import { Utilisateur } from './lib/utilisateurs';
import { Tirage } from './lib/tirages';

module.exports = Object.assign(
  JSON.parse(process.env.TKDO_DATABASE),
  {
    entities: [
      Utilisateur,
      Tirage
    ],
    migrations: [
      './migrations/*.ts'
    ],
    cli: {
      migrationsDir: "migrations"
    }
  }
);
