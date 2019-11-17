import { EntityRepository, Repository } from 'typeorm';
import { Participation } from './participation.entity';
import { Tirage } from './tirage.entity';

@EntityRepository(Participation)
export class ParticipationRepository extends Repository<Participation> {

  async findTiragesParticipant(idParticipant: number): Promise<Tirage[]> {
    let participations = await this.createQueryBuilder('p')
      .innerJoin('p.participant', 'u', 'u.id = :idParticipant', { idParticipant })
      .innerJoinAndSelect('p.tirage', 't')
      .getMany();
    return participations.map(p => p.tirage);
  }
}
