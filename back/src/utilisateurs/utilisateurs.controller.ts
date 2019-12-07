import { Controller, UseGuards, Get, Param, Query, ParseIntPipe, Post } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { InjectRepository } from '@nestjs/typeorm';
import { pick } from 'lodash';

import { Droit } from '../../../shared/domaine';
import { ParticipationRepository, TirageRepository } from '../../../shared/schema';
import { NecessiteDroit } from '../auth/droit.decorator';
import { DroitGuard } from '../auth/droits.guard';
import { IdUtilisateurGuard } from '../auth/id-utilisateur.guard';
import { ParamIdUtilisateur } from '../auth/param-id-utilisateur.decorator';
import { GetTirageDTO } from './dto/get-tirage.dto';
import { TirageResumeDTO } from './dto/tirage-resume.dto';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitGuard, IdUtilisateurGuard)
export class UtilisateursController {

  constructor(
    @InjectRepository(ParticipationRepository) private readonly participationRepository: ParticipationRepository,
    @InjectRepository(TirageRepository) private readonly tirageRepository: TirageRepository
  ) {}

  @Get('/:idUtilisateur/tirages')
  @NecessiteDroit(Droit.ConsultationTirages)
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
  @NecessiteDroit(Droit.ConsultationTirages)
  @ParamIdUtilisateur()
  async getTirageUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Param('idTirage', new ParseIntPipe()) idTirage: number
  ): Promise<GetTirageDTO> {
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
            ? { estAQuiOffrir: true } : {},
          (participation.participant.id === idUtilisateur) ? { estUtilisateur: true } : {}
        ))
      },
      estOrganisateur ? { estOrganisateur } : {}
    );
  }

  @Post('/:idUtilisateur/tirages')
  @NecessiteDroit(Droit.ModificationTirages)
  @ParamIdUtilisateur()
  async postTiragesUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Query('organisateur', new ParseIntPipe()) organisateur?: number
  ): Promise<TirageResumeDTO[]> {
    let tirages = !!organisateur
      ? await this.tirageRepository.findTiragesOrganisateur(idUtilisateur)
      : await this.participationRepository.findTiragesParticipant(idUtilisateur);
    return tirages.map(tirage => pick(tirage, 'id', 'titre', 'date'));
  }
}
