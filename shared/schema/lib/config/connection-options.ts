import { ConnectionOptions } from "typeorm";

import { Utilisateur } from "../utilisateurs";
import { Participation, ParticipationRepository, Tirage } from "../tirages";
import { readFileSync } from "fs";

export const connectionOptions = {
  type: process.env.TKDO_CONNECTION_TYPE,
  host: process.env.TKDO_CONNECTION_HOST,
  username: process.env.TKDO_CONNECTION_USERNAME,
  password: fileToString(process.env.TKDO_CONNECTION_PASSWORD_FILE),
  database: process.env.TKDO_CONNECTION_DATABASE,
  synchronize: false,
  entities: [
    Participation,
    ParticipationRepository,
    Tirage,
    Utilisateur
  ],
  logging: process.env.NODE_ENV !== 'production'
} as ConnectionOptions;

function fileToString(path?: string) {
  if (path) {
    return readFileSync(path);
  }
}
