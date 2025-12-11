# SSL Setup Guide for Cloudflare

This guide explains how to set up SSL certificates for your docker-stack.yml deployment using Cloudflare.

## Prerequisites

- Active Cloudflare account
- Domain added to Cloudflare
- DNS records pointing to your server
- Docker Swarm initialized

## Option 1: Cloudflare Origin Certificate (Recommended)

This is the easiest and most secure option for sites using Cloudflare. The certificate is valid for up to 15 years and is specifically designed for use with Cloudflare's SSL modes.

### Step 1: Generate Origin Certificate in Cloudflare

1. Log in to your [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Select your domain
3. Go to **SSL/TLS** → **Origin Server**
4. Click **Create Certificate**
5. Choose the following options:
   - **Private key type**: RSA (2048)
   - **Hostnames**:
     - `yourdomain.com`
     - `*.yourdomain.com` (wildcard for subdomains)
   - **Certificate Validity**: 15 years (recommended)
6. Click **Create**
7. You'll see two text boxes:
   - **Origin Certificate**: Copy this entire content (including `-----BEGIN CERTIFICATE-----` and `-----END CERTIFICATE-----`)
   - **Private Key**: Copy this entire content (including `-----BEGIN PRIVATE KEY-----` and `-----END PRIVATE KEY-----`)

### Step 2: Save Certificates to Your Server

1. SSH into your server and navigate to your project:
```bash
cd /home/arcylisz/PhpstormProjects/RentCar
```

2. Create the certificate files:
```bash
# Save the Origin Certificate
cat > docker/nginx/ssl/cert.pem << 'EOF'
-----BEGIN CERTIFICATE-----
[Paste your Origin Certificate here]
-----END CERTIFICATE-----
EOF

# Save the Private Key
cat > docker/nginx/ssl/key.pem << 'EOF'
-----BEGIN PRIVATE KEY-----
[Paste your Private Key here]
-----END PRIVATE KEY-----
EOF
```

3. Set proper permissions:
```bash
chmod 600 docker/nginx/ssl/key.pem
chmod 644 docker/nginx/ssl/cert.pem
```

### Step 3: Configure Cloudflare SSL/TLS Settings

1. In your Cloudflare Dashboard, go to **SSL/TLS** → **Overview**
2. Set **SSL/TLS encryption mode** to one of:
   - **Full**: Encrypts traffic between Cloudflare and your origin server (recommended)
   - **Full (strict)**: Same as Full, but validates the origin certificate (requires valid cert)

**Important**: DO NOT use "Flexible" mode as it will create a redirect loop.

### Step 4: Configure Your Domain in .env

Nginx will automatically use your domain from the `APP_URL` environment variable. Edit your `.env` file:

```bash
APP_URL=https://yourdomain.com
```

The domain will be automatically extracted and used by nginx - no need to edit config files!

**Note:** The default nginx config uses `localhost`. It stays that way in your repository (no domain hardcoded in git). The actual domain is set via environment variables at deployment time.

### Step 5: Create Docker Secrets

```bash
# Remove old secrets if they exist
docker secret rm ssl_cert ssl_key 2>/dev/null || true

# Create new secrets
docker secret create ssl_cert ./docker/nginx/ssl/cert.pem
docker secret create ssl_key ./docker/nginx/ssl/key.pem
docker secret create app_env .env
```

Verify secrets were created:
```bash
docker secret ls
```

You should see:
```
ID                          NAME        CREATED
xxxxxxxxxxxx                app_env     X seconds ago
xxxxxxxxxxxx                ssl_cert    X seconds ago
xxxxxxxxxxxx                ssl_key     X seconds ago
```

### Step 6: Deploy the Stack

```bash
# Build the production image
docker build -f Dockerfile.prod -t laravel-app:latest .

# Deploy the stack
docker stack deploy -c docker-stack.yml laravel-app

# Check deployment status
docker stack services laravel-app
docker service logs laravel-app_nginx
```

### Step 7: Verify SSL is Working

1. Wait a few moments for services to start
2. Visit your domain: `https://yourdomain.com`
3. Check for the padlock icon in your browser
4. Verify the certificate:
   - Click the padlock icon
   - View certificate details
   - Should show "Cloudflare Inc ECC CA-3" as issuer

### Step 8: Configure Cloudflare Additional Settings (Optional)

1. **Enable Always Use HTTPS**:
   - Go to SSL/TLS → Edge Certificates
   - Turn on "Always Use HTTPS"

2. **Enable HSTS** (HTTP Strict Transport Security):
   - Go to SSL/TLS → Edge Certificates
   - Enable HSTS with recommended settings:
     - Max Age: 6 months
     - Include subdomains: Yes
     - Preload: Yes (only if you're sure)

3. **Minimum TLS Version**:
   - Go to SSL/TLS → Edge Certificates
   - Set "Minimum TLS Version" to TLS 1.2 or higher

4. **Enable HTTP/2 and HTTP/3**:
   - Go to Speed → Optimization
   - Enable HTTP/2 and HTTP/3 (QUIC)

---

## Option 2: Let's Encrypt with Cloudflare DNS Challenge

If you prefer Let's Encrypt certificates with automatic renewal, you can use Certbot with Cloudflare DNS plugin.

### Prerequisites

- Cloudflare API Token with DNS edit permissions

### Step 1: Install Certbot

```bash
# On Ubuntu/Debian
sudo apt update
sudo apt install certbot python3-certbot-dns-cloudflare
```

### Step 2: Create Cloudflare API Token

1. Log in to Cloudflare Dashboard
2. Go to My Profile → API Tokens
3. Click "Create Token"
4. Use template "Edit zone DNS"
5. Set permissions:
   - Zone → DNS → Edit
   - Zone → Zone → Read
6. Include your specific zone/domain
7. Create token and copy it

### Step 3: Configure Cloudflare Credentials

```bash
mkdir -p ~/.secrets/certbot
cat > ~/.secrets/certbot/cloudflare.ini << EOF
dns_cloudflare_api_token = YOUR_API_TOKEN_HERE
EOF

chmod 600 ~/.secrets/certbot/cloudflare.ini
```

### Step 4: Generate Certificate

```bash
sudo certbot certonly \
  --dns-cloudflare \
  --dns-cloudflare-credentials ~/.secrets/certbot/cloudflare.ini \
  --dns-cloudflare-propagation-seconds 60 \
  -d yourdomain.com \
  -d www.yourdomain.com \
  --email your-email@example.com \
  --agree-tos \
  --non-interactive
```

Certificates will be saved to:
- Certificate: `/etc/letsencrypt/live/yourdomain.com/fullchain.pem`
- Private Key: `/etc/letsencrypt/live/yourdomain.com/privkey.pem`

### Step 5: Copy Certificates to Project

```bash
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem
sudo chown $USER:$USER docker/nginx/ssl/*.pem
chmod 600 docker/nginx/ssl/key.pem
chmod 644 docker/nginx/ssl/cert.pem
```

### Step 6: Set Up Auto-Renewal

Certbot automatically creates a systemd timer for renewals. To test:

```bash
sudo certbot renew --dry-run
```

Create a post-renewal hook to update Docker secrets:

```bash
sudo cat > /etc/letsencrypt/renewal-hooks/deploy/update-docker-secrets.sh << 'EOF'
#!/bin/bash
cd /home/arcylisz/PhpstormProjects/RentCar
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem
chown arcylisz:arcylisz docker/nginx/ssl/*.pem

# Recreate secrets and redeploy
docker secret rm ssl_cert ssl_key
docker secret create ssl_cert docker/nginx/ssl/cert.pem
docker secret create ssl_key docker/nginx/ssl/key.pem
docker service update --force laravel-app_nginx
EOF

sudo chmod +x /etc/letsencrypt/renewal-hooks/deploy/update-docker-secrets.sh
```

---

## Troubleshooting

### Certificate Errors

**Error**: "SSL handshake failed"
- Check that secrets were created correctly: `docker secret ls`
- Verify certificate files exist and are not empty: `ls -lh docker/nginx/ssl/`
- Check nginx logs: `docker service logs laravel-app_nginx`

**Error**: "Redirect loop"
- Change Cloudflare SSL mode from "Flexible" to "Full" or "Full (strict)"

**Error**: "Certificate not trusted"
- If using Origin Certificate, ensure Cloudflare proxy is enabled (orange cloud)
- If using Let's Encrypt, ensure fullchain.pem is used (not cert.pem)

### Deployment Issues

**Services won't start**:
```bash
# Check service status
docker service ps laravel-app_nginx --no-trunc

# View detailed logs
docker service logs laravel-app_nginx --tail 100
```

**Port 443 not accessible**:
```bash
# Check firewall
sudo ufw status
sudo ufw allow 443/tcp

# Or with iptables
sudo iptables -I INPUT -p tcp --dport 443 -j ACCEPT
```

### Cloudflare Settings

**Wrong SSL mode**:
- Flexible = Cloudflare to visitor is encrypted, Cloudflare to origin is NOT (causes redirect loop)
- Full = Cloudflare to visitor AND origin is encrypted (recommended)
- Full (strict) = Same as Full but validates origin certificate

**Proxy status**:
- Ensure DNS record has orange cloud (proxied) enabled
- If using origin certificate, proxy MUST be enabled

---

## Security Best Practices

1. **Never commit certificates to git**:
   - The `.gitignore` in `docker/nginx/ssl/` already prevents this
   - Double-check: `git status`

2. **Restrict certificate file permissions**:
   ```bash
   chmod 600 docker/nginx/ssl/key.pem
   chmod 644 docker/nginx/ssl/cert.pem
   ```

3. **Use Docker secrets** (not volumes) for sensitive data:
   - Secrets are encrypted at rest
   - Only available to services that need them
   - Never stored on disk in containers

4. **Enable HSTS** in Cloudflare:
   - Prevents protocol downgrade attacks
   - Forces HTTPS even if user types http://

5. **Regular certificate rotation**:
   - Origin certificates: Rotate every 1-2 years (though valid for 15 years)
   - Let's Encrypt: Auto-renewed every 60 days

---

## Updating SSL Certificates

Docker secrets are **immutable** - they cannot be updated while in use. To update your SSL certificates:

### Method 1: Using the Helper Script (Recommended)

```bash
./deploy-ssl.sh
```

The script will:
1. Detect existing secrets
2. Check if the stack is running
3. Offer to remove the stack, update secrets, and redeploy automatically

### Method 2: Manual Update

```bash
# 1. Remove the stack
docker stack rm laravel-app

# 2. Wait for stack to fully shut down
sleep 15

# 3. Remove old secrets
docker secret rm ssl_cert ssl_key app_env

# 4. Create new secrets with updated certificates
docker secret create ssl_cert ./docker/nginx/ssl/cert.pem
docker secret create ssl_key ./docker/nginx/ssl/key.pem
docker secret create app_env .env

# 5. Redeploy the stack
docker stack deploy -c docker-stack.yml laravel-app
```

**Important**: You cannot remove secrets while the stack is using them. Always remove the stack first.

---

## Quick Reference Commands

```bash
# List secrets
docker secret ls

# List running stacks
docker stack ls

# Deploy/Update stack (does not update secrets)
docker stack deploy -c docker-stack.yml laravel-app

# Remove stack
docker stack rm laravel-app

# Check nginx configuration
docker service logs laravel-app_nginx

# Force update nginx service (restarts without updating secrets)
docker service update --force laravel-app_nginx

# Test SSL certificate
openssl s_client -connect yourdomain.com:443 -servername yourdomain.com

# View certificate details
curl -vI https://yourdomain.com
```

---

## Summary

For most users with Cloudflare, **Option 1 (Cloudflare Origin Certificate)** is recommended because:
- Easy to set up (5 minutes)
- No automatic renewal needed (valid for 15 years)
- Perfect integration with Cloudflare
- Free and secure

Choose **Option 2 (Let's Encrypt)** if:
- You need publicly trusted certificates
- You might move away from Cloudflare
- You prefer 90-day rotation for security

Both options work perfectly with your docker-stack.yml configuration!
