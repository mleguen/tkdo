import { DOCUMENT } from '@angular/common';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Inject, Injectable } from '@angular/core';
import { BehaviorSubject, Observable, firstValueFrom, of } from 'rxjs';
import { first, map, shareReplay, switchMap } from 'rxjs/operators';

export interface Occasion {
  id: number;
  date: string;
  titre: string;
  participants: Utilisateur[];
  resultats: Resultat[]
}

export interface Utilisateur {
  id: number;
  nom: string;
  genre: Genre;
}

export interface UtilisateurPrive extends Utilisateur {
  email: string;
  admin: boolean;
  identifiant: string;
  prefNotifIdees: string;
}

export enum Genre {
  Feminin = 'F',
  Masculin = 'M',
}

export enum PrefNotifIdees {
  Aucune = 'N',
  Instantanee = 'I',
  Quotidienne = 'Q',
}

export interface Resultat {
  idQuiOffre: number;
  idQuiRecoit: number;
}

export interface IdeesPour {
  utilisateur: Utilisateur;
  idees: Idee[];
}

export interface Idee {
  id: number;
  description: string;
  auteur: Utilisateur;
  dateProposition: string;
}

const URL_API = '/api';
const URL_CONNEXION = `${URL_API}/connexion`;
const URL_LISTE_OCCASIONS = `${URL_API}/occasion`;
const URL_OCCASION = (idOccasion: number) => `${URL_API}/occasion/${idOccasion}`;
const URL_UTILISATEUR = (idUtilisateur: number) => `${URL_API}/utilisateur/${idUtilisateur}`;
const URL_IDEES = `${URL_API}/idee`;
const URL_IDEE = (idIdee: number) => `${URL_IDEES}/${idIdee}`;
const URL_SUPPRESSION_IDEE = (idIdee: number) => `${URL_IDEE(idIdee)}/suppression`;

const CLE_ID_UTILISATEUR = 'id_utilisateur';
const CLE_LISTE_OCCASIONS = 'occasions';
const CLE_TOKEN = 'backend-token';

interface PostConnexionDTO {
  token: string;
  utilisateur: Pick<UtilisateurPrive, 'id' | 'nom' | 'admin'>;
}

@Injectable({
  providedIn: 'root'
})
export class BackendService {

  erreur$ = new BehaviorSubject<string | undefined>(undefined);
  occasions$: Observable<Occasion[] | null>;
  token = localStorage.getItem(CLE_TOKEN);
  utilisateurConnecte$: Observable<UtilisateurPrive | null>;

  protected idUtilisateurConnecte$: BehaviorSubject<number | null>;

  constructor(
    private readonly http: HttpClient,
    @Inject(DOCUMENT) private document: Document,
  ) {
    this.idUtilisateurConnecte$ = new BehaviorSubject(JSON.parse(localStorage.getItem(CLE_ID_UTILISATEUR) || 'null'));
    this.utilisateurConnecte$ = this.idUtilisateurConnecte$.pipe(
      switchMap(idUtilisateur => idUtilisateur === null ? of(null) : this.http.get<UtilisateurPrive>(URL_UTILISATEUR(idUtilisateur))),
      shareReplay(1)
    );
    this.occasions$ = this.idUtilisateurConnecte$.pipe(
      switchMap(idUtilisateur => idUtilisateur === null ? of(null) : this.http.get<Occasion[]>(`${URL_LISTE_OCCASIONS}?idParticipant=${idUtilisateur}`)),
      shareReplay(1)
    );
  }

  async ajouteIdee(idUtilisateur: number, description: string) {
    return firstValueFrom(this.http.post(URL_IDEES, { idUtilisateur, idAuteur: await this.getIdUtilisateurConnecte(), description }));
  }

  async connecte(identifiant: string, mdp: string) {
    const { token, utilisateur } = await firstValueFrom(this.http.post<PostConnexionDTO>(URL_CONNEXION, { identifiant, mdp }));
    localStorage.setItem(CLE_ID_UTILISATEUR, JSON.stringify(utilisateur.id));
    localStorage.setItem(CLE_TOKEN, token);
    // this.token doit être set avant de publier l'utilisateur sur l'observable
    // (ce qui peut déclencher des appels réseaux nécessitant le token)
    this.token = token;
    this.idUtilisateurConnecte$.next(utilisateur.id);
  }

  async deconnecte() {
    this.token = null;
    localStorage.removeItem(CLE_ID_UTILISATEUR);
    localStorage.removeItem(CLE_LISTE_OCCASIONS);
    localStorage.removeItem(CLE_TOKEN);
    this.idUtilisateurConnecte$.next(null);
  }

  admin() {
    return firstValueFrom(
      this.utilisateurConnecte$.pipe(
        first(),
        map(u => !!u?.admin),
      )
    );
  }

  async estConnecte() {
    return await firstValueFrom(this.idUtilisateurConnecte$) !== null;
  }

  estUrlBackend(url: string): boolean {
    return !!url.match(new RegExp(`^${URL_API}`));
  }

  getIdees(idUtilisateur: number) {
    return this.http.get<IdeesPour>(`${URL_IDEES}?idUtilisateur=${idUtilisateur}&supprimees=0`);
  }

  async getIdUtilisateurConnecte() {
    const idUtilisateurConnecte = await firstValueFrom(this.idUtilisateurConnecte$);
    if (idUtilisateurConnecte == null) throw Error("Aucun utilisateur connecté");
    return idUtilisateurConnecte;
  }

  getOccasion(idOccasion: number) {
    return firstValueFrom(this.http.get<Occasion>(URL_OCCASION(idOccasion)));
  }

  getAbsUrlApi() {
    return new URL(URL_API, this.document.baseURI).href;
  }

  async getUtilisateurConnecte() {
    const utilisateurConnecte = await firstValueFrom(this.utilisateurConnecte$);
    if (utilisateurConnecte == null) throw Error("Aucun utilisateur connecté");
    return utilisateurConnecte;
  }

  async modifieUtilisateur(utilisateur: Partial<UtilisateurPrive>) {
    return firstValueFrom(this.http.put(URL_UTILISATEUR(await this.getIdUtilisateurConnecte()), utilisateur));
  }

  notifieErreurHTTP(error: HttpErrorResponse) {
    // Les erreurs applicatives sont censées être prises en compte
    if (error.status === 400) return;

    this.erreur$.next(error.message || (`${error.status} ${error.statusText}`));
  }

  notifieSuccesHTTP() {
    this.erreur$.next(undefined);
  }

  supprimeIdee(idIdee: number) {
    return firstValueFrom(this.http.post(URL_SUPPRESSION_IDEE(idIdee), undefined));
  }
}
