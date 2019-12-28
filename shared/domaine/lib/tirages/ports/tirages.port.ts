import { omit } from "lodash";
import { DomaineError } from "../../commun";
import { IUtilisateur } from "../../utilisateurs";
import { StatutTirage } from "../constantes";
import { IPluginRepositoryTirages, IPluginRepositoryParticipations } from "../plugins";
import { ITirage, TirageAnonymise, IParticipation } from "../types";

export class NiParticipantNiOrganisateurError extends DomaineError {
  constructor(utilisateur: Pick<IUtilisateur, "id">, tirage: Pick<ITirage, "id">) {
    super(`l'utilisateur ${utilisateur.id} ne participe pas au tirage ${tirage.id} et n'en est pas l'organisateur`);
  }
}

export class TirageDejaLanceError extends DomaineError {
  constructor(tirage: Pick<ITirage, "id">) {
    super(`le tirage ${tirage.id} est déjà lancé`);
  }
}

export class PortTirages {

  constructor(
    private readonly repositoryParticipations: IPluginRepositoryParticipations,
    private readonly repositoryTirages: IPluginRepositoryTirages
  ) { }

  async anonymiseTirage(tirage: ITirage, utilisateur: IUtilisateur): Promise<TirageAnonymise> {
    const estOrganisateur = tirage.organisateur.id === utilisateur.id;
    const participation = tirage.participations.find(participation => participation.participant.id === utilisateur.id);
    if (!estOrganisateur && !participation) throw new NiParticipantNiOrganisateurError(utilisateur, tirage);

    return Object.assign(
      omit(tirage, "organisateur", "participations"),
      {
        estOrganisateur,
        participants: tirage.participations.map(participation => Object.assign(
          {},
          participation.participant,
          {
            estAQuiOffrir: participation && participation.offreA && (participation.participant.id === participation.offreA.id),
            estUtilisateur: participation.participant.id === utilisateur.id
          }
        ))
      },
    );
  }

  async deleteTirage(tirage: ITirage): Promise<void> {
    // TODO : gérer le CASCADE dans le modèle pour ne pas avoir à supprimer manuellement les participations avant le tirage
    if (tirage.statut !== StatutTirage.Cree) throw new TirageDejaLanceError(tirage);
    await this.repositoryParticipations.remove(tirage.participations);
    tirage.participations = [];
    return this.repositoryTirages.remove(tirage);
  }

  createParticipation(participation: IParticipation): Promise<void> {
    if (participation.tirage.statut !== StatutTirage.Cree) throw new TirageDejaLanceError(participation.tirage);
    return this.repositoryParticipations.save(participation);
  }

  deleteParticipation(participation: IParticipation): Promise<void> {
    if (participation.tirage.statut !== StatutTirage.Cree) throw new TirageDejaLanceError(participation.tirage);
    return this.repositoryParticipations.remove(participation);
  }
}
