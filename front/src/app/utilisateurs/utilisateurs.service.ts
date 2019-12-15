import { Injectable } from '@angular/core';

import { UtilisateurResumeDTO } from '../../../../back/src/utilisateurs/dto/utilisateur-resume.dto';
import { BackendService } from '../backend.service';

const URL_UTILISATEURS = '/utilisateurs';

@Injectable()
export class UtilisateursService {

  constructor(
    private backendService: BackendService
  ) { }
  
  getUtilisateurs() {
    return this.backendService.get<UtilisateurResumeDTO[]>(URL_UTILISATEURS);
  }
}
