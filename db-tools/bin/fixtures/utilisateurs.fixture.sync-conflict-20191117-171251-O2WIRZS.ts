import { Repository } from "typeorm";
import { Utilisateur } from "../../../shared/schema";

export class UtilisateursFixture {
  alice: Utilisateur;
  bob: Utilisateur;
  charlie: Utilisateur;
  david: Utilisateur;
  eve: Utilisateur;

  constructor(
    private utilisateursRepository: Repository<Utilisateur>
  ) { }

  async sync() {
    // TODO: trouver un moyen de cascader le clear pour ne pas avoir une
    // ER_TRUNCATE_ILLEGAL_FK: Cannot truncate a table referenced in a foreign key constraint
    await this.utilisateursRepository.clear();

    this.alice = await this.createUtilisateur({ login: 'alice@domaine.tld', nom: 'Alice' });
    this.bob = await this.createUtilisateur({ login: 'bob@domaine.tld', nom: 'Bob' });
    this.charlie = await this.createUtilisateur({ login: 'charlie@domaine.tld', nom: 'Charlie' });
    this.david = await this.createUtilisateur({ login: 'david@domaine.tld', nom: 'David' });
    this.eve = await this.createUtilisateur({ login: 'eve@domaine.tld', nom: 'Eve' });
  }

  private async createUtilisateur(proprietes: Pick<Utilisateur, 'login' | 'nom'>): Promise<Utilisateur> {
    let utilisateur = await this.utilisateursRepository.save(new Utilisateur(proprietes))
    console.log(`Utilisateur ${utilisateur.login} créé (${utilisateur.id})`);
    return utilisateur;
  }
}
