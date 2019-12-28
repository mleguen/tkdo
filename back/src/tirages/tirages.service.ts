import { Injectable, NotFoundException, BadRequestException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';

import { ITirage, IUtilisateur, PortTirages, NiParticipantNiOrganisateurError, TirageDejaLanceError } from '../../../shared/domaine';
import { ParticipationRepository, TirageRepository, Tirage, Utilisateur, Participation } from '../../../shared/schema';
import { omit } from 'lodash';

@Injectable()
export class TiragesService {

  constructor(
    @InjectRepository(ParticipationRepository) private readonly participationRepository: ParticipationRepository,
    @InjectRepository(TirageRepository) private readonly tirageRepository: TirageRepository,
    private readonly portTirages: PortTirages,
  ) { }

  createTirage(data: Partial<ITirage>, organisateur: Pick<IUtilisateur, "id">): Promise<ITirage> {
    const eTirage = new Tirage(data);
    eTirage.organisateur = new Utilisateur(organisateur);
    return this.tirageRepository.save(eTirage);
  }

  readTirages(utilisateur: Pick<IUtilisateur, "id">, organisateur: boolean): Promise<ITirage[]> {
    return organisateur
      ? this.tirageRepository.findTiragesOrganisateur(utilisateur.id)
      : this.participationRepository.findTiragesParticipant(utilisateur.id);
  }

  private async readTirage(tirage: Pick<ITirage, "id">, relations: string[]) {
    const eTirage = await this.tirageRepository.findOne(tirage.id, { relations });
    if (!eTirage) throw new NotFoundException(`le tirage ${tirage.id} n'existe pas`);
    return eTirage;
  }

  private async readTirageEtVerifieOrganisateur(
    tirage: Pick<ITirage, "id">,
    utilisateur: Pick<IUtilisateur, "id">,
    relations: string[]
  ) {
    const eTirage = await this.readTirage(tirage, relations);
    this.verifieOrganisateur(eTirage.organisateur, utilisateur);
    return eTirage;
  }

  private async verifieOrganisateur(
    organisateur: Pick<IUtilisateur, "id">,
    utilisateur: Pick<IUtilisateur, "id">
  ) {
    if (organisateur.id !== utilisateur.id) {
      throw new BadRequestException("vous n'êtes pas l'organisateur de ce tirage");
    }
  }

  async readTirageAnonymise(tirage: Pick<ITirage, "id">, utilisateur: IUtilisateur) {
    const eTirage = await this.readTirage(tirage, [
      'organisateur', 'participations', 'participations.participant', 'participations.offreA'
    ]);

    try {
      return await this.portTirages.anonymiseTirage(eTirage, utilisateur);
    } catch (err) {
      if (err instanceof NiParticipantNiOrganisateurError) {
        throw new BadRequestException("vous ne participez pas à ce tirage et n'en êtes pas l'organisateur");
      }
      throw err;
    }
  }

  async deleteTirage(tirage: Pick<ITirage, "id">, utilisateur: Pick<IUtilisateur, "id">) {
    const eTirage = await this.readTirageEtVerifieOrganisateur(tirage, utilisateur, ['organisateur', 'participations']);

    try {
      return await this.portTirages.deleteTirage(eTirage);
    } catch (err) {
      if (err instanceof TirageDejaLanceError) {
        throw new BadRequestException("le tirage est déjà lancé");
      }
      throw err;
    }
  }

  async createParticipation(
    tiragePartiel: Pick<ITirage, "id">,
    participantPartiel: Pick<IUtilisateur, "id">,
    utilisateur: Pick<IUtilisateur, "id">
  ) {
    const tirage = await this.readTirageEtVerifieOrganisateur(tiragePartiel, utilisateur, [
      'organisateur', 'participations', "participations.participant"
    ]);

    const participant = new Utilisateur(participantPartiel);
    
    try {
      return await this.portTirages.createParticipation(new Participation({ participant, tirage }));
    } catch (err) {
      if (err instanceof TirageDejaLanceError) {
        throw new BadRequestException("le tirage est déjà lancé");
      }
      throw err;
    }
  }

  async deleteParticipation(
    tirage: Pick<ITirage, "id">,
    participant: Pick<IUtilisateur, "id">,
    utilisateur: Pick<IUtilisateur, "id">
  ) {
    const participation = await this.participationRepository.findOne(
      {
        tirageId: tirage.id,
        participantId: participant.id
      },
      {
        relations: ["tirage", "tirage.organisateur"]
      }
    );
    if (!participation) {
      throw new BadRequestException("cet utilisateur ne participe déjà pas au tirage");
    }
    this.verifieOrganisateur(participation.tirage.organisateur, utilisateur);

    try {
      return await this.portTirages.deleteParticipation(participation);
    } catch (err) {
      if (err instanceof TirageDejaLanceError) {
        throw new BadRequestException("le tirage est déjà lancé");
      }
      throw err;
    }
  }
}
