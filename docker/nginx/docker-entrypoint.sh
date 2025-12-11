#!/bin/sh
set -e

# Extract domain from APP_URL if SERVER_NAME is not set
if [ -z "$SERVER_NAME" ] && [ -n "$APP_URL" ]; then
    # Extract domain from APP_URL (removes http://, https://, and trailing /)
    SERVER_NAME=$(echo "$APP_URL" | sed -e 's|^[^/]*//||' -e 's|/.*$||')
    export SERVER_NAME
fi

# Default to localhost if still not set
if [ -z "$SERVER_NAME" ]; then
    export SERVER_NAME="localhost"
fi

echo "Configuring nginx for domain: $SERVER_NAME"

# Process template and create actual config
envsubst '${SERVER_NAME}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Test nginx configuration
nginx -t

# Execute the main command (nginx)
exec "$@"
