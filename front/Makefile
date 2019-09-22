.PHONY: build

build: .build

.build: Dockerfile dist
	touch .build

dist: $(shell find src)
	npm run build --prod
