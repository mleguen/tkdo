import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { pick } from 'lodash';

import { PortHabilitations } from '../../../domaine';
import { TirageRepository } from '../../../schema';
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
    private tirageRepository: TirageRepository
  ) {}

  @Get('/:idUtilisateur/tirages')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
  async getTiragesUtilisateur(@Param('idUtilisateur') idUtilisateur: string): Promise<TirageResumeDTO[]> {
    let tirages = await this.tirageRepository.findTiragesParticipant(+idUtilisateur);
    return tirages.map(tirage => pick(tirage, 'id', 'titre', 'date'));
  }

  @Get('/:idUtilisateur/tirages/:idTirage')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
  async getTirageUtilisateur(@Param('idUtilisateur') idUtilisateur: string, @Param('idTirage') idTirage: string): Promise<TirageDTO> {
    let tirage = await this.tirageRepository.findTirageParticipant(+idUtilisateur, +idTirage);
    return Object.assign(
      pick(tirage, 'id', 'titre', 'date'),
      {
        participants: tirage.participants.map(participant => pick(participant, 'id', 'nom'))
      }
    );
  }
}
