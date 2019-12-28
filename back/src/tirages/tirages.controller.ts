import { Controller, UseGuards, Get, Param, Query, ParseIntPipe, Post, Body, Delete } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { pick } from 'lodash';

import { Droit, IUtilisateur, ITirage, TirageAnonymise } from '../../../shared/domaine';
import { DroitNecessaireGuard, DroitNecessaire, Utilisateur } from '../auth';
import { TiragesService } from './tirages.service';

@Controller('tirages')
@UseGuards(AuthGuard('jwt'), DroitNecessaireGuard)
export class TiragesController {

  constructor(
    private readonly tiragesService: TiragesService
  ) {}

  @Get()
  @DroitNecessaire(Droit.ConsultationTirages)
  async getTirages(
    @Utilisateur() utilisateur: IUtilisateur,
    @Query('organisateur', new ParseIntPipe()) organisateur?: number
  ): Promise<Pick<ITirage, 'id' | 'titre' | 'date'>[]> {
    const tirages = await this.tiragesService.readTirages(utilisateur, !!organisateur);
    return tirages.map(tirage => pick(tirage, 'id', 'titre', 'date'));
  }

  @Post()
  @DroitNecessaire(Droit.ModificationTirages)
  async postTirages(
    @Utilisateur() utilisateur: IUtilisateur,
    @Body() data: Pick<ITirage, 'titre' | 'date'>
  ): Promise<Pick<ITirage, 'id'>> {
    const tirage = await this.tiragesService.createTirage(data, utilisateur);
    return pick(tirage, 'id');
  }

  @Get(':idTirage')
  @DroitNecessaire(Droit.ConsultationTirages)
  async getTirage(
    @Utilisateur() utilisateur: IUtilisateur,
    @Param('idTirage', new ParseIntPipe()) idTirage: number
  ): Promise<TirageAnonymise> {
    const tirage = await this.tiragesService.readTirageAnonymise({ id: idTirage }, utilisateur);
    return Object.assign(
      pick(tirage, "id", "titre", "date", "statut", "estOrganisateur"),
      {
        participants: tirage.participants.map(participant =>
          pick(participant, "id", "nom", "login", "estAQuiOffrir", "estUtilisateur")
        ) 
      }
    );
  }

  @Delete('/:idTirage')
  @DroitNecessaire(Droit.ModificationTirages)
  deleteTirage(
    @Utilisateur() utilisateur: IUtilisateur,
    @Param('idTirage', new ParseIntPipe()) idTirage: number
  ): Promise<any> {
    return this.tiragesService.deleteTirage({ id: idTirage }, utilisateur);
  }

  @Post('/:idTirage/participants')
  @DroitNecessaire(Droit.ModificationTirages)
  postParticipation(
    @Utilisateur() utilisateur: IUtilisateur,
    @Param('idTirage', new ParseIntPipe()) idTirage: number,
    @Body() participant: Pick<IUtilisateur, 'id'>
  ): Promise<any> {
    return this.tiragesService.createParticipation({ id: idTirage }, participant, utilisateur);
  }

  @Delete('/:idTirage/participants/:idParticipant')
  @DroitNecessaire(Droit.ModificationTirages)
  deleteParticipation(
    @Utilisateur() utilisateur: IUtilisateur,
    @Param('idTirage', new ParseIntPipe()) idTirage: number,
    @Param('idParticipant', new ParseIntPipe()) idParticipant: number
  ): Promise<any> {
    return this.tiragesService.deleteParticipation({ id: idTirage }, { id: idParticipant }, utilisateur);
  }
}
