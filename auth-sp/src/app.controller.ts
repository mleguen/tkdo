import { Controller, Get, Req, Res, UseGuards, Post, Body } from '@nestjs/common';
import { Request, Response } from 'express';
import { AuthGuard } from '@nestjs/passport';
import { IUtilisateur } from '../../shared/auth';
import { SamlStrategy } from './saml/saml.strategy';
import { AppService } from './app.service';

@Controller()
export class AppController {
  constructor(private appService: AppService, private samlStrategy: SamlStrategy) {}

  @UseGuards(AuthGuard('saml'))
  @Post('/acs')
  postAssertionConsumerService(@Req() req: Request, @Res() res: Response, @Body('RelayState') relayState?: string) {
    return this.appService.redirectToRelayStateWithJwt(res, relayState, req.user as IUtilisateur);
  }

  @UseGuards(AuthGuard('saml'))
  @Get('/login')
  getLogin() {}

  @Get('/metadata')
  getMetadata(): string {
    return this.samlStrategy.generateServiceProviderMetadata();
  }
}
