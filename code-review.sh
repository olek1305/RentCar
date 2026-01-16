#!/bin/bash

# Full Project Code Review Script for Claude Code (Laravel)
# Performs comprehensive code review of Laravel project

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REVIEW_AREAS=("all")

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

print_header() {
    echo -e "${CYAN}================================${NC}"
    echo -e "${CYAN}$1${NC}"
    echo -e "${CYAN}================================${NC}"
    echo
}

check_claude_code() {
    if ! command -v claude &> /dev/null; then
        print_error "Claude Code CLI is not installed or not in PATH"
        print_info "Install it from: https://github.com/anthropics/claude-code"
        exit 1
    fi
    print_success "Claude Code CLI found"
}

check_laravel_project() {
    if [ ! -f "$PROJECT_ROOT/artisan" ]; then
        print_error "Not a Laravel project (artisan file not found)"
        exit 1
    fi
    print_success "Laravel project detected"
}

run_full_review() {
    local focus="$1"

    print_info "Starting full project code review..."
    print_info "Focus area: $focus"
    echo

    local prompt=""

    case $focus in
        all)
            prompt="Przeprowadź kompleksowy code review tego projektu Laravel. Przeanalizuj:

1. **Architektura i struktura**
   - Organizacja kodu i zgodność z konwencjami Laravel
   - Separacja odpowiedzialności (Controllers, Services, Repositories)
   - Użycie Design Patterns

2. **Bezpieczeństwo**
   - SQL Injection, XSS, CSRF
   - Walidacja inputów
   - Autoryzacja i autentykacja
   - Wrażliwe dane w kodzie/konfiguracji

3. **Wydajność**
   - N+1 queries
   - Brakujące indeksy w migracjach
   - Eager loading
   - Caching

4. **Jakość kodu**
   - DRY (Don't Repeat Yourself)
   - SOLID principles
   - Type hints i return types
   - Error handling

5. **Testy**
   - Pokrycie testami
   - Jakość testów

6. **Best Practices Laravel**
   - Użycie Eloquent vs Query Builder
   - Form Requests
   - Resources/Transformers
   - Events/Listeners
   - Queues dla długich operacji

Podaj konkretne przykłady problemów z lokalizacją plików i sugestie naprawy. Odpowiedz po polsku."
            ;;
        security)
            prompt="Przeprowadź security audit tego projektu Laravel. Skup się na:

1. **Injection attacks** - SQL Injection, Command Injection
2. **XSS (Cross-Site Scripting)** - w Blade templates i API responses
3. **CSRF** - ochrona formularzy
4. **Authentication** - bezpieczeństwo logowania, sesji
5. **Authorization** - policies, gates, middleware
6. **Sensitive data** - hasła, klucze API, tokeny w kodzie
7. **File uploads** - walidacja, ścieżki
8. **Mass assignment** - \$fillable vs \$guarded
9. **Rate limiting** - ochrona przed brute force
10. **HTTPS/SSL** - wymuszanie bezpiecznych połączeń

Podaj konkretne luki z lokalizacją plików i jak je naprawić. Odpowiedz po polsku."
            ;;
        performance)
            prompt="Przeprowadź audyt wydajności tego projektu Laravel. Przeanalizuj:

1. **Database queries**
   - N+1 problem
   - Brakujące indeksy
   - Nieoptymalne zapytania
   - Eager loading vs Lazy loading

2. **Caching**
   - Brakujące cache'owanie
   - Strategia cache

3. **Code optimization**
   - Zbędne operacje w pętlach
   - Memory leaks
   - Heavy computations

4. **Assets & Frontend**
   - Bundle size
   - Lazy loading

5. **Queue usage**
   - Operacje do przeniesienia na queue

Podaj konkretne miejsca z lokalizacją plików i sugestie optymalizacji. Odpowiedz po polsku."
            ;;
        architecture)
            prompt="Przeanalizuj architekturę tego projektu Laravel. Oceń:

1. **Struktura katalogów** - zgodność z konwencjami
2. **Separacja warstw** - Controllers, Services, Repositories
3. **Design Patterns** - użycie wzorców projektowych
4. **SOLID principles** - przestrzeganie zasad
5. **Dependency Injection** - użycie DI container
6. **Code coupling** - powiązania między modułami
7. **Scalability** - skalowalność rozwiązań
8. **Testability** - możliwość testowania

Podaj rekomendacje architektoniczne. Odpowiedz po polsku."
            ;;
        tests)
            prompt="Przeanalizuj testy w tym projekcie Laravel:

1. **Pokrycie testami** - które części kodu nie mają testów
2. **Jakość testów** - czy testują właściwe rzeczy
3. **Test isolation** - niezależność testów
4. **Factories & Seeders** - użycie do testów
5. **Mocking** - prawidłowe mockowanie zależności
6. **Feature vs Unit tests** - proporcje i zasadność
7. **Edge cases** - testowanie przypadków brzegowych
8. **Database testing** - RefreshDatabase, transactions

Zaproponuj brakujące testy i ulepszenia. Odpowiedz po polsku."
            ;;
        models)
            prompt="Przeanalizuj modele Eloquent w tym projekcie:

1. **Relationships** - poprawność relacji
2. **Scopes** - użycie local/global scopes
3. **Accessors/Mutators** - prawidłowe użycie
4. **Casts** - rzutowanie atrybutów
5. **Events** - obserwatory, eventy modeli
6. **Fillable/Guarded** - mass assignment protection
7. **Soft deletes** - użycie gdzie potrzebne
8. **Factories** - jakość fabryk

Odpowiedz po polsku z konkretnymi przykładami."
            ;;
        api)
            prompt="Przeanalizuj API w tym projekcie Laravel:

1. **RESTful conventions** - zgodność z REST
2. **Versioning** - wersjonowanie API
3. **Resources** - użycie API Resources
4. **Validation** - walidacja requestów
5. **Error handling** - obsługa błędów, kody HTTP
6. **Authentication** - Sanctum/Passport
7. **Rate limiting** - limity requestów
8. **Documentation** - dokumentacja API

Odpowiedz po polsku z rekomendacjami."
            ;;
    esac

    cd "$PROJECT_ROOT"
    claude "$prompt"
}

show_help() {
    cat << EOF
Laravel Project Code Review Script for Claude Code

Usage: $0 [AREA]

Review Areas:
    all             Full comprehensive review (default)
    security        Security audit - vulnerabilities, injections, auth
    performance     Performance audit - queries, caching, optimization
    architecture    Architecture review - structure, patterns, SOLID
    tests           Test coverage and quality analysis
    models          Eloquent models review
    api             API endpoints review

Options:
    -h, --help      Show this help message

Examples:
    # Full project review
    $0
    $0 all

    # Security focused review
    $0 security

    # Performance audit
    $0 performance

    # Architecture analysis
    $0 architecture

    # Test coverage review
    $0 tests

Prerequisites:
    1. Claude Code CLI must be installed and configured
    2. Must be run from Laravel project root
EOF
}

# Main script
main() {
    local focus="all"

    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_help
                exit 0
                ;;
            all|security|performance|architecture|tests|models|api)
                focus="$1"
                shift
                ;;
            *)
                print_error "Unknown option: $1"
                show_help
                exit 1
                ;;
        esac
    done

    print_header "Laravel Code Review"

    # Check prerequisites
    check_claude_code
    check_laravel_project
    echo

    # Run review
    run_full_review "$focus"

    echo
    print_success "Code review session completed!"
}

# Run main function
main "$@"
