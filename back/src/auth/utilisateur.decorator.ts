import { createParamDecorator } from '@nestjs/common';

export const Utilisateur = createParamDecorator((key = 'user', req) => req[key].utilisateur);
