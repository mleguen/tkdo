import { getRepositoryToken } from "@nestjs/typeorm";
import { PortTirages } from "../../../shared/domaine";
import { TirageRepository, ParticipationRepository } from "../../../shared/schema";

export const providerPortTirage = {
  provide: PortTirages,
  useFactory: (
    participationRepository: ParticipationRepository,
    tirageRepository: TirageRepository
  ) => new PortTirages(participationRepository, tirageRepository),
  inject: [
    getRepositoryToken(ParticipationRepository),
    getRepositoryToken(TirageRepository)
  ]
};
