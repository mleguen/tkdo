import { Component, OnInit } from '@angular/core';
import { Validators, FormBuilder, ValidatorFn } from '@angular/forms';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-profil',
  templateUrl: './profil.component.html',
  styleUrls: ['./profil.component.scss']
})
export class ProfilComponent implements OnInit {

  formProfil = this.fb.group(
    {
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

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
  ) { }

  async ngOnInit() {
    this.backend.getProfil$().subscribe(({ nom }) => {
      this.nom.setValue(nom);
    });
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
      await this.backend.modifieProfil(nom, mdp);
      for (let champ of ['mdp', 'confirmeMdp']) this.formProfil.get(champ).reset();
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
