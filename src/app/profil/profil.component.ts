import { Component } from '@angular/core';
import { Validators, FormBuilder, ValidatorFn } from '@angular/forms';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-profil',
  templateUrl: './profil.component.html',
  styleUrls: ['./profil.component.scss']
})
export class ProfilComponent {

  formProfil = this.fb.group(
    {
      'mdp': ['', [Validators.required, Validators.minLength(8)]],
      'confirmeMdp': [''],
    },
    { validators: [
      requireOne('mdp'),
      sameValueIfDefined('mdp', 'confirmeMdp'),
    ]},
  );

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
  ) { }

  get mdp() {
    return this.formProfil?.get('mdp');
  }
  
  get confirmeMdp() {
    return this.formProfil?.get('confirmeMdp');
  }

  async modifie() {
    await this.backend.modifieProfil(this.mdp.value);
    this.formProfil.reset();
  }
}

function requireOne (...names: string[]): ValidatorFn {
  return group => {
    return names.some(name => Validators.required(group.get(name)) !== null)
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
