import { Entity, PrimaryGeneratedColumn, Column, ManyToMany } from 'typeorm';
import { IUtilisateur } from '../../../domaine';
import { Tirage } from '../tirages';

@Entity()
export class Utilisateur implements IUtilisateur {
  
  constructor(utilisateur: Pick<Utilisateur, 'login' | 'nom'>) {
    Object.assign(this, utilisateur);
  }

  @PrimaryGeneratedColumn()
  id: number;

  @Column({ length: 255, unique: true, nullable: false })
  login: string;

  @Column({ length: 255, nullable: false })
  nom: string;

  @ManyToMany(type => Tirage, tirage => tirage.participants)
  tirages: Tirage[];
}
