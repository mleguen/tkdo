import { environmentDev, extendsEnv } from './environment.dev';

export const environment = extendsEnv(environmentDev, {
  authSpLoginUrl: '/auth-sp/login',
  backUrl: '/back',
  production: true,
  titre: 'TKDO'
});
