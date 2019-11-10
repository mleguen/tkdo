.PHONY: build clean install uninstall

build: dist

IMAGE=tkdo/back
clean: uninstall
	[ ! -d dist ] || rm -rf dist
	[ ! -d node_modules ] || rm -rf node_modules

install: .docker-build

uninstall:
	[ ! -f .docker-build ] || (rm .docker-build && docker rmi $(IMAGE))

dist: $(shell find ../shared/domaine/lib ../shared/schema/lib src -type f \! -name "*.spec.ts" ) node_modules tsconfig*.json
	npm run build

node_modules: package*.json
	npm install
	touch node_modules

.docker-build: Dockerfile .dockerignore dist
	docker build --network host -t $(IMAGE) .
	touch .docker-build
