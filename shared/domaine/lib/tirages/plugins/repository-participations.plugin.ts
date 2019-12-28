import { IParticipation } from "../types";

export interface IPluginRepositoryParticipations {
  remove(participation: IParticipation | IParticipation[]): Promise<any>;
  save(participation: IParticipation): Promise<any>;
}
