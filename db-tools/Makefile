.PHONY: build clean install uninstall

build: .docker-build

clean:
	[ ! -d dist ] || rm -rf dist
	[ ! -d node_modules ] || rm -rf node_modules

IMAGE=tkdo-db-tools
install: .docker-build

uninstall:
	[ ! -f .docker-build ] || (rm .docker-build && docker rmi $(IMAGE))

dist: $(shell find bin -type f \! -name "*.spec.ts") ormconfig.ts node_modules tsconfig.json webpack.config.js
	npm run build

node_modules: package*.json
	npm install
	touch node_modules

.docker-build: Dockerfile .dockerignore dist
	docker build --network host -t $(IMAGE) .
	touch .docker-build
