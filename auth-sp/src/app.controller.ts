import { Controller, Get, Req, Res, UseGuards, Post, Body } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { Request, Response } from 'express';

import { IProfile } from '../../shared/domaine';
import { AuthSamlStrategy } from './auth/auth-saml.strategy';
import { AppService } from './app.service';

@Controller()
export class AppController {
  constructor(private appService: AppService, private samlStrategy: AuthSamlStrategy) {}

  @UseGuards(AuthGuard('saml'))
  @Post('/acs')
  postAssertionConsumerService(@Req() req: Request, @Res() res: Response, @Body('RelayState') relayState?: string) {
    return this.appService.redirectToRelayStateWithJwt(res, relayState, req.user as IProfile);
  }

  @UseGuards(AuthGuard('saml'))
  @Get('/login')
  getLogin() {}

  @Get('/metadata')
  getMetadata(): string {
    return this.samlStrategy.generateServiceProviderMetadata();
  }
}
