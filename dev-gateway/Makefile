.PHONY: build

build: .last-contents-change

.last-contents-change: Dockerfile httpd.conf server.crt server.key
	touch .last-contents-change

# génère les certificats de développement
server.crt server.key:
	openssl req -new -x509 -nodes -out server.crt -keyout server.key
