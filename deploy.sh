#!/usr/bin/env bash
set -euo pipefail
umask 002

# Colors
if [ -t 1 ] && command -v tput >/dev/null 2>&1; then
  BOLD="$(tput bold)"; RESET="$(tput sgr0)"; DIM="$(tput dim)"
  RED="\033[31m"; GREEN="\033[32m"; YELLOW="\033[33m"; BLUE="\033[34m"; CYAN="\033[36m"
else
  BOLD=""; RESET=""; DIM=""; RED=""; GREEN=""; YELLOW=""; BLUE=""; CYAN=""
fi

info()   { echo -e "${BLUE}==>${RESET} $*"; }
warn()   { echo -e "${YELLOW}==>${RESET} $*"; }
error()  { echo -e "${RED}==>${RESET} $*" >&2; }
success(){ echo -e "${GREEN}==>${RESET} $*"; }

usage() {
  cat <<USAGE
${BOLD}Laravel+Inertia Production Deployment Script${RESET}

Usage: ./deploy.sh [options]

Options:
  -m, --mode <prod|dev>     Deployment mode (production or development). Defaults to production.
  -b, --branch <branch>     Git branch to deploy (default: main)
  -r, --remote <remote>      Git remote name (default: origin)
  --no-pull                  Skip git pull (useful if already on latest commit)
  --no-tests                 Skip running tests before deployment
  --no-migrate               Skip running migrations
  --migrate-only             Only run migrations (skip other steps)
  --maintenance              Enable maintenance mode during deployment
  --no-maintenance           Do not use maintenance mode
  -y, --yes                  Non-interactive; accept defaults and skip prompts
  -h, --help                 Show this help and exit

Examples:
  ./deploy.sh                              # Interactive deployment (defaults to production)
  ./deploy.sh -m dev                       # Deploy in development mode
  ./deploy.sh -m prod -b main -y          # Non-interactive production deployment from main
  ./deploy.sh -m dev --no-tests            # Development deployment, skip tests
  ./deploy.sh --migrate-only               # Only run migrations
USAGE
}

MODE_FLAG=""
BRANCH="main"
REMOTE="origin"
DO_PULL=1
RUN_TESTS=1
RUN_MIGRATE=1
MIGRATE_ONLY=0
USE_MAINTENANCE=1
ASSUME_YES=0

while [ "${1-}" != "" ]; do
  case "$1" in
    -m|--mode)
      MODE_FLAG="${2-}"; shift 2 || { error "Missing value for $1"; exit 2; } ;;
    -b|--branch)
      BRANCH="${2-}"; shift 2 || { error "Missing value for $1"; exit 2; } ;;
    -r|--remote)
      REMOTE="${2-}"; shift 2 || { error "Missing value for $1"; exit 2; } ;;
    --no-pull)
      DO_PULL=0; shift ;;
    --no-tests)
      RUN_TESTS=0; shift ;;
    --no-migrate)
      RUN_MIGRATE=0; shift ;;
    --migrate-only)
      MIGRATE_ONLY=1; shift ;;
    --maintenance)
      USE_MAINTENANCE=1; shift ;;
    --no-maintenance)
      USE_MAINTENANCE=0; shift ;;
    -y|--yes)
      ASSUME_YES=1; shift ;;
    -h|--help)
      usage; exit 0 ;;
    *)
      error "Unknown option: $1"; usage; exit 2 ;;
  esac
done

banner() {
  echo -e "${CYAN}${BOLD}────────────────────────────────────────────────────────────${RESET}"
  echo -e "${CYAN}${BOLD}  Laravel Vue Starter Kit - Deployment${RESET}"
  echo -e "${CYAN}${BOLD}────────────────────────────────────────────────────────────${RESET}"
}

trap 'error "Deployment failed."' ERR

ask_yes_no() {
  local prompt="$1"; shift
  local default="$1"; shift
  local answer
  if [ "$ASSUME_YES" -eq 1 ]; then
    answer="$default"
  else
    read -r -p "$prompt " answer || answer=""
    answer=${answer:-$default}
  fi
  case "$answer" in
    y|Y) return 0 ;;
    n|N) return 1 ;;
    *) return 1 ;;
  esac
}

