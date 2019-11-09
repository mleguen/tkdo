import { Controller, Get, Res, Query } from '@nestjs/common';
import { Response } from 'express';
import { AppService } from './app.service';
import { IIDPProfile } from '../../shared/domaine';

@Controller()
export class AppDevController {
  constructor(private appService: AppService) {}

  @Get('/login')
  getLogin(@Res() res: Response, @Query('RelayState') relayState?: string) {
    return this.appService.redirectToRelayStateWithJwt(res, relayState, JSON.parse(process.env.TKDO_IDP_PROFILE_DEV) as IIDPProfile);
  }
}
