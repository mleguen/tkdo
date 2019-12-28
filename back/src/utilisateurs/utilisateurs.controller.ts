import { Controller, UseGuards, Get } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { pick } from 'lodash';

import { Droit, IUtilisateur } from '../../../shared/domaine';
import { DroitNecessaire, DroitNecessaireGuard } from '../auth';
import { UtilisateursService } from './utilisateurs.service';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitNecessaireGuard)
export class UtilisateursController {

  constructor(
    private readonly utilisateursService: UtilisateursService
  ) { }

  @Get()
  @DroitNecessaire(Droit.ConsultationUtilisateurs)
  async getUtilisateurs(): Promise<IUtilisateur[]> {
    const utilisateurs = await this.utilisateursService.readUtilisateurs();
    return utilisateurs.map(utilisateur => pick(utilisateur, 'id', 'nom', 'login'));
  }
}
