import { Entity, PrimaryGeneratedColumn, Column, ManyToOne, PrimaryColumn } from 'typeorm';

import { IParticipation } from '../../../domaine/lib/tirages/interfaces/participation.interface';
import { Utilisateur } from '../utilisateurs/utilisateur.entity';
import { Tirage } from './tirage.entity';

@Entity()
export class Participation implements IParticipation {

  constructor(participation: Pick<Participation, 'participant' | 'offreA'>) {
    Object.assign(this, participation);
  }

  @ManyToOne(type => Tirage, { nullable: false })
  tirage: Tirage;
  
  // TypeOrm ne supporte pas le fait de définir directement la colonne d'une FK comme PK
  // Il faut donc créer une 2nde colonne avec le même nom que celle générée par TypeORM pour la FK, et la définir en PK
  // NOTE : la redondance n'est que dans le code, pas dans le SQL généré (une seule colonne PK + FK)
  @PrimaryColumn()
  tirageId: number;

  @ManyToOne(type => Utilisateur, { nullable: false })
  participant: Utilisateur;
  
  // Même remarque que pour tirageId
  @PrimaryColumn()
  participantId: number;

  @ManyToOne(type => Utilisateur, { nullable: true })
  offreA?: Utilisateur;
}
