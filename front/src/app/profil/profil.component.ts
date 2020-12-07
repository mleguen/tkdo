import { Component, OnInit } from '@angular/core';
import { Validators, FormBuilder, ValidatorFn } from '@angular/forms';
import { BackendService, Genre, PrefNotifIdees, Utilisateur } from '../backend.service';

@Component({
  selector: 'app-profil',
  templateUrl: './profil.component.html',
  styleUrls: ['./profil.component.scss']
})
export class ProfilComponent implements OnInit {

  Genre = Genre;
  PrefNotifIdees = PrefNotifIdees;

  formProfil = this.fb.group(
    {
      'identifiant': [''],
      'nom': ['', [Validators.minLength(3)]],
      'email': ['', [Validators.email]],
      'genre': [''],
      'prefNotifIdees': [''],
      'mdp': ['', [Validators.minLength(8)]],
      'confirmeMdp': [''],
    },
    {
      validators: [
        requireOne(['nom', 'email', 'genre', 'prefNotifIdees'], ['mdp']),
        sameValueIfDefined('mdp', 'confirmeMdp'),
      ]
    },
  );
  erreurModification: string;
  enregistre: boolean;
  utilisateur: Utilisateur;

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
  ) { }

  ngOnInit(): void {
    this.backend.getUtilisateur$().subscribe(
      utilisateur => {
        this.utilisateur = utilisateur;
        this.identifiant.setValue(utilisateur.identifiant);
        this.nom.setValue(utilisateur.nom);
        this.email.setValue(utilisateur.email);
        this.genre.setValue(utilisateur.genre);
        this.prefNotifIdees.setValue(utilisateur.prefNotifIdees);
      },
      // Les erreurs backend sont déjà affichées par AppComponent
      () => {}
    );
  }

  get identifiant() {
    return this.formProfil.get('identifiant');
  }

  get nom() {
    return this.formProfil.get('nom');
  }
  
  get email() {
    return this.formProfil.get('email');
  }

  get genre() {
    return this.formProfil.get('genre');
  }

  get prefNotifIdees() {
    return this.formProfil.get('prefNotifIdees');
  }
  
  get mdp() {
    return this.formProfil.get('mdp');
  }

  async modifie() {
    const { nom, email, genre, prefNotifIdees, mdp } = this.formProfil.value;
    try {
      Object.assign(this.utilisateur, { nom, email, genre, prefNotifIdees });
      if (mdp) Object.assign(this.utilisateur, { mdp });
      await this.backend.modifieUtilisateur(this.utilisateur);
      for (let champ of ['mdp', 'confirmeMdp']) this.formProfil.get(champ).reset();
      this.erreurModification = undefined;
      this.enregistre = true;
    }
    catch (err) {
      this.erreurModification = err.message;
      this.enregistre = false;
    }
  }
}

/**
 * Validate that at least one list have all fields non-empty
 */
function requireOne (...lists: string[][]): ValidatorFn {
  return group => {
    return lists.some(names =>
      names.every(name => Validators.required(group.get(name)) === null)
    )
      ? null
      : { requireOne: true };
  }
}

/**
 * Validate the fields have the same value if non-empty
 */
function sameValueIfDefined (name1: string, name2: string): ValidatorFn {
  return group => {
    if (Validators.required(group.get(name1)) !== null) return null;
    return group.get(name1).value !== group.get(name2).value ? { sameValueIfDefined: [name1, name2] } : null;
  }
}
