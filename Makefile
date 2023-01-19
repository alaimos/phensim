VERSION ?= v1.0.0
REGISTRY ?= ghcr.io/alaimos/phensim-

# Commands
all: clean server
server: server-build server-push 

clean:
	docker builder prune --all --force

server-build:
	DOCKER_BUILDKIT=1 docker build . --target phensim_cli -t ${REGISTRY}cli:${VERSION}
	DOCKER_BUILDKIT=1 docker build . --target phensim_cron -t ${REGISTRY}cron:${VERSION}
	DOCKER_BUILDKIT=1 docker build . --target phensim_fpm_server -t ${REGISTRY}fpm_server:${VERSION}
	DOCKER_BUILDKIT=1 docker build . --target phensim_web_server -t ${REGISTRY}web_server:${VERSION}

server-push:
	DOCKER_BUILDKIT=1 docker push ${REGISTRY}cli:${VERSION}
	DOCKER_BUILDKIT=1 docker push ${REGISTRY}cron:${VERSION}
	DOCKER_BUILDKIT=1 docker push ${REGISTRY}fpm_server:${VERSION}
	DOCKER_BUILDKIT=1 docker push ${REGISTRY}web_server:${VERSION}
