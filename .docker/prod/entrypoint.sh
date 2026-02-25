#!/bin/sh
set -e

cleanup() {
    kill -TERM "$NGINX_PID" 2>/dev/null
    kill -TERM "$FPM_PID" 2>/dev/null
    wait "$NGINX_PID" "$FPM_PID" 2>/dev/null
}

trap cleanup TERM INT QUIT

php-fpm &
FPM_PID=$!

nginx -g 'daemon off;' &
NGINX_PID=$!

wait -n
cleanup
