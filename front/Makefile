build: dist

dist: $(shell find src -type f \! -name "*.spec.ts" \! -name ".git*") angular.json package*.json tsconfig.json tsconfig.app.json
	npm run build -- --prod

clean:
	[ -d dist ] && rm -rf dist
	[ -d node_modules ] && rm-rf node_modules

# Cibles qui ne doivent jamais être considérées comme à jour
.PHONY: build clean
