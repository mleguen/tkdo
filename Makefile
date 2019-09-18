# A chaque build, vérifie que front et .built sont à jour
build: front .built

# Ne considère jamais front comme à jour...
.PHONY: front

# ... pour y forcer un make
front:
	$(MAKE) -C $@

# .built matérialise la dernière exécution de docker-compose build
# qui n'est exécutée que si la conf, le front ou les certificats ont changé
.built: docker-compose.yml front/.built certs/front.crt certs/front.key
	docker-compose build
	touch .built

# génère des certificats de développement pour le front s'ils ne sont pas fournis
certs/front.crt certs/front.key:
	[ ! -d certs ] && mkdir certs
	openssl req -new -x509 -nodes -out certs/front.crt -keyout certs/front.key; done

up: build
	docker-compose up
