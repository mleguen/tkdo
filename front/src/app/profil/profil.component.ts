import { CommonModule } from '@angular/common';
import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { Validators, FormBuilder, ValidatorFn, AbstractControl, ReactiveFormsModule } from '@angular/forms';

import { BackendService, Genre, PrefNotifIdees, Utilisateur } from '../backend.service';

@Component({
  selector: 'app-profil',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
  ],
  templateUrl: './profil.component.html',
  styleUrl: './profil.component.scss'
})
export class ProfilComponent implements OnInit {

  Genre = Genre;
  PrefNotifIdees = PrefNotifIdees;

  formProfil = this.formBuilder.group(
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
  enregistre = false;
  utilisateur?: Utilisateur;

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly backend: BackendService,
    private readonly changeDetector: ChangeDetectorRef,
  ) { }

  ngOnInit(): void {
    this.backend.utilisateurConnecte$.subscribe({
      next: utilisateur => {
        if (utilisateur) {
          this.utilisateur = utilisateur;
          this.identifiant.setValue(utilisateur.identifiant);
          this.nom.setValue(utilisateur.nom);
          this.email.setValue(utilisateur.email);
          this.genre.setValue(utilisateur.genre);
          this.prefNotifIdees.setValue(utilisateur.prefNotifIdees);
        }
      },
      // Les erreurs backend sont déjà affichées par AppComponent
      error: () => { }
    });
  }

  get identifiant() {
    return getChildControl(this.formProfil, 'identifiant');
  }

  get nom() {
    return getChildControl(this.formProfil, 'nom');
  }

  get email() {
    return getChildControl(this.formProfil, 'email');
  }

  get genre() {
    return getChildControl(this.formProfil, 'genre');
  }

  get prefNotifIdees() {
    return getChildControl(this.formProfil, 'prefNotifIdees');
  }

  get mdp() {
    return getChildControl(this.formProfil, 'mdp');
  }

  async modifie() {
    const { nom, email, genre, prefNotifIdees, mdp } = this.formProfil.value;
    try {
      if (!this.utilisateur) throw Error('utilisateur pas encore initialisé')
      Object.assign(this.utilisateur, { nom, email, genre, prefNotifIdees });
      if (mdp) Object.assign(this.utilisateur, { mdp });
      await this.backend.modifieUtilisateur(this.utilisateur);
      for (const champ of ['mdp', 'confirmeMdp']) getChildControl(this.formProfil, champ).reset();
      this.erreurModification = undefined;
      this.enregistre = true;
    }
    catch (err) {
      this.erreurModification = (err instanceof Error ? err.message : undefined) || "enregistrement impossible";
      this.enregistre = false;
    }
    finally {
      this.changeDetector.detectChanges();
      document.getElementsByClassName('feedback').item(0)!.scrollIntoView();
    }
  }
}

function getChildControl(group: AbstractControl, path: string) {
  const child = group.get(path);
  if (!child) throw new Error(`le contrôle '${path}' n'existe pas dans le groupe`);
  return child;
}

/**
 * Validate that at least one list of field names
 * have all matching fields in the group non-empty
 */
function requireOne(...lists: string[][]): ValidatorFn {
  return group => {
    return lists.some(names =>
      names.every(name => Validators.required(getChildControl(group, name)) === null)
    )
      ? null
      : { requireOne: true };
  }
}

/**
 * Validate 2 fields either are empty or contain the same value
 */
function sameValueIfDefined(name1: string, name2: string): ValidatorFn {
  return group => {
    const child1 = getChildControl(group, name1);
    if (Validators.required(child1) !== null) return null;
    return child1.value !== getChildControl(group, name2).value ? { sameValueIfDefined: [name1, name2] } : null;
  }
}
