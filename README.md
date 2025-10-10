# Laravel RentCar - Docker Production Setup
This guide covers the complete setup for running Laravel RentCar in production with 
    Docker, Nginx, MySQL, Valkey (Redis-compatible).
    Soon add SSL and HTTP/2 support.

## Prerequisites for production
- Docker
- Git

### 1. Clone Repository
```bash
    git clone https://github.com/olek1305/RentCar.git RentCar
    cd RentCar
```

### 2. Environment Configuration
```bash
    cp .env.example .env
```

### 3. Run Docker Swarm
- Initialize Docker Swarm
```bash
    docker swarm init
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
