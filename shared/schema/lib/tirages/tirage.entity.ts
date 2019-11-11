import { Entity, PrimaryGeneratedColumn, Column, ManyToMany, JoinTable, OneToMany } from 'typeorm';
import { ITirage, StatutTirage } from '../../../domaine';
import { Utilisateur } from '../utilisateurs/utilisateur.entity';
import { Participation } from './participation.entity';

@Entity()
export class Tirage implements ITirage {

  constructor(tirage: Pick<Tirage, 'titre' | 'date'>) {
    Object.assign(this, tirage);
  }

  @PrimaryGeneratedColumn()
  id: number;

  @Column({ length: 255, unique: true })
  titre: string;

  @Column({ length: 255, nullable: false })
  date: string;

  @OneToMany(type => Participation, participation => participation.tirage, { cascade: true })
  participations: Participation[];

  @Column({ length: 16, nullable: false, default: StatutTirage.CREE })
  statut: string;
}
