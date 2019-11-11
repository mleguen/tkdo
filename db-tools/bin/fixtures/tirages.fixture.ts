import * as moment from "moment";
import { Repository } from "typeorm";

import { Tirage, Participation } from "../../../shared/schema";
import { UtilisateursFixture } from "./utilisateurs.fixture";

export class TiragesFixture {
  noel: Tirage;
  reveillon: Tirage;

  constructor(
    private tiragesRepository: Repository<Tirage>,
    private utilisateursFixture: UtilisateursFixture
  ) { }

  async sync() {
    await this.tiragesRepository.clear();

    this.noel = await this.createTirage(
      { titre: 'Noël', date: moment('25/12', 'DD/MM').format(), statut: 'LANCE' },
      [
        { participant: this.utilisateursFixture.alice, offreA: this.utilisateursFixture.david },
        { participant: this.utilisateursFixture.bob, offreA: this.utilisateursFixture.eve },
        { participant: this.utilisateursFixture.charlie, offreA: this.utilisateursFixture.alice },
        { participant: this.utilisateursFixture.david, offreA: this.utilisateursFixture.bob },
        { participant: this.utilisateursFixture.eve, offreA: this.utilisateursFixture.charlie }
      ]
    );
    this.reveillon = await this.createTirage(
      { titre: 'Réveillon', date: moment('31/12', 'DD/MM').format(), statut: 'CREE' },
      [
        { participant: this.utilisateursFixture.alice },
        { participant: this.utilisateursFixture.bob },
        { participant: this.utilisateursFixture.charlie },
        { participant: this.utilisateursFixture.david },
        { participant: this.utilisateursFixture.eve }
      ]
    );
  }

  private async createTirage(
    proprietes: Pick<Tirage, 'titre' | 'date' | 'statut'>,
    proprietesParticipations: Pick<Participation, 'participant' | 'offreA'>[]
  ): Promise<Tirage> {
    let tirage = new Tirage(proprietes);
    tirage.participations = proprietesParticipations.map(proprietesParticipation => new Participation(proprietesParticipation));
    tirage = await this.tiragesRepository.save(tirage);
    console.log(`Tirage ${tirage.titre} créé (${tirage.id})`);
    return tirage;
  }
}
