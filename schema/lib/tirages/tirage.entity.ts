import { Entity, PrimaryGeneratedColumn, Column, ManyToMany, JoinTable } from 'typeorm';
import { ITirage } from '../../../domaine';
import { Utilisateur } from '../utilisateurs/utilisateur.entity';

@Entity()
export class Tirage implements ITirage {

  constructor(tirage: Pick<Tirage, 'titre' | 'date' | 'participants'>) {
    Object.assign(this, tirage);
  }

  @PrimaryGeneratedColumn()
  id: number;

  @Column({ length: 255, unique: true })
  titre: string;

  @Column({ length: 255, nullable: false })
  date: string;

  @ManyToMany(type => Utilisateur, utilisateur => utilisateur.tirages)
  @JoinTable()
  participants: Utilisateur[];
}
