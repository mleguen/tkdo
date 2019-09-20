.PHONY: build

build: .build

.build: Dockerfile dist  $(wildcard .env)
	touch .build

dist: $(shell find src)
	npm run build
