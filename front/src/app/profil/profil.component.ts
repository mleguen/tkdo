import { Component, OnInit } from '@angular/core';
import { Validators, FormBuilder, ValidatorFn, AbstractControl } from '@angular/forms';
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
  erreurModification?: string;
  enregistre?: boolean;
  utilisateur?: Utilisateur;

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
  ) { }

  async ngOnInit(): Promise<void> {
    try {
      const utilisateur = await this.backend.getUtilisateur();
      this.utilisateur = utilisateur;
      this.identifiant?.setValue(utilisateur.identifiant);
      this.nom?.setValue(utilisateur.nom);
      this.email?.setValue(utilisateur.email);
      this.genre?.setValue(utilisateur.genre);
      this.prefNotifIdees?.setValue(utilisateur.prefNotifIdees);
    }
    catch (err) {
      // Les erreurs backend sont déjà affichées par AppComponent
    }
  }

  get identifiant() {
    return controlGet(this.formProfil, 'identifiant');
  }

  get nom() {
    return controlGet(this.formProfil, 'nom');
  }
  
  get email() {
    return controlGet(this.formProfil, 'email');
  }

  get genre() {
    return controlGet(this.formProfil, 'genre');
  }

  get prefNotifIdees() {
    return controlGet(this.formProfil, 'prefNotifIdees');
  }
  
  get mdp() {
    return controlGet(this.formProfil, 'mdp');
  }

  async modifie() {
    if (!this.utilisateur) throw new Error('Pas encore initialisé !');
    const { nom, email, genre, prefNotifIdees, mdp } = this.formProfil.value;
    try {
      Object.assign(this.utilisateur, { nom, email, genre, prefNotifIdees });
      if (mdp) Object.assign(this.utilisateur, { mdp });
      await this.backend.modifieUtilisateur(this.utilisateur);
      for (let champ of ['mdp', 'confirmeMdp']) controlGet(this.formProfil, (champ)).reset();
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
      names.every(name => Validators.required(controlGet(group, name)) === null)
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
    if (Validators.required(controlGet(group, name1)) !== null) return null;
    return controlGet(group, name1).value !== controlGet(group, name2).value ? { sameValueIfDefined: [name1, name2] } : null;
  }
}


function controlGet(group: AbstractControl, name: string): AbstractControl {
  const control = group.get(name);
  if (!control) throw new Error(`Contrôle '${name}' inconnu !`);
  return control;
}