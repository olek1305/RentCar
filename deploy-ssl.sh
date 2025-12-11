#!/bin/bash

# SSL Certificate Deployment Script for Docker Stack
# This script helps deploy SSL certificates to Docker Swarm secrets

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SSL_CERT_PATH="docker/nginx/ssl/cert.pem"
SSL_KEY_PATH="docker/nginx/ssl/key.pem"
ENV_PATH=".env"
STACK_NAME="laravel-app"

# Functions
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_file() {
    if [ ! -f "$1" ]; then
        print_error "File not found: $1"
        return 1
    fi
    return 0
}

check_swarm() {
    if ! docker info 2>/dev/null | grep -q "Swarm: active"; then
        print_error "Docker Swarm is not initialized"
        print_info "Run: docker swarm init"
        exit 1
    fi
    print_success "Docker Swarm is active"
}

validate_certificate() {
    local cert_path="$1"

    print_info "Validating certificate..."

    if ! openssl x509 -in "$cert_path" -noout -text &>/dev/null; then
        print_error "Invalid certificate format"
        return 1
    fi

    local expiry_date
    local expiry_epoch
    local current_epoch
    local days_until_expiry

    expiry_date=$(openssl x509 -in "$cert_path" -noout -enddate | cut -d= -f2)
    expiry_epoch=$(date -d "$expiry_date" +%s)
    current_epoch=$(date +%s)
    days_until_expiry=$(( (expiry_epoch - current_epoch) / 86400 ))

    print_success "Certificate is valid"
    print_info "Expires: $expiry_date ($days_until_expiry days remaining)"

    if [ $days_until_expiry -lt 30 ]; then
        print_warning "Certificate expires in less than 30 days!"
    fi

    return 0
}

validate_key() {
    local key_path="$1"

    print_info "Validating private key..."

    if ! openssl pkey -in "$key_path" -check -noout &>/dev/null; then
        print_error "Invalid or corrupted private key"
        return 1
    fi

    print_success "Private key is valid"
    return 0
}

check_key_cert_match() {
    local cert_path="$1"
    local key_path="$2"

    print_info "Verifying certificate and key match..."

    local cert_modulus
    local key_modulus

    cert_modulus=$(openssl x509 -noout -modulus -in "$cert_path" 2>/dev/null | openssl md5)
    key_modulus=$(openssl rsa -noout -modulus -in "$key_path" 2>/dev/null | openssl md5)

    if [ "$cert_modulus" != "$key_modulus" ]; then
        print_error "Certificate and private key do not match!"
        return 1
    fi

    print_success "Certificate and key match correctly"
    return 0
}

check_stack_running() {
    if docker stack ls --format "{{.Name}}" | grep -q "^${STACK_NAME}$"; then
        return 0
    fi
    return 1
}

