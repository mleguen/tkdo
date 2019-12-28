import { ITirage } from "../types";

export interface IPluginRepositoryTirages {
  remove(tirage: ITirage): Promise<any>;
}
