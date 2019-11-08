import { config } from 'dotenv';
config();
import { createConnection, getRepository } from "typeorm";

import { connectionOptions, Tirage, Utilisateur } from '../../../shared/schema';
import { TiragesFixture } from "./tirages.fixture";
import { UtilisateursFixture } from "./utilisateurs.fixture";

async function main() {
  try {
    await createConnection(connectionOptions);
  
    const utilisateurs = new UtilisateursFixture(getRepository(Utilisateur));
    const tirages = new TiragesFixture(getRepository(Tirage), utilisateurs);
  
    await utilisateurs.sync();
    await tirages.sync();
    
    process.exitCode = 0;
  }
  catch(err) {
    console.error(err);
    process.exitCode = 1;
  }
}

main();
