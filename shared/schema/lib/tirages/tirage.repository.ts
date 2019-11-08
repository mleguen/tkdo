import { EntityRepository, Repository, SelectQueryBuilder } from 'typeorm';
import { Tirage } from './tirage.entity';

@EntityRepository(Tirage)
export class TirageRepository extends Repository<Tirage> {

  findTirageParticipant(idUtilisateur: number, idTirage: number): Promise<Tirage> {
    return this.selectTiragesUtilisateur(idUtilisateur)
      .where('tirage.id = :idTirage', { idTirage })
      .getOne();
  }

  findTiragesParticipant(idUtilisateur: number): Promise<Tirage[]> {
    return this.selectTiragesUtilisateur(idUtilisateur)
      .getMany();
  }

  private selectTiragesUtilisateur(idUtilisateur: number): SelectQueryBuilder<Tirage> {
    return this.createQueryBuilder('tirage')
      .innerJoinAndSelect('tirage.participants', 'utilisateur')
      .where('utilisateur.id = :idUtilisateur', { idUtilisateur });
  }
}
