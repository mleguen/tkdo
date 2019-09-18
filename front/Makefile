.PHONY: build

build: .built

.built: Dockerfile dist
	touch .built

dist: $(exec find src)
	ng build
