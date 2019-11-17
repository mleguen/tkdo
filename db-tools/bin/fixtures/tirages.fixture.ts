import * as moment from "moment";
import { Repository } from "typeorm";

import { Tirage, Participation } from "../../../shared/schema";
import { UtilisateursFixture } from "./utilisateurs.fixture";

export class TiragesFixture {
  noel: Tirage;
  reveillon: Tirage;

  constructor(
    private tiragesRepository: Repository<Tirage>,
    private utilisateurs: UtilisateursFixture
  ) { }

  async sync() {
    await this.tiragesRepository.clear();

    this.noel = await this.createTirage(
      { titre: 'Noël', date: moment('25/12', 'DD/MM').format(), organisateur: this.utilisateurs.bob, statut: 'LANCE' },
      [
        { participant: this.utilisateurs.alice, offreA: this.utilisateurs.david },
        { participant: this.utilisateurs.bob, offreA: this.utilisateurs.eve },
        { participant: this.utilisateurs.charlie, offreA: this.utilisateurs.alice },
        { participant: this.utilisateurs.david, offreA: this.utilisateurs.bob },
        { participant: this.utilisateurs.eve, offreA: this.utilisateurs.charlie }
      ]
    );
    this.reveillon = await this.createTirage(
      { titre: 'Réveillon', date: moment('31/12', 'DD/MM').format(), organisateur: this.utilisateurs.bob, statut: 'CREE' },
      [
        { participant: this.utilisateurs.alice },
        { participant: this.utilisateurs.charlie },
        { participant: this.utilisateurs.david },
        { participant: this.utilisateurs.eve }
      ]
    );
  }

  private async createTirage(
    proprietes: Pick<Tirage, 'titre' | 'date' | 'organisateur' | 'statut'>,
    proprietesParticipations: Pick<Participation, 'participant' | 'offreA'>[]
  ): Promise<Tirage> {
    let tirage = new Tirage(proprietes);
    tirage.participations = proprietesParticipations.map(proprietesParticipation => new Participation(proprietesParticipation));
    tirage = await this.tiragesRepository.save(tirage);
    console.log(`Tirage ${tirage.titre} créé (${tirage.id})`);
    return tirage;
  }
}
