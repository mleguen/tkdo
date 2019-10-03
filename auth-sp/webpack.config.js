const path = require('path');

// Conserve comme externals les dépendances listées dans package.json
// (les modules actuellement incompatibles avec webpack, ou chargés dynamiquement par NestJS)
let externals = {};
for (let mod of Object.keys(require('./package.json').dependencies)) {
  externals[mod] = `commonjs ${mod}`;
}

module.exports = {
  entry: './src/main.ts',
  externals: externals,
  mode: 'development',
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
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'auth-sp.js'
  },
  resolve: {
    extensions: ['.ts', '.js'],
  },
  target: 'node'
}
