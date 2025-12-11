# Nginx Configuration with Environment Variables

This document explains how the nginx configuration dynamically uses environment variables to set the server name, keeping your repository free of hardcoded domain names.

## Overview

The nginx service in `docker-stack.yml` uses a template-based configuration system that:
- Keeps `localhost` as the default in the repository
- Dynamically sets the actual domain from `APP_URL` environment variable at runtime
- Prevents committing sensitive domain information to git

## How It Works

### 1. Template File

The actual nginx configuration is stored as a template:
- **File**: `docker/nginx/sites/laravel.prod.conf.template`
- **Placeholders**: Uses `${SERVER_NAME}` instead of hardcoded domains

```nginx
server {
    listen 80;
    server_name ${SERVER_NAME};  # Will be replaced at runtime
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    http2 on;
    server_name ${SERVER_NAME};  # Will be replaced at runtime
    # ... rest of config
}
```

### 2. Entrypoint Script

The entrypoint script (`docker/nginx/docker-entrypoint.sh`) processes the template:

```bash
#!/bin/sh
# Extract domain from APP_URL if SERVER_NAME is not set
if [ -z "$SERVER_NAME" ] && [ -n "$APP_URL" ]; then
    SERVER_NAME=$(echo "$APP_URL" | sed -e 's|^[^/]*//||' -e 's|/.*$||')
    export SERVER_NAME
fi

# Default to localhost if still not set
if [ -z "$SERVER_NAME" ]; then
    export SERVER_NAME="localhost"
fi

# Process template and create actual config
envsubst '${SERVER_NAME}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Execute nginx
exec "$@"
```

**What it does:**
1. Checks if `SERVER_NAME` is already set
2. If not, extracts domain from `APP_URL` (e.g., `https://example.com` → `example.com`)
3. Falls back to `localhost` if neither is set
4. Uses `envsubst` to replace `${SERVER_NAME}` in the template
5. Generates the final nginx config file
6. Starts nginx

### 3. Docker Stack Configuration

The `docker-stack.yml` mounts the template and entrypoint:

```yaml
nginx:
  image: nginx:1.29.1-alpine
  entrypoint: ["/docker-entrypoint.sh"]
  command: ["nginx", "-g", "daemon off;"]
  environment:
    APP_URL: ${APP_URL:-https://localhost}  # From .env or defaults to localhost
  volumes:
    - ./docker/nginx/sites/laravel.prod.conf.template:/etc/nginx/conf.d/default.conf.template:ro
    - ./docker/nginx/docker-entrypoint.sh:/docker-entrypoint.sh:ro
```

**How it works:**
- `entrypoint`: Runs our custom script before nginx
- `environment`: Passes `APP_URL` from your `.env` file to the container
- `volumes`: Mounts both the template and entrypoint script

## Usage

### Development (localhost)

In your `.env` file:
```bash
APP_URL=http://localhost
```

Result: nginx uses `server_name localhost;`

### Production (your domain)

In your `.env` file:
```bash
APP_URL=https://yourdomain.com
```

Result: nginx uses `server_name yourdomain.com;`

### Multiple Domains

If you need multiple domains (e.g., with and without www), set `SERVER_NAME` directly:

```yaml
# docker-stack.yml
nginx:
  environment:
    SERVER_NAME: "yourdomain.com www.yourdomain.com"
```

Or create a custom entrypoint that extracts both from `APP_URL`.

## Benefits

### 1. Security
- No hardcoded domains in git repository
- Production domains stay in `.env` (which is gitignored)
- Different environments can use different domains

### 2. Flexibility
- Easy to deploy to different domains without code changes
- Supports staging, production, and development environments
- Can override with `SERVER_NAME` if needed

### 3. Maintainability
- Single source of truth for domain (`APP_URL`)
- No need to edit nginx configs manually
- Template stays generic and reusable

## File Structure

```
RentCar/
├── docker/
│   └── nginx/
│       ├── docker-entrypoint.sh          # Processes template at runtime
│       ├── nginx.conf                     # Main nginx config (no changes)
│       └── sites/
│           ├── laravel.prod.conf          # Static config (kept for reference)
│           └── laravel.prod.conf.template # Template with ${SERVER_NAME}
├── docker-stack.yml                       # Uses template and entrypoint
└── .env                                   # Contains APP_URL
```

## Troubleshooting

### Check What Domain Is Being Used

View nginx logs during startup:
```bash
docker service logs laravel-app_nginx
```

You should see:
```
Configuring nginx for domain: yourdomain.com
```

### Verify Generated Config

Inspect the running container:
```bash
# Get container ID
docker ps | grep nginx

# View generated config
docker exec <container_id> cat /etc/nginx/conf.d/default.conf
```

The `server_name` should show your actual domain, not `${SERVER_NAME}`.

### Common Issues

**Issue**: nginx shows `server_name localhost;` in production

**Solution**: Check that `APP_URL` is set correctly in `.env`:
```bash
grep APP_URL .env
```

---

**Issue**: Template variable not replaced

**Solution**: Ensure entrypoint has execute permissions:
```bash
chmod +x docker/nginx/docker-entrypoint.sh
```

---

**Issue**: nginx fails to start

**Solution**: Test the template locally:
```bash
export SERVER_NAME="yourdomain.com"
envsubst '${SERVER_NAME}' < docker/nginx/sites/laravel.prod.conf.template
```

## Advanced Usage

### Custom Domain Extraction

If you need more complex domain handling, modify `docker-entrypoint.sh`:

```bash
# Example: Extract first domain from comma-separated list
if [ -z "$SERVER_NAME" ] && [ -n "$ALLOWED_DOMAINS" ]; then
    SERVER_NAME=$(echo "$ALLOWED_DOMAINS" | cut -d',' -f1)
    export SERVER_NAME
fi
```

### Environment-Specific Templates

Create different templates for different environments:
- `laravel.dev.conf.template` - Development
- `laravel.staging.conf.template` - Staging
- `laravel.prod.conf.template` - Production

Then use environment variables to select which template to use.

### Additional Placeholders

Add more environment variables to the template:

```nginx
# In template file
server {
    listen 443 ssl;
    server_name ${SERVER_NAME};

    # Custom upload size from environment
    client_max_body_size ${MAX_UPLOAD_SIZE:-10M};
}
```

Update entrypoint:
```bash
envsubst '${SERVER_NAME} ${MAX_UPLOAD_SIZE}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf
```

## Summary

This template-based approach ensures that:
- ✅ No domain hardcoded in git
- ✅ Single source of truth (`APP_URL` in `.env`)
- ✅ Easy deployment across different environments
- ✅ Nginx automatically uses the correct domain
- ✅ Template stays generic and shareable

No manual nginx config editing required!
