import { config } from 'dotenv';
config();
import { createConnection, getRepository } from "typeorm";

import { connectionOptions, Tirage, Utilisateur } from '../../../schema';
import { TiragesFixture } from "./tirages.fixture";
import { UtilisateursFixture } from "./utilisateurs.fixture";

async function main() {
  await createConnection(connectionOptions);

  const utilisateurs = new UtilisateursFixture(getRepository(Utilisateur));
  const tirages = new TiragesFixture(getRepository(Tirage), utilisateurs);

  await utilisateurs.sync();
  await tirages.sync();
}

main();
