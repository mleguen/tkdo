const path = require('path');

const commonConfig = {
  // Liste des modules auxquels les dépendances du projet font référence,
  // mais qui ne sont pas installés car jamais chargés pour l'usage que le projet fait de ces dépendances
  // et que webpack doit donc ignorer
  externals: [
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
        use: ['ts-loader']
      }
    ]
  },
  optimization: {
    minimize: false
  },
  output: {
    path: path.resolve(__dirname, 'dist')
  },
  resolve: {
    extensions: ['.ts', '.js'],
    modules: [path.resolve(__dirname, 'node_modules'), 'node_modules']
  },
  stats: {
    // Liste des modules pour lesquels webpack ne doit pas émettre d'avertissement
    // (généralement des modules faisant appel à du chargement dynamique)
    warningsFilter: [
      /node_modules\/app-root-path/,
      /node_modules\/parse5/,
      /node_modules\/typeorm/
    ]
  },
  target: 'node'
};

module.exports = [
  Object.assign({}, commonConfig, {
    name: 'ormconfig',
    entry: './ormconfig.ts',
    output: Object.assign({}, commonConfig.output, {
      filename: 'ormconfig.js',
      libraryTarget: 'commonjs'
    })
  }),
  Object.assign({}, commonConfig, {
    name: 'fixtures',
    entry: './bin/fixtures/index.ts',
    output: Object.assign({}, commonConfig.output, {
      filename: 'fixtures.js'
    })
  })
];
