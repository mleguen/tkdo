import { Controller, UseGuards, Get, Param, Query, ParseIntPipe } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { InjectRepository } from '@nestjs/typeorm';
import { pick } from 'lodash';

import { PortHabilitations } from '../../../shared/domaine';
import { ParticipationRepository, TirageRepository } from '../../../shared/schema';
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
    @InjectRepository(ParticipationRepository) private readonly participationRepository: ParticipationRepository,
    @InjectRepository(TirageRepository) private readonly tirageRepository: TirageRepository
  ) {}

  @Get('/:idUtilisateur/tirages')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES)
  @ParamIdUtilisateur()
  async getTiragesUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Query('organisateur', new ParseIntPipe()) organisateur?: number
  ): Promise<TirageResumeDTO[]> {
    let tirages = !!organisateur
      ? await this.tirageRepository.findTiragesOrganisateur(idUtilisateur)
      : await this.participationRepository.findTiragesParticipant(idUtilisateur);
    return tirages.map(tirage => pick(tirage, 'id', 'titre', 'date'));
  }

  @Get('/:idUtilisateur/tirages/:idTirage')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES)
  @ParamIdUtilisateur()
  async getTirageUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Param('idTirage', new ParseIntPipe()) idTirage: number
  ): Promise<TirageDTO> {
    const tirage = await this.tirageRepository.findOne(idTirage, {
      relations: ['organisateur', 'participations', 'participations.participant', 'participations.offreA']
    });
    const estOrganisateur = tirage.organisateur.id === idUtilisateur;
    const participationUtilisateur = tirage.participations.find(participation => participation.participant.id === idUtilisateur);
    if (!estOrganisateur && !participationUtilisateur) {
      throw new Error("l'utilisateur ne participe pas à ce tirage et n'en est pas l'organisateur");
    }

    return Object.assign(
      pick(tirage, 'id', 'titre', 'date', 'statut'),
      {
        participants: tirage.participations.map(participation => Object.assign(
          pick(participation.participant, 'id', 'nom'),
          participationUtilisateur && participationUtilisateur.offreA && (participation.participant.id === participationUtilisateur.offreA.id)
            ? { estParticipantAQuiOffrir: true } : {},
          (participation.participant.id === idUtilisateur) ? { estUtilisateur: true } : {}
        ))
      },
      estOrganisateur ? { estOrganisateur } : {}
    );
  }
}
