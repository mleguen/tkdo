import { Controller, Get, Res, Query } from '@nestjs/common';
import { Response } from 'express';

import { IIDPProfile } from './auth/types/idp-profile';
import { AuthService } from './auth/auth.service';
import { AppService } from './app.service';

@Controller()
export class AppDevController {
  constructor(
    private appService: AppService,
    private authService: AuthService
  ) {}

  @Get('/login')
  async getLogin(@Res() res: Response, @Query('RelayState') relayState?: string) {
    const profile = await this.authService.createProfile(JSON.parse(process.env.TKDO_IDP_PROFILE_DEV) as IIDPProfile);
    return this.appService.redirectToRelayStateWithJwt(res, relayState, profile);
  }
}
