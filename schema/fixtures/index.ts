import { createConnection, ConnectionOptions, getRepository } from "typeorm";
import * as ormconfig from '../ormconfig';

import { Utilisateur } from "../lib/utilisateurs";
import { Tirage } from "../lib/tirages";
import { UtilisateursFixture } from "./utilisateurs.fixture";
import { TiragesFixture } from "./tirages.fixture";

async function main() {
  await createConnection(ormconfig as ConnectionOptions);

  const utilisateurs = new UtilisateursFixture(getRepository(Utilisateur));
  const tirages = new TiragesFixture(getRepository(Tirage), utilisateurs);

  await utilisateurs.sync();
  await tirages.sync();
}

main();
