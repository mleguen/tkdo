.PHONY: build

build: .build

.build: Dockerfile dist  $(wildcard .env)
	touch .build

dist: $(shell find src) package.json webpack.config.js tsconfig.build.json tsconfig.json
	npm run build
