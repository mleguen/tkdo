.PHONY: build clean install uninstall

build: dist/main.js

IMAGE=tkdo-back
clean:
	[ ! -d dist ] || rm -rf dist
	[ ! -d node_modules ] || rm -rf node_modules

install: .docker-build

uninstall:
	[ ! -f .docker-build ] || (rm .docker-build && docker rmi $(IMAGE))

dist/main.js: $(shell find src -type f \! -name "*.spec.ts" ) node_modules tsconfig*.json webpack.config.js
	npm run build

node_modules: package*.json
	npm install
	touch node_modules

.docker-build: Dockerfile .dockerignore dist/main.js
	docker build -t $(IMAGE) .
	touch .docker-build
