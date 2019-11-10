import { Entity, PrimaryGeneratedColumn, Column } from 'typeorm';
import { IUtilisateur } from '../../../domaine';

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
}
