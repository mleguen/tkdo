import * as moment from "moment";
import { Repository } from "typeorm";

import { Tirage } from "../../../shared/schema";
import { UtilisateursFixture } from "./utilisateurs.fixture";

export class TiragesFixture {
  noel: Tirage;
  reveillon: Tirage;

  constructor(
    private tiragesRepository: Repository<Tirage>,
    private utilisateursFixture: UtilisateursFixture
  ) { }

  async sync() {
    this.noel = await this.findOrCreate({
      titre: 'Noël', date: moment('25/12', 'DD/MM').format(), participants: [
        this.utilisateursFixture.alice,
        this.utilisateursFixture.bob,
        this.utilisateursFixture.charlie,
        this.utilisateursFixture.david,
        this.utilisateursFixture.eve
      ]
    });
    this.reveillon = await this.findOrCreate({
      titre: 'Réveillon', date: moment('31/12', 'DD/MM').format(), participants: [
        this.utilisateursFixture.alice,
        this.utilisateursFixture.bob,
        this.utilisateursFixture.charlie,
        this.utilisateursFixture.david,
        this.utilisateursFixture.eve
      ]
    });
  }

  private async findOrCreate(proprietes: Pick<Tirage, 'titre' | 'date' | 'participants'>): Promise<Tirage> {
    let tirage = await this.tiragesRepository.findOne({ titre: proprietes.titre });
    if (tirage) {
      console.log(`Le tirage ${tirage.titre} existe déjà (${tirage.id})`);
    } else {
      tirage = await this.tiragesRepository.save(new Tirage(proprietes))
      console.log(`Tirage ${tirage.titre} créé (${tirage.id})`);
    }
    return tirage;
  }
}
