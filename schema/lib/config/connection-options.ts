import { ConnectionOptions } from "typeorm";

import { Utilisateur } from "../utilisateurs";
import { Tirage, TirageRepository } from "../tirages";

export const connectionOptions = {
  type: process.env.TKDO_TYPEORM_CONNECTION,
  host: process.env.TKDO_TYPEORM_HOST,
  username: process.env.TKDO_TYPEORM_USERNAME,
  password: process.env.TKDO_TYPEORM_PASSWORD,
  database: process.env.TKDO_TYPEORM_DATABASE,
  synchronize: false,
  entities: [
    Tirage,
    TirageRepository,
    Utilisateur
  ]
} as ConnectionOptions;
