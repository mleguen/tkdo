import { Controller, Get, Req, Res, UseGuards, Post, Body } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { Request, Response } from 'express';
import { AuthGuard } from '@nestjs/passport';
import { SamlStrategy } from './saml/saml.strategy';

@Controller()
export class AppController {
  constructor(private jwtService: JwtService, private samlStrategy: SamlStrategy) {}

  @UseGuards(AuthGuard('saml'))
  @Post('/acs')
  async postAssertionConsumerService(@Req() req: Request, @Res() res: Response, @Body('RelayState') relayState?: string) {
    if(!relayState) throw new Error('RelayState manquant');
    let jwt = await this.jwtService.signAsync(req.user);
    res.redirect([relayState, jwt].join('#'));
  }

  @UseGuards(AuthGuard('saml'))
  @Get('/login')
  getLogin() {}

  @Get('/metadata')
  getMetadata(): string {
    return this.samlStrategy.generateServiceProviderMetadata();
  }
}
