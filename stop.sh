#!/bin/bash

if [ -z "$UID" ]; then
    UID=$(id -u)
fi
export UID
export GID=$(id -g)

docker compose down
