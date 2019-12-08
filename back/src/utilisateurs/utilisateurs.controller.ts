import { Controller, UseGuards, Get, Param, Query, ParseIntPipe, Post, Body, Delete, BadRequestException, NotFoundException } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { InjectRepository } from '@nestjs/typeorm';
import { pick } from 'lodash';

import { Droit, StatutTirage } from '../../../shared/domaine';
import { ParticipationRepository, TirageRepository, Tirage, Utilisateur } from '../../../shared/schema';
import { UtilisateurAuthentifieDoitAvoirDroit } from '../auth/droit.decorator';
import { DroitGuard } from '../auth/droits.guard';
import { IdUtilisateurGuard } from '../auth/id-utilisateur.guard';
import { UtilisateurAuthentifieDoitAvoirId } from '../auth/param-id-utilisateur.decorator';
import { GetTirageResDTO, TirageResumeDTO, PostTirageReqDTO, PostTirageResDTO } from './dto';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitGuard, IdUtilisateurGuard)
export class UtilisateursController {

  constructor(
    @InjectRepository(ParticipationRepository) private readonly participationRepository: ParticipationRepository,
    @InjectRepository(TirageRepository) private readonly tirageRepository: TirageRepository
  ) {}

  @Delete('/:idUtilisateur/tirages/:idTirage')
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ModificationTirages)
  @UtilisateurAuthentifieDoitAvoirId()
  async deleteTirageUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Param('idTirage', new ParseIntPipe()) idTirage: number
  ): Promise<any> {
    const tirage = await this.tirageRepository.findOne(idTirage, {
      relations: ['organisateur']
    });
    if (!tirage) {
      throw new NotFoundException("ce tirage n'existe pas");
    }
    if (tirage.organisateur.id !== idUtilisateur) {
      throw new BadRequestException("vous n'êtes pas l'organisateur de ce tirage");
    }
    // TODO : la suite devrait être faite dans le domaine, via un plugin repository
    if (tirage.statut !== StatutTirage.Cree) {
      throw new BadRequestException("le tirage est déjà lancé");
    }

    await this.tirageRepository.remove(tirage);
  }

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
    if (!tirage) {
      throw new NotFoundException("ce tirage n'existe pas");
    }
    const estOrganisateur = tirage.organisateur.id === idUtilisateur;
    const participationUtilisateur = tirage.participations.find(participation => participation.participant.id === idUtilisateur);
    if (!estOrganisateur && !participationUtilisateur) {
      throw new BadRequestException("vous ne participez pas à ce tirage et n'en êtes pas l'organisateur");
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
  async postTirageUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Body() body: PostTirageReqDTO
  ): Promise<PostTirageResDTO> {
    // TODO : devrait être fait dans le domaine, via un plugin repository
    let tirage = new Tirage(body);
    tirage.organisateur = new Utilisateur({ id: idUtilisateur });
    tirage = await this.tirageRepository.save(tirage);
    return pick(tirage, 'id');
  }
}
