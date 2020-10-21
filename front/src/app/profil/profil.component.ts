import { Component, OnInit } from '@angular/core';
import { Validators, FormBuilder, ValidatorFn } from '@angular/forms';
import { BackendService, Utilisateur } from '../backend.service';

@Component({
  selector: 'app-profil',
  templateUrl: './profil.component.html',
  styleUrls: ['./profil.component.scss']
})
export class ProfilComponent implements OnInit {

  formProfil = this.fb.group(
    {
      'identifiant': [''],
      'nom': ['', [Validators.minLength(3)]],
      'mdp': ['', [Validators.minLength(8)]],
      'confirmeMdp': [''],
    },
    {
      validators: [
        requireOne('nom', 'mdp'),
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
  
  get mdp() {
    return this.formProfil.get('mdp');
  }

  async modifie() {
    const { nom, mdp } = this.formProfil.value;
    try {
      await this.backend.modifieUtilisateur(Object.assign(this.utilisateur, { nom, mdp }));
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

function requireOne (...names: string[]): ValidatorFn {
  return group => {
    return names.every(name => Validators.required(group.get(name)) !== null)
      ? { requireOne: names }
      : null;
  }
}

function sameValueIfDefined (name1: string, name2: string): ValidatorFn {
  return group => {
    if (Validators.required(group.get(name1)) !== null) return null;
    return group.get(name1).value !== group.get(name2).value ? { sameValueIfDefined: [name1, name2] } : null;
  }
}