# Laravel RentCar - Docker Production Setup
This guide covers the complete setup for running Laravel RentCar in production with 
    Docker, Nginx (HTTP/2), MySQL, Valkey (Redis-compatible), and SSL.

### Prerequisites
- Docker & Docker Compose installed
- Git
- Node.js & NPM (for building assets)
- OpenSSL (for generating certificates if not using Let's Encrypt)

### 1. Clone Repository
```bash
    git clone https://github.com/olek1305/RentCar.git RentCar
    cd RentCar
```

### 2. Environment Configuration
```bash
    cp .env.example .env
```

# 3. Run Docker
### First, start the containers temporarily
```bash
    docker compose up -d --build
```



# 4. Install Dependencies & Build Assets
### Install dependencies
```bash
    docker compose exec app composer install --no-dev --optimize-autoloader
    docker compose exec app npm install
    docker compose exec app npm run build
    docker compose exec app php artisan key:generate
```

# 4.1 Optional migrate from seeds for development
### First you need install composer with dev
```bash
    docker compose exec app composer install
```

### Migrate
```bash
    docker compose exec app php artisan migrate --seed
```

# 5. SSL & HTTP/2 Verification
### lace your SSL certificates in ./docker/nginx/ssl/ or generate self-signed certificates:
```bash
    mkdir -p docker/nginx/ssl
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout docker/nginx/ssl/key.pem \
        -out docker/nginx/ssl/cert.pem
```

# Network Verification
### Verify HTTP/2 and SSL:
```bash
    curl -I https://localhost --http2 --insecure
    # Should show: HTTP/2 200
```
# Maintenance
### Update Application
```bash
    # Pull latest changes
    git pull origin main
    
    # Rebuild if Dockerfile changed
    docker compose build app
    
    # Restart containers
    docker compose down
    docker compose up -d
    
    # Run any new migrations
    docker compose exec app php artisan migrate
```

### Backup Database
```bash
    docker compose exec mysql mysqldump -u root -p${DB_PASSWORD} --all-databases > backup.sql
```

# Troubleshooting
### Container Logs
```bash
    docker compose logs -f nginx  # Nginx logs
    docker compose logs -f app    # PHP/Laravel logs
    docker compose logs -f mysql  # MySQL logs
    docker compose logs -f valkey # Valkey logs
```

### Database connection issues
```bash
    # Check MySQL is running
    docker compose exec mysql mysql -u ${DB_USERNAME} -p${DB_PASSWORD} -e "SHOW DATABASES;"

    # Test from Laravel
    docker compose exec app php artisan tinker --execute="DB::connection()->getPdo();"
```
