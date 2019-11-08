import { ConnectionOptions } from "typeorm";

import { Utilisateur } from "../utilisateurs";
import { Tirage, TirageRepository } from "../tirages";
import { readFileSync } from "fs";

export const connectionOptions = {
  type: process.env.TKDO_CONNECTION_TYPE,
  host: process.env.TKDO_CONNECTION_HOST,
  username: fileToString(process.env.TKDO_CONNECTION_USERNAME_FILE),
  password: fileToString(process.env.TKDO_CONNECTION_PASSWORD_FILE),
  database: fileToString(process.env.TKDO_CONNECTION_DATABASE_FILE),
  synchronize: false,
  entities: [
    Tirage,
    TirageRepository,
    Utilisateur
  ]
} as ConnectionOptions;

function fileToString(path?: string) {
  if (path) {
    return readFileSync(path);
  }
}
