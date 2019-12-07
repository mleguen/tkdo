import { Controller, UseGuards, Get, Param, Query, ParseIntPipe, Post, Body } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { InjectRepository } from '@nestjs/typeorm';
import { pick } from 'lodash';

import { Droit } from '../../../shared/domaine';
import { ParticipationRepository, TirageRepository, Tirage, Utilisateur } from '../../../shared/schema';
import { UtilisateurAuthentifieDoitAvoirDroit } from '../auth/droit.decorator';
import { DroitGuard } from '../auth/droits.guard';
import { IdUtilisateurGuard } from '../auth/id-utilisateur.guard';
import { UtilisateurAuthentifieDoitAvoirId } from '../auth/param-id-utilisateur.decorator';
import { GetTirageResDTO } from './dto/get-tirage-res.dto';
import { TirageResumeDTO } from './dto/tirage-resume.dto';
import { PostTirageReqDTO } from './dto/post-tirage-req.dto';
import { PostTirageResDTO } from './dto/post-tirage-res.dto';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitGuard, IdUtilisateurGuard)
export class UtilisateursController {

  constructor(
    @InjectRepository(ParticipationRepository) private readonly participationRepository: ParticipationRepository,
    @InjectRepository(TirageRepository) private readonly tirageRepository: TirageRepository
  ) {}

  @Get('/:idUtilisateur/tirages')
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ConsultationTirages)
  @UtilisateurAuthentifieDoitAvoirId()
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
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ConsultationTirages)
  @UtilisateurAuthentifieDoitAvoirId()
  async getTirageUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Param('idTirage', new ParseIntPipe()) idTirage: number
  ): Promise<GetTirageResDTO> {
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
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ModificationTirages)
  @UtilisateurAuthentifieDoitAvoirId()
  async postTiragesUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Body() proprietes: PostTirageReqDTO
  ): Promise<PostTirageResDTO> {
    let tirage = new Tirage(proprietes);
    tirage.organisateur = new Utilisateur({ id: idUtilisateur });
    tirage = await this.tirageRepository.save(tirage);
    return pick(tirage, 'id');
  }
}
