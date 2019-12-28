import { Injectable } from '@angular/core';

import { IUtilisateur } from '../../../../shared/domaine';
import { BackendService } from '../backend.service';

const URL_UTILISATEURS = '/utilisateurs';

@Injectable()
export class UtilisateursService {

  constructor(
    private backendService: BackendService
  ) { }
  
  getUtilisateurs() {
    return this.backendService.get<IUtilisateur[]>(URL_UTILISATEURS);
  }
}