# Move to project root (directory of this script)
SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
cd "$SCRIPT_DIR"

banner

# Verify we're in a Laravel project
if [ ! -f "artisan" ] || [ ! -f "composer.json" ]; then
  error "This doesn't appear to be a Laravel project directory."
  exit 1
fi

info "Checking required tools"
command -v php >/dev/null || { error "php not found"; exit 1; }
command -v composer >/dev/null || { error "composer not found"; exit 1; }
command -v npm >/dev/null || { error "npm not found"; exit 1; }

# Check if .env exists, copy from .env.example if not
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    info ".env file not found. Copying from .env.example"
    cp .env.example .env
    success ".env file created from .env.example"
  else
    error ".env file not found and .env.example is missing. Please create .env manually."
    exit 1
  fi
fi

# Get current branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")

# Determine deployment mode
echo
if [ -n "$MODE_FLAG" ]; then
  case "$MODE_FLAG" in
    prod|production|p|P|1) MODE="p" ;;
    dev|development|d|D|2) MODE="d" ;;
    *) error "Invalid mode: $MODE_FLAG"; exit 2 ;;
  esac
else
  if [ "$ASSUME_YES" -eq 1 ]; then
    MODE="p"
  else
    echo ""
    echo "${BOLD}Select deployment mode:${RESET}"
    echo "  1) Production (default)"
    echo "  2) Development"
    read -r -p "Enter choice [1-2] (default 1): " MODE_CHOICE || MODE_CHOICE=""
    MODE_CHOICE=${MODE_CHOICE:-1}
    case "$MODE_CHOICE" in
      1) MODE="p" ;;
      2) MODE="d" ;;
      p|P) MODE="p" ;;
      d|D) MODE="d" ;;
      *) warn "Unknown choice '$MODE_CHOICE', defaulting to production"; MODE="p" ;;
    esac
  fi
fi
IS_PROD=0
case "$MODE" in
  p|P) IS_PROD=1 ;;
  *) IS_PROD=0 ;;
esac

if [ "$IS_PROD" -eq 1 ]; then
  info "Deployment mode: ${BOLD}Production${RESET}"
else
  info "Deployment mode: ${BOLD}Development${RESET}"
fi

if [ "$MIGRATE_ONLY" -eq 0 ]; then
  # Initial setup steps - must be done before git pull to ensure dependencies are available
  info "Installing PHP dependencies"
  composer update --no-interaction --prefer-dist --ansi || {
    error "Failed to install PHP dependencies"
    exit 1
  }

  info "Installing Node dependencies"
  if [ -f package-lock.json ]; then
    npm ci --prefer-offline --no-audit || {
      error "Failed to install Node dependencies"
      exit 1
    }
  else
    npm install --prefer-offline --no-audit || {
      error "Failed to install Node dependencies"
      exit 1
    }
  fi

  info "Building frontend assets"
  npm run build || {
    error "Failed to build frontend assets"
    exit 1
  }

  # Generate app key if not set
  info "Ensuring application key is set"
  php artisan key:generate --force || true

  # Create database.sqlite if it doesn't exist
  if [ ! -f database/database.sqlite ]; then
    info "Creating database.sqlite file"
    touch database/database.sqlite || {
      warn "Could not create database.sqlite, continuing anyway"
    }
  fi

  # ALWAYS disable maintenance mode at the start to ensure clean state for tests
  # This MUST happen before any tests run
  info "Disabling maintenance mode (if enabled) to ensure tests run against live application"
  php artisan up || true
  sleep 1  # Give Laravel a moment to fully disable maintenance mode

  if [ "$DO_PULL" -eq 1 ]; then
    info "Fetching latest changes from ${REMOTE}/${BRANCH}"
    git fetch "${REMOTE}" "${BRANCH}" || {
      error "Failed to fetch from ${REMOTE}/${BRANCH}"
      exit 1
    }

    # Check if there are local changes
    if ! git diff-index --quiet HEAD -- 2>/dev/null; then
      warn "You have uncommitted changes. They may be overwritten."
      if ! ask_yes_no "Continue anyway? [y/N]" "n"; then
        exit 1
      fi
    fi

    # Check if we need to pull
    LOCAL=$(git rev-parse HEAD 2>/dev/null)
    REMOTE_REF="${REMOTE}/${BRANCH}"
    REMOTE_COMMIT=$(git rev-parse "${REMOTE_REF}" 2>/dev/null || echo "")
    
    if [ -n "$REMOTE_COMMIT" ] && [ "$LOCAL" != "$REMOTE_COMMIT" ]; then
      info "Pulling latest changes from ${REMOTE}/${BRANCH}"
      git pull "${REMOTE}" "${BRANCH}" || {
        error "Failed to pull from ${REMOTE}/${BRANCH}"
        exit 1
      }
    else
      info "Already up to date with ${REMOTE}/${BRANCH}"
    fi
  else
    info "Skipping git pull (--no-pull flag set)"
  fi

  # Run tests after setup is complete
  if [ "$RUN_TESTS" -eq 1 ]; then
    info "Running database migrations fresh with seeding"
    php artisan migrate:fresh --seed --force || {
      error "Failed to run migrations"
      exit 1
    }
    
    info "Running tests"
    # Try composer test first, then php artisan test, then fallback to phpunit/pest directly
    if composer run test 2>/dev/null; then
      success "All tests passed"
    elif php artisan test --stop-on-failure 2>/dev/null; then
      success "All tests passed"
    elif [ -f vendor/bin/pest ]; then
      if vendor/bin/pest --stop-on-failure; then
        success "All tests passed"
      else
        error "Tests failed. Aborting deployment."
        exit 1
      fi
    elif [ -f vendor/bin/phpunit ]; then
      if vendor/bin/phpunit --stop-on-failure; then
        success "All tests passed"
      else
        error "Tests failed. Aborting deployment."
        exit 1
      fi
    else
      error "No test runner found (composer test, artisan test, pest, or phpunit)"
      exit 1
    fi
  fi

  # Enable maintenance mode if requested (after tests pass)
  if [ "$USE_MAINTENANCE" -eq 1 ]; then
    info "Enabling maintenance mode"
    php artisan down || true
  fi

  # Reinstall dependencies based on mode (production vs development)
  if [ "$IS_PROD" -eq 1 ]; then
    info "Reinstalling PHP dependencies for production"
    composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction --prefer-dist --ansi
  fi

  if [ "$IS_PROD" -eq 1 ]; then
    info "Optimizing Laravel for production"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache || true
  else
    info "Development mode: skipping Laravel optimization"
  fi

  # Clear application cache
  info "Clearing application cache"
  php artisan cache:clear || true
fi

# Run migrations if requested
if [ "$RUN_MIGRATE" -eq 1 ]; then
  info "Running database migrations"
  php artisan migrate --force
else
  info "Skipping migrations (--no-migrate flag set)"
fi

# Set proper permissions
info "Setting permissions"
for dir in storage bootstrap/cache; do
  if [ -d "$dir" ]; then
    chmod -R ug+rwX "$dir" || true
  fi
done

# Disable maintenance mode if it was enabled
if [ "$USE_MAINTENANCE" -eq 1 ] && [ "$MIGRATE_ONLY" -eq 0 ]; then
  info "Disabling maintenance mode"
  php artisan up || true
fi

# Show deployment summary
echo
success "Deployment completed successfully!"
echo
if [ "$IS_PROD" -eq 1 ]; then
  info "Deployment mode: ${BOLD}Production${RESET}"
else
  info "Deployment mode: ${BOLD}Development${RESET}"
fi
info "Current branch: ${CURRENT_BRANCH}"
info "Deployed from: ${REMOTE}/${BRANCH}"
if [ "$RUN_MIGRATE" -eq 1 ]; then
  info "Migrations: Applied"
else
  info "Migrations: Skipped"
fi
echo


