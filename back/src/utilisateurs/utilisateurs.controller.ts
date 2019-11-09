import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { InjectRepository } from '@nestjs/typeorm';
import { pick } from 'lodash';

import { PortHabilitations } from '../../../shared/domaine';
import { TirageRepository } from '../../../shared/schema';
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
    // TODO: trouver une solution au problème d'injection venant du fait que @nestjs/typeorm et TirageRepository
    // n'utilisent pas la même classe Repository :
    // - l'une du module typeorm dans back/node_modules
    // - l'autre du module typeorm dans schema/node_modules
    // du coup, pour @nestjs/typeorm, TirageRepository n'est pas une instance de Repository
    // Cela remet complètement en question l'utilisation d'un "répertoire partagé" au lieu d'un "module partagé".
    @InjectRepository(TirageRepository) private readonly tirageRepository: TirageRepository
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
