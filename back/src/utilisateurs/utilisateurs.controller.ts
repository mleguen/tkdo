import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { InjectRepository } from '@nestjs/typeorm';
import { pick } from 'lodash';
import { Repository } from 'typeorm';

import { PortHabilitations } from '../../../domaine';
import { Tirage, Utilisateur } from '../../../schema';
import { Droit } from '../auth/droit.decorator';
import { DroitsGuard } from '../auth/droits.guard';
import { IdUtilisateurGuard } from '../auth/id-utilisateur.guard';
import { ParamIdUtilisateur } from '../auth/param-id-utilisateur.decorator';
import { TirageDTO } from './dto/tirage.dto';
import { TirageResumeDTO } from './dto/tirage-resume.dto';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitsGuard, IdUtilisateurGuard)
export class UtilisateursController {

  constructor(
    @InjectRepository(Utilisateur) private utilisateursRepository: Repository<Utilisateur>,
    @InjectRepository(Tirage) private tiragesRepository: Repository<Tirage>
  ) {}

  @Get('/:idUtilisateur/tirages')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
  async getTiragesUtilisateur(@Param('idUtilisateur') idUtilisateur: string): Promise<TirageResumeDTO[]> {
    let utilisateur = await this.utilisateursRepository.findOne(idUtilisateur, { relations: ['tirages'] });
    return utilisateur.tirages.map(tirage => pick(tirage, 'id', 'titre', 'date'));
  }

  @Get('/:idUtilisateur/tirages/:idTirage')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
  async getTirageUtilisateur(@Param('idUtilisateur') idUtilisateur: string, @Param('idTirage') idTirage: string): Promise<TirageDTO> {
    let tirage = await this.tiragesRepository.findOne(+idTirage, { relations: ['participants'] });
    if (!tirage.participants.find(participant => participant.id === +idUtilisateur)) {
      throw new Error(`l'utilisateur ${idUtilisateur} ne participe pas au tirage ${idTirage}`);
    }
    return Object.assign(
      pick(tirage, 'id', 'titre', 'date'),
      {
        participants: tirage.participants.map(participant => pick(participant, 'id', 'nom'))
      }
    );
  }
}
