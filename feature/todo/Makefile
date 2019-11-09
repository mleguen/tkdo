.PHONY: build add-new-features remove-missing-features

build: add-new-features remove-missing-features

ROADMAP=../../ROADMAP.md
add-new-features:
	for f in *.feature; do grep -q "/$$f" $(ROADMAP) || echo "- [$$f](./feature/todo/$$f)" >> $(ROADMAP); done

remove-missing-features:
	for f in $$(grep -o "/[^/]*\.feature" $(ROADMAP)); do [ -f ".$$f" ] || (grep -v "$$f" $(ROADMAP) > ROADMAP.tmp.md && mv -f ROADMAP.tmp.md $(ROADMAP)); done