create_secrets() {
    print_info "Checking for existing secrets..."

    local secrets_exist=false
    local existing_secrets=""

    for secret in ssl_cert ssl_key app_env; do
        if docker secret ls --format "{{.Name}}" | grep -q "^${secret}$"; then
            secrets_exist=true
            existing_secrets="$existing_secrets $secret"
        fi
    done

    if [ "$secrets_exist" = true ]; then
        print_warning "Found existing secrets:$existing_secrets"

        if check_stack_running; then
            print_warning "Stack '$STACK_NAME' is currently running"
            print_info "Docker secrets cannot be updated while in use."
            echo
            print_info "Options:"
            print_info "  1. Remove stack, update secrets, and redeploy (automatic)"
            print_info "  2. Keep existing secrets and continue to deployment"
            print_info "  3. Cancel (exit without doing anything)"
            echo
            read -p "Choose option (1/2/3): " choice

            case $choice in
                1)
                    print_info "Removing stack: $STACK_NAME"
                    if docker stack rm "$STACK_NAME"; then
                        print_success "Stack removed"
                        print_info "Waiting for stack to fully shut down (15 seconds)..."
                        sleep 15
                    else
                        print_error "Failed to remove stack"
                        exit 1
                    fi
                    ;;
                2)
                    print_warning "Keeping existing secrets (certificates won't be updated)"
                    print_info "Skipping to deployment step..."
                    return 0
                    ;;
                3)
                    echo
                    print_info "Operation cancelled."
                    echo
                    print_info "To update SSL certificates manually, follow these steps:"
                    echo
                    echo -e "${YELLOW}# Step 1: Remove the running stack${NC}"
                    echo "  docker stack rm $STACK_NAME"
                    echo
                    echo -e "${YELLOW}# Step 2: Wait for stack to shut down completely${NC}"
                    echo "  docker stack ls  # Wait until $STACK_NAME is gone"
                    echo
                    echo -e "${YELLOW}# Step 3: Remove old secrets${NC}"
                    echo "  docker secret rm ssl_cert ssl_key app_env"
                    echo
                    echo -e "${YELLOW}# Step 4: Create new secrets with updated certificates${NC}"
                    echo "  docker secret create ssl_cert $SSL_CERT_PATH"
                    echo "  docker secret create ssl_key $SSL_KEY_PATH"
                    echo "  docker secret create app_env $ENV_PATH"
                    echo
                    echo -e "${YELLOW}# Step 5: Deploy the stack${NC}"
                    echo "  docker stack deploy -c docker-stack.yml $STACK_NAME"
                    echo
                    print_info "Or simply run this script again after removing the stack."
                    echo
                    exit 0
                    ;;
                *)
                    print_error "Invalid option"
                    exit 1
                    ;;
            esac
        fi

        # Now remove old secrets
        print_info "Removing old secrets..."
        for secret in ssl_cert ssl_key app_env; do
            if docker secret ls --format "{{.Name}}" | grep -q "^${secret}$"; then
                if docker secret rm "$secret" 2>/dev/null; then
                    print_success "Removed secret: $secret"
                else
                    print_error "Failed to remove secret: $secret"
                    exit 1
                fi
            fi
        done
    fi

    print_info "Creating new Docker secrets..."

    # Create SSL certificate secret
    if docker secret create ssl_cert "$SSL_CERT_PATH"; then
        print_success "Created secret: ssl_cert"
    else
        print_error "Failed to create secret: ssl_cert"
        exit 1
    fi

    # Create SSL key secret
    if docker secret create ssl_key "$SSL_KEY_PATH"; then
        print_success "Created secret: ssl_key"
    else
        print_error "Failed to create secret: ssl_key"
        exit 1
    fi

    # Create app environment secret
    if [ -f "$ENV_PATH" ]; then
        if docker secret create app_env "$ENV_PATH"; then
            print_success "Created secret: app_env"
        else
            print_error "Failed to create secret: app_env"
            exit 1
        fi
    else
        print_warning ".env file not found, skipping app_env secret"
    fi
}

list_secrets() {
    print_info "Current Docker secrets:"
    docker secret ls
}

deploy_stack() {
    print_info "Would you like to deploy/update the stack now? (y/n)"
    read -r response

    if [[ "$response" =~ ^[Yy]$ ]]; then
        print_info "Deploying stack: $STACK_NAME"

        if docker stack deploy -c docker-stack.yml "$STACK_NAME"; then
            print_success "Stack deployed successfully"
            print_info "Checking service status..."
            sleep 3
            docker stack services "$STACK_NAME"
        else
            print_error "Failed to deploy stack"
            exit 1
        fi
    else
        print_info "Skipping stack deployment"
        print_info "To deploy manually, run:"
        print_info "  docker stack deploy -c docker-stack.yml $STACK_NAME"
    fi
}

