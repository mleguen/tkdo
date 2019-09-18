PROD_MODULES = front
DEV_MODULES = dev-gateway
ALL_MODULES = $(PROD_MODULES) $(DEV_MODULES)

build: $(PROD_MODULES) .last-prod-contents-change

start: build $(DEV_MODULES) .last-docker-compose
	(sleep 5 && xdg-open https://localhost)&
	docker-compose up

# Ne considère jamais aucun des modules comme à jour...
.PHONY: $(ALL_MODULES)

# ... pour y forcer un make
$(ALL_MODULES):
	$(MAKE) -C $@

.last-docker-compose: .last-prod-contents-change docker-compose.override.yml $(addsuffix /.last-contents-change,$(DEV_MODULES))
	docker-compose build
	touch .last-docker-compose

.last-prod-contents-change: docker-compose.yml $(addsuffix /.last-contents-change,$(PROD_MODULES))
	touch .last-prod-contents-change
