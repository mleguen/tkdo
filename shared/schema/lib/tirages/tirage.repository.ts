import { EntityRepository, Repository } from 'typeorm';
import { Tirage } from './tirage.entity';

@EntityRepository(Tirage)
export class TirageRepository extends Repository<Tirage> {

  findTiragesOrganisateur(idOrganisateur: number): Promise<Tirage[]> {
    return this.createQueryBuilder('t')
      .innerJoinAndSelect('t.organisateur', 'u', 'u.id = :idOrganisateur', { idOrganisateur })
      .getMany();
  }
}