update_nginx() {
    if docker service ls --format "{{.Name}}" | grep -q "${STACK_NAME}_nginx"; then
        print_info "Would you like to force update the nginx service to reload certificates? (y/n)"
        read -r response

        if [[ "$response" =~ ^[Yy]$ ]]; then
            print_info "Updating nginx service..."
            if docker service update --force "${STACK_NAME}_nginx"; then
                print_success "Nginx service updated"
            else
                print_error "Failed to update nginx service"
            fi
        fi
    fi
}

show_help() {
    cat << EOF
SSL Certificate Deployment Script for Docker Stack

Usage: $0 [OPTIONS]

Options:
    -h, --help          Show this help message
    -c, --cert PATH     Path to SSL certificate (default: $SSL_CERT_PATH)
    -k, --key PATH      Path to SSL private key (default: $SSL_KEY_PATH)
    -e, --env PATH      Path to .env file (default: $ENV_PATH)
    -s, --stack NAME    Docker stack name (default: $STACK_NAME)
    --skip-validation   Skip certificate validation
    --auto-deploy       Automatically deploy stack without prompting

Examples:
    # Deploy with default paths
    $0

    # Specify custom certificate paths
    $0 -c /path/to/cert.pem -k /path/to/key.pem

    # Auto-deploy without prompts
    $0 --auto-deploy

Prerequisites:
    1. Docker Swarm must be initialized
    2. SSL certificate and key files must exist
    3. Files must have proper permissions (cert: 644, key: 600)

For more information, see: SSL_SETUP_CLOUDFLARE.md
EOF
}

# Main script
main() {
    local skip_validation=false
    local auto_deploy=false

    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_help
                exit 0
                ;;
            -c|--cert)
                SSL_CERT_PATH="$2"
                shift 2
                ;;
            -k|--key)
                SSL_KEY_PATH="$2"
                shift 2
                ;;
            -e|--env)
                ENV_PATH="$2"
                shift 2
                ;;
            -s|--stack)
                STACK_NAME="$2"
                shift 2
                ;;
            --skip-validation)
                skip_validation=true
                shift
                ;;
            --auto-deploy)
                auto_deploy=true
                shift
                ;;
            *)
                print_error "Unknown option: $1"
                show_help
                exit 1
                ;;
        esac
    done

    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}SSL Certificate Deployment${NC}"
    echo -e "${BLUE}================================${NC}"
    echo

    # Check Docker Swarm
    check_swarm
    echo

    # Check if certificate files exist
    print_info "Checking certificate files..."
    check_file "$SSL_CERT_PATH" || exit 1
    check_file "$SSL_KEY_PATH" || exit 1
    print_success "Certificate files found"
    echo

    # Validate certificates
    if [ "$skip_validation" = false ]; then
        validate_certificate "$SSL_CERT_PATH" || exit 1
        validate_key "$SSL_KEY_PATH" || exit 1
        check_key_cert_match "$SSL_CERT_PATH" "$SSL_KEY_PATH" || exit 1
        echo
    else
        print_warning "Skipping certificate validation"
        echo
    fi

    # Create secrets
    create_secrets
    echo

    # List secrets
    list_secrets
    echo

    # Deploy stack
    if [ "$auto_deploy" = true ]; then
        print_info "Auto-deploying stack: $STACK_NAME"
        docker stack deploy -c docker-stack.yml "$STACK_NAME"
        print_success "Stack deployed successfully"
        sleep 3
        docker stack services "$STACK_NAME"
    else
        deploy_stack
    fi
    echo

    # Update nginx
    update_nginx
    echo

    print_success "SSL deployment completed!"
    echo
    print_info "Next steps:"
    print_info "  1. Verify services are running: docker stack services $STACK_NAME"
    print_info "  2. Check nginx logs: docker service logs ${STACK_NAME}_nginx"
    print_info "  3. Test SSL: curl -vI https://yourdomain.com"
    print_info "  4. Configure Cloudflare SSL mode to 'Full' or 'Full (strict)'"
}

# Run main function
main "$@"
