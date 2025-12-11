# Laravel RentCar - Docker Setup

This guide covers the complete setup for running Laravel RentCar with Docker in both development and production environments.

## Table of Contents
- [Development Setup (docker-compose.yml)](#development-setup)
- [Production Setup (docker-stack.yml)](#production-setup)

## Additional Documentation
- **[SSL Setup with Cloudflare](SSL_SETUP_CLOUDFLARE.md)** - Complete guide for SSL certificates
- **[Nginx Configuration](NGINX_CONFIG.md)** - How environment variables configure nginx automatically

---

## Development Setup

### For Developers - Using docker-compose.yml

This setup is optimized for local development with hot-reloading, Composer access, and no SSL complications.

#### Prerequisites
- Docker
- Docker Compose
- Git

#### 1. Clone Repository
```bash
git clone https://github.com/olek1305/RentCar.git RentCar
cd RentCar
```

#### 2. Environment Configuration
```bash
cp .env.example .env
```

Edit `.env` and set:
```
APP_ENV=local
APP_DEBUG=true
DB_HOST=db
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password
REDIS_HOST=valkey
```

#### 3. Start Development Environment
```bash
docker compose up -d
```

This will automatically:
- Build the development image with Composer and Node.js installed
- Install PHP dependencies via Composer
- Install NPM dependencies
- Build frontend assets with Vite
- Start Vite dev server with hot-reloading on port 5173
- Mount your code directory for live editing
- Start services without SSL (HTTP only on port 80)
- Generate APP_KEY if missing
- Run database migrations
- Create vendor and node_modules volumes

**Note:** The first startup may take a few minutes while Composer and NPM install dependencies.

#### 4. Access Your Application
- **Application**: http://localhost
- **Vite Dev Server**: http://localhost:5173 (for HMR - Hot Module Replacement)
- **No HTTPS required** - Development runs on HTTP only

**Frontend Development:**
The Vite service runs `npm run dev` automatically with hot-reloading enabled. Any changes you make to your frontend files (CSS, JS, Vue, etc.) will automatically reload in the browser.

#### 5. Create Admin Account (Optional)
```bash
docker compose exec app php artisan app:create-account
```

#### 6. Useful Development Commands
```bash
# View logs
docker compose logs -f app
docker compose logs -f vite  # View Vite dev server logs

# Run artisan commands
docker compose exec app php artisan [command]

# Install Composer packages
docker compose exec app composer require [package]

# Install NPM packages
docker compose exec vite npm install [package]

# Run tests
docker compose exec app php artisan test

# Rebuild frontend assets manually
docker compose exec vite npm run build

# Restart Vite dev server
docker compose restart vite

# Stop containers
docker compose down

# Rebuild after Dockerfile changes
docker compose up -d --build
```

---

## Production Setup

### For Production - Using docker-stack.yml with Docker Swarm

This setup is optimized for production with SSL/HTTPS, secrets management, and scalability.

#### Prerequisites
- Docker
- Docker Swarm initialized
- Git
- Domain name
- SSL certificate (cert.pem and key.pem)

#### 1. Clone Repository
```bash
git clone https://github.com/olek1305/RentCar.git RentCar
cd RentCar
```

#### 2. Environment Configuration
```bash
cp .env.example .env
```

Edit `.env` for production:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com  # Nginx will automatically use this domain
DB_HOST=db
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=your_secure_password
REDIS_HOST=valkey
```

**Important:** The `APP_URL` value will be automatically used by nginx as the server name. No need to edit nginx config files!

#### 3. SSL Certificate Setup

**Using Cloudflare (Recommended):**

For detailed instructions on setting up SSL with Cloudflare, see **[SSL_SETUP_CLOUDFLARE.md](SSL_SETUP_CLOUDFLARE.md)**

Quick setup:
1. Generate Cloudflare Origin Certificate (SSL/TLS → Origin Server)
2. Save certificate and key to `docker/nginx/ssl/`
3. Use the helper script: `./deploy-ssl.sh`

**Manual setup:**
```bash
mkdir -p docker/nginx/ssl
# Copy your certificate files:
# - docker/nginx/ssl/cert.pem
# - docker/nginx/ssl/key.pem
```

**Note:** Tested with Cloudflare SSL (mode: Full, TLS 1.2+) + OVH Cloud VPS

#### 4. Initialize Docker Swarm
```bash
docker swarm init
```

#### 5. Create Docker Secrets

**Using the helper script (recommended):**
```bash
./deploy-ssl.sh
```

**Manual creation:**
```bash
docker secret create app_env .env
docker secret create ssl_key ./docker/nginx/ssl/key.pem
docker secret create ssl_cert ./docker/nginx/ssl/cert.pem
```

#### 6. Build Production Image
```bash
docker build -f Dockerfile.prod -t laravel-app:latest .
```

This builds a multi-stage production image with:
- Optimized PHP dependencies
- Compiled frontend assets
- No development dependencies
- Minimal image size

#### 7. Deploy the Stack
```bash
docker stack deploy -c docker-stack.yml laravel-app
```

#### 8. Verify Deployment
```bash
# Check services
docker stack services laravel-app

# Check container logs
docker service logs laravel-app_app
```

#### 9. Access Your Application
- **HTTP**: http://yourdomain.com (redirects to HTTPS)
- **HTTPS**: https://yourdomain.com

#### 10. Create Admin Account (Optional)
```bash
# Find the container ID
docker ps | grep laravel-app_app

# Execute command
docker exec -it <container_id> php artisan app:create-account
```

#### 11. Production Management Commands
```bash
# Update the stack after changes
docker build -f Dockerfile.prod -t laravel-app:latest .
docker stack deploy -c docker-stack.yml laravel-app

# Scale services
docker service scale laravel-app_app=3

# Remove the stack
docker stack rm laravel-app

# View service logs
docker service logs -f laravel-app_app
docker service logs -f laravel-app_nginx

# Inspect service status
docker service ps laravel-app_app
```

---

## Key Differences

### Development (docker-compose.yml)
- Uses `Dockerfile.dev` with Composer and Node.js installed
- Mounts source code for live editing
- HTTP only (no SSL)
- Separate Vite service for hot-reloading (`npm run dev`)
- Single instance
- Fast iteration
- Debug mode enabled (Xdebug)
- Automatic Composer and NPM install
- File watching with polling for instant updates
- Runs on `localhost:80` (app) and `localhost:5173` (Vite HMR)

### Production (docker-stack.yml)
- Uses `Dockerfile.prod` with multi-stage build
- Baked-in code (no live mounting)
- Pre-built frontend assets
- HTTPS with SSL certificates
- Multiple replicas for high availability
- Optimized for performance
- Secrets management
- Health checks and auto-restart
- No development dependencies
- Runs on domain with SSL

---

## Troubleshooting

### Development Issues

**Port 80 already in use:**
```bash
# Check what's using port 80
sudo lsof -i :80
# or
sudo netstat -tulpn | grep :80
```

**Composer install fails:**
```bash
# Enter container and run manually
docker compose exec app bash
composer install --verbose
```

**Permission errors:**
```bash
# Fix Laravel storage permissions
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R laravel:laravel storage bootstrap/cache
```

**Vite manifest not found:**
```bash
# Rebuild frontend assets
docker compose exec app npm run build
# Or restart the Vite service
docker compose restart vite
```

**Vite HMR not working:**
- Check that port 5173 is accessible
- View Vite logs: `docker compose logs -f vite`
- Make sure vite.config.js has correct server settings

### Production Issues

**Stack fails to deploy:**
```bash
# Check service errors
docker service ps laravel-app_app --no-trunc

# View detailed logs
docker service logs laravel-app_app
```

**SSL certificate errors:**
- Verify cert.pem and key.pem are valid
- Check that secrets were created correctly:
```bash
docker secret ls
```

**Cannot access application:**
- Verify ports 80 and 443 are open in firewall
- Check nginx service is running:
```bash
docker service ps laravel-app_nginx
```

---

## Additional Notes

- The development environment includes:
  - Xdebug for PHP debugging
  - Composer for PHP dependency management
  - Node.js 20.x and NPM for frontend development
  - Vite dev server with hot module replacement (HMR)
- Production images are optimized and do not include development tools
- Both setups use MySQL 9.4.0 and Valkey (Redis-compatible)
- Nginx 1.29.1 is used in both environments
- The Vite service in development uses file polling to detect changes in Docker volumes
