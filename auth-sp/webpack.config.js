const path = require('path');

module.exports = {
  entry: './src/main.ts',
  // Liste des modules auxquels les dépendances du projet font référence,
  // mais qui ne sont pas installés car jamais chargés pour l'usage que le projet fait de ces dépendances
  // et que webpack doit donc ignorer
  externals: [
    '@nestjs/microservices',
    'cache-manager',
    'class-transformer',
    'class-validator',
    'ioredis',
    'mongodb',
    'mssql',
    'mysql2',
    'oracledb',
    'pg',
    'pg-native',
    'pg-query-stream',
    'react-native-sqlite-storage',
    'redis',
    'sql.js',
    'sqlite3'
  ].reduce((externals, mod) => Object.assign(externals, { [mod]: `commonjs ${mod}` }), {}),
  mode: 'production',
  module: {
    rules: [
      {
        test: /\.ts$/,
        use: [
          {
            loader: 'ts-loader',
            options: {
              configFile: 'tsconfig.build.json'
            }
          }
        ]
      }
    ]
  },
  optimization: {
    minimize: false
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'main.js'
  },
  resolve: {
    extensions: ['.ts', '.js'],
    // Force webpack à prendre en priorité les modules dans le node_modules à la racine de ce répertoire
    // pour limiter les risques de se retrouver avec 2 versions d'un même module dans le pack
    // (celle de ce répertoire, et celle d'un autre répertoire comme schema).
    // Cela peut en effet entraîner des effets de bords, comme par exemple "A instanceof B" false
    // parce que A est une instance de B, avec B importé d'une version d'un module
    // et le module qui fait le instanceof importe B de l'autre version du module
    modules: [path.resolve(__dirname, 'node_modules'), 'node_modules']
  },
  stats: {
    // Liste des modules pour lesquels webpack ne doit pas émettre d'avertissement
    // (généralement des modules faisant appel à du chargement dynamique)
    warningsFilter: [
      /node_modules\/@nestjs\/common/,
      /node_modules\/@nestjs\/core/,
      /node_modules\/app-root-path/,
      /node_modules\/ejs/,
      /node_modules\/optional/,
      /node_modules\/parse5/,
      /node_modules\/typeorm/
    ]
  },
  target: 'node'
}
