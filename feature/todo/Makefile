.PHONY: build add-new-features remove-missing-features

build: add-new-features remove-missing-features

add-new-features:
	for f in *.feature; do grep -q "\./$$f" README.md || echo "- [$$f](./$$f)" >> README.md; done

remove-missing-features:
	for f in $$(grep -o "\./[^/]*\.feature" README.md); do [ -f "$$f" ] || (grep -v "$$f" README.md > README.tmp.md && mv -f README.tmp.md README.md); done
