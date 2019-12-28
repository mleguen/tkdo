import { Entity, ManyToOne, PrimaryColumn } from 'typeorm';

import { IParticipation } from '../../../domaine';
import { Utilisateur } from '../utilisateurs';
import { Tirage } from './tirage.entity';

@Entity()
export class Participation implements IParticipation {

  constructor(participation: Partial<IParticipation>) {
    Object.assign(this, participation);
  }

  @ManyToOne(type => Tirage, { nullable: false })
  tirage: Tirage;
  
  // TypeOrm ne supporte pas le fait de définir directement la colonne d'une FK comme PK
  // Il faut donc créer une 2nde colonne avec le même nom que celle générée par TypeORM pour la FK, et la définir en PK
  // NOTE : la redondance n'est que dans le code, pas dans le SQL généré (une seule colonne PK + FK)
  @PrimaryColumn()
  tirageId: number;

  @ManyToOne(type => Utilisateur, { nullable: false, cascade: true })
  participant: Utilisateur;
  
  // Même remarque que pour tirageId
  @PrimaryColumn()
  participantId: number;

  @ManyToOne(type => Utilisateur, { nullable: true, cascade: true })
  offreA?: Utilisateur;
}
