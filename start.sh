#!/bin/bash

if [ -z "$UID" ]; then
    UID=$(id -u)
fi
export UID
export GID=$(id -g)

DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

mkdir -p "$DIR/data"

docker compose up -d
