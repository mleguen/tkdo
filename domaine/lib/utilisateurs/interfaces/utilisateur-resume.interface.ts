import { Utilisateur } from "./utilisateur.interface";

export type UtilisateurResume = Pick<Utilisateur, 'id' | 'nom'>;
