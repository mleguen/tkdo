.PHONY: build add-new-features remove-missing-features

build: add-new-features remove-missing-features

CHANGELOG=../../CHANGELOG.md
add-new-features:
	for f in *.feature; do grep -q "/$$f" $(CHANGELOG) || echo "- [$$f](./feature/done/$$f)" >> $(CHANGELOG); done

remove-missing-features:
	for f in $$(grep -o "/[^/]*\.feature" $(CHANGELOG)); do [ -f ".$$f" ] || (grep -v "$$f" $(CHANGELOG) > CHANGELOG.tmp.md && mv -f CHANGELOG.tmp.md $(CHANGELOG)); done
