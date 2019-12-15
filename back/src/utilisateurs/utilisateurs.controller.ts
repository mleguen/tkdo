import { Controller, UseGuards, Get, Param, Query, ParseIntPipe, Post, Body, Delete, BadRequestException, NotFoundException } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { InjectRepository } from '@nestjs/typeorm';
import { pick } from 'lodash';
import { Repository } from 'typeorm';

import { Droit, StatutTirage } from '../../../shared/domaine';
import { ParticipationRepository, TirageRepository, Tirage, Utilisateur, Participation } from '../../../shared/schema';
import { UtilisateurAuthentifieDoitAvoirDroit } from '../auth/droit.decorator';
import { DroitGuard } from '../auth/droits.guard';
import { IdUtilisateurGuard } from '../auth/id-utilisateur.guard';
import { UtilisateurAuthentifieDoitAvoirId } from '../auth/param-id-utilisateur.decorator';
import { GetTirageResDTO, TirageResumeDTO, PostParticipantsTirageReqDTO, PostTiragesReqDTO, PostTiragesResDTO, UtilisateurResumeDTO } from './dto';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitGuard, IdUtilisateurGuard)
export class UtilisateursController {

  constructor(
    @InjectRepository(ParticipationRepository) private readonly participationRepository: ParticipationRepository,
    @InjectRepository(TirageRepository) private readonly tirageRepository: TirageRepository,
    @InjectRepository(Utilisateur) private readonly utilisateurRepository: Repository<Utilisateur>
  ) {}

  @Get('')
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ConsultationUtilisateurs)
  async getUtilisateurs(): Promise<UtilisateurResumeDTO[]> {
    // TODO : toute manipulation de repository devrait être faite dans un service
    let utilisateurs = await this.utilisateurRepository.find();
    return utilisateurs.map(utilisateur => pick(utilisateur, 'id', 'nom', 'login'));
  }

  @Delete('/:idUtilisateur/tirages/:idTirage/participants/:idParticipant')
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ModificationTirages)
  @UtilisateurAuthentifieDoitAvoirId()
  async deleteParticipantTirageUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Param('idTirage', new ParseIntPipe()) idTirage: number,
    @Param('idParticipant', new ParseIntPipe()) idParticipant: number
  ): Promise<any> {
    // TODO : toute manipulation de repository devrait être faite dans un service
    const tirage = await this.tirageRepository.findOne(idTirage, {
      relations: ['organisateur', 'participations']
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
    let participation = tirage.participations.find(({ participantId }: Participation) => participantId === idParticipant)
    if (!participation) {
      throw new BadRequestException("cet utilisateur ne participe déjà pas au tirage");
    }
    await this.participationRepository.remove(participation);
  }

  @Delete('/:idUtilisateur/tirages/:idTirage')
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ModificationTirages)
  @UtilisateurAuthentifieDoitAvoirId()
  async deleteTirageUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Param('idTirage', new ParseIntPipe()) idTirage: number
  ): Promise<any> {
    // TODO : toute manipulation de repository devrait être faite dans un service
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
    // TODO : toute manipulation de repository devrait être faite dans un service
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
    // TODO : toute manipulation de repository devrait être faite dans un service
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
          pick(participation.participant, 'id', 'nom', 'login'),
          participationUtilisateur && participationUtilisateur.offreA && (participation.participant.id === participationUtilisateur.offreA.id)
            ? { estAQuiOffrir: true } : {},
          (participation.participant.id === idUtilisateur) ? { estUtilisateur: true } : {}
        ))
      },
      estOrganisateur ? { estOrganisateur } : {}
    );
  }

  @Post('/:idUtilisateur/tirages/:idTirage/participants')
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ModificationTirages)
  @UtilisateurAuthentifieDoitAvoirId()
  async postParticipantsTirageUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Param('idTirage', new ParseIntPipe()) idTirage: number,
    @Body() body: PostParticipantsTirageReqDTO
  ): Promise<any> {
    // TODO : toute manipulation de repository devrait être faite dans un service
    const tirage = await this.tirageRepository.findOne(idTirage, {
      relations: ['organisateur', 'participations', 'participations.participant']
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
    const participant = await this.utilisateurRepository.findOne(body.id);
    if (!participant) {
      throw new NotFoundException("cet utilisateur n'existe pas");
    }
    let participation = new Participation({ participant, tirage });
    await this.participationRepository.save(participation);
  }

  @Post('/:idUtilisateur/tirages')
  @UtilisateurAuthentifieDoitAvoirDroit(Droit.ModificationTirages)
  @UtilisateurAuthentifieDoitAvoirId()
  async postTiragesUtilisateur(
    @Param('idUtilisateur', new ParseIntPipe()) idUtilisateur: number,
    @Body() body: PostTiragesReqDTO
  ): Promise<PostTiragesResDTO> {
    // TODO : toute manipulation de repository devrait être faite dans un service
    // TODO : devrait être fait dans le domaine, via un plugin repository
    let tirage = new Tirage(body);
    tirage.organisateur = new Utilisateur({ id: idUtilisateur });
    tirage = await this.tirageRepository.save(tirage);
    return pick(tirage, 'id');
  }
}
