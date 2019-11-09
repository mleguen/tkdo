.PHONY: build clean install uninstall

build: dist

clean: uninstall
	[ ! -d dist ] || rm -rf dist
	[ ! -d node_modules ] || rm -rf node_modules

IMAGE=tkdo/front
install: .docker-build

uninstall:
	[ ! -f .docker-build ] || (rm .docker-build && docker rmi $(IMAGE))

dist: $(shell find src -type f \! -name "*.spec.ts" \! -name ".git*") node_modules angular.json tsconfig.json tsconfig.app.json
	npm run build
	touch dist

node_modules: package*.json
	npm install
	touch node_modules

.docker-build: Dockerfile .dockerignore dist
	docker build -t $(IMAGE) .
	touch .docker-build
