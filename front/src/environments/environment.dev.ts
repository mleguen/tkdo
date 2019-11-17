export const environmentDev = {
  authTokenLocalStorageKey: 'authToken',
  authSpLoginUrl: 'http://127.0.0.1:3001/login',
  backUrl: 'http://127.0.0.1:3000',
  locale: 'fr',
  production: false,
  titre: 'TKDO (développement)'
};

export function extendsEnv<TEnvironment>(defaultEnv: TEnvironment, specificEnv: Partial<TEnvironment>): TEnvironment {
  return Object.assign({}, defaultEnv, specificEnv);
}
