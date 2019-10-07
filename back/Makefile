build: dist/main.js

dist/main.js: $(shell find src -type f \! -name "*.spec.ts" ) package*.json tsconfig*.json webpack.config.js
	npm install
	npm run build

clean:
	[ -d dist ] && rm -rf dist
	[ -d node_modules ] && rm-rf node_modules

# Cibles qui ne doivent jamais être considérées comme à jour
.PHONY: build clean
