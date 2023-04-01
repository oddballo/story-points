#!/bin/bash

set -eou pipefail

DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

cd "$DIR"

cd components/longpoll
./build.sh
cd "$DIR"

cd components/nginx
./build.sh
cd "$DIR"

cd components/php
./build.sh
cd "$DIR"

./stop.sh
./start.sh
