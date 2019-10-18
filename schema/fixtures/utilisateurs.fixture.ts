import { Repository } from "typeorm";
import { Utilisateur } from "../lib/utilisateurs";

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
    this.alice = await this.findOrCreate({ login: 'alice@domaine.tld', nom: 'Alice' });
    this.bob = await this.findOrCreate({ login: 'bob@domaine.tld', nom: 'Bob' });
    this.charlie = await this.findOrCreate({ login: 'charlie@domaine.tld', nom: 'Charlie' });
    this.david = await this.findOrCreate({ login: 'david@domaine.tld', nom: 'David' });
    this.eve = await this.findOrCreate({ login: 'eve@domaine.tld', nom: 'Eve' });
  }

  private async findOrCreate(proprietes: Pick<Utilisateur, 'login' | 'nom'>): Promise<Utilisateur> {
    let utilisateur = await this.utilisateursRepository.findOne({ login: proprietes.login });
    if (utilisateur) {
      console.log(`L'utilisateur ${utilisateur.login} existe déjà (${utilisateur.id})`);
    } else {
      utilisateur = await this.utilisateursRepository.save(new Utilisateur(proprietes))
      console.log(`Utilisateur ${utilisateur.login} créé (${utilisateur.id})`);
    }
    return utilisateur;
  }
}
