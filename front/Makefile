.PHONY: build

build: .last-contents-change

.last-contents-change: Dockerfile dist
	touch .last-contents-change

dist: $(exec find src)
	npm build
