import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { InjectRepository } from '@nestjs/typeorm';
import { pick } from 'lodash';

import { PortHabilitations } from '../../../shared/domaine';
import { ParticipationRepository, Tirage } from '../../../shared/schema';
import { Droit } from '../auth/droit.decorator';
import { DroitsGuard } from '../auth/droits.guard';
import { IdUtilisateurGuard } from '../auth/id-utilisateur.guard';
import { ParamIdUtilisateur } from '../auth/param-id-utilisateur.decorator';
import { TirageDTO } from './dto/tirage.dto';
import { TirageResumeDTO } from './dto/tirage-resume.dto';
import { Repository } from 'typeorm';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitsGuard, IdUtilisateurGuard)
export class UtilisateursController {

  constructor(
    @InjectRepository(ParticipationRepository) private readonly participationRepository: ParticipationRepository,
    @InjectRepository(Tirage) private readonly tirageRepository: Repository<Tirage>
  ) {}

  @Get('/:idUtilisateur/tirages')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
  async getTiragesUtilisateur(@Param('idUtilisateur') idUtilisateur: string): Promise<TirageResumeDTO[]> {
    let tirages = await this.participationRepository.findTiragesParticipant(+idUtilisateur);
    return tirages.map(tirage => pick(tirage, 'id', 'titre', 'date'));
  }

  @Get('/:idUtilisateur/tirages/:idTirage')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
  async getTirageUtilisateur(@Param('idUtilisateur') idUtilisateur: string, @Param('idTirage') idTirage: string): Promise<TirageDTO> {
    let tirage = await this.tirageRepository.findOne(+idTirage, {
      relations: ['participations', 'participations.participant', 'participations.offreA']
    });
    let participationUtilisateur = tirage.participations.find(participation => participation.participant.id === +idUtilisateur);
    if (!participationUtilisateur) throw new Error("l'utilisateur ne participe pas à ce tirage");

    return Object.assign(
      pick(tirage, 'id', 'titre', 'date', 'statut'),
      {
        participants: tirage.participations.map(participation => Object.assign(
          pick(participation.participant, 'id', 'nom'),
          participationUtilisateur.offreA && (participation.participant.id === participationUtilisateur.offreA.id) ? {
            aQuiOffrir: true
          }: {}
        ))
      }
    );
  }
}
