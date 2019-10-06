import { Controller, Get, Res, Query } from '@nestjs/common';
import { Response } from 'express';
import { Utilisateur } from '../../domaine';
import { AppService } from './app.service';

const UTILISATEUR_DEV_PAR_DEFAUT: Utilisateur = process.env.TKDO_UTILISATEUR_DEV_PAR_DEFAUT
  ? JSON.parse(process.env.TKDO_UTILISATEUR_DEV_PAR_DEFAUT)
  : {
    id: 0,
    nom: 'Alice',
    roles: [
      'TKDO_PARTICIPANT'
    ]
  };

@Controller()
export class AppDevController {
  constructor(private appService: AppService) {}

  @Get('/login')
  getLogin(@Res() res: Response, @Query('RelayState') relayState?: string) {
    return this.appService.redirectToRelayStateWithJwt(res, relayState, UTILISATEUR_DEV_PAR_DEFAUT);
  }
}
