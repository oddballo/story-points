#!/bin/bash

DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

if [ -z "$UID" ]; then
    UID=$(id -u)
fi
export UID 
export GID=$(id -g)

#
#   Build
#
docker run \
    -u "${UID}:${GID}" \
    -v "$DIR:/mnt" \
    -w "/mnt" \
    --entrypoint "npm" \
    node:19.8.1-alpine3.16 \
    install
    
