# Laravel RentCar - Docker Production Setup
This guide covers the complete setup for running Laravel RentCar in production with 
    Docker, Nginx, MySQL, Valkey (Redis-compatible). 
    Enable HTTPS and SSL.

I tested Cloudfare with SSL (mode: full, TLS 1.2+) + OvhCloud VPS

## Prerequisites for production
- Docker
- Git
- SSL certificate and domain

### 1. Clone Repository
```bash
    git clone https://github.com/olek1305/RentCar.git RentCar
    cd RentCar
```

### 2. Environment Configuration
```bash
    cp .env.example .env
```
- SSL key.pem and cert.pem put in docker/nginx/ssl

## 3A. For docker compose
### Run Docker compose
```bash
    docker compose up -d
```
## 3B. For Swarm
### Run Docker Swarm
- Initialize Docker Swarm
```bash
    docker swarm init
    docker secret create app_env .env
    docker secret create ssl_key ./docker/nginx/ssl/key.pem
    docker secret create ssl_cert ./docker/nginx/ssl/cert.pem
```

- Build the Docker image:
```bash
    docker build -t laravel-app:8.4 .
```

- Deploy the stack:
```bash
    docker stack deploy -c docker-stack.yml laravel-app
```

# 4. (Optional) Create an Admin Account
- Windows
```bash
  docker ps | findstr laravel-app_app
  docker exec -it <container_id_or_name> php artisan app:create-account
```

- Linux
```bash
  docker ps | grep laravel-app_app
  docker exec -it <container_id_or_name> php artisan app:create-account
```
