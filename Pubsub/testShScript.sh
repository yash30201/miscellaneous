#!/bin/bash

echo 'Usage: ./StartStandardPhpDocker.sh <[Optional]DOCKER_IMAGE_NAME> \
    <[Optional]DOCKER_CONTAINER_NAME>'

dockerImage=${DOCKER_IMAGE_NAME}
if [ -z "$1" ]
    then
        echo "Docker image not specified, using ${DOCKER_IMAGE_NAME}"
    else
        dockerImage=$1
fi

dockerContainer=${DOCKER_CONTAINER_NAME}
if [ -z "$2" ]
    then
        echo "Docker container not specified, using ${DOCKER_CONTAINER_NAME}"
    else
        dockerContainer=$2
fi

docker run \
-ti --rm --privileged \
--env-file $DOCKER_ENV_FILE_PATH \
--add-host=host.docker.internal:host-gateway \
--name $DOCKER_CONTAINER_NAME \
$DOCKER_IMAGE_NAME \
bash
