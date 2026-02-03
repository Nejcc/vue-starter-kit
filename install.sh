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
${BOLD}Laravel+Inertia Installer${RESET}

Usage: ./install.sh [options]

Options:
  -m, --mode <prod|dev>   Setup mode (production or development). Defaults to dev.
  -y, --yes               Non-interactive; accept defaults and skip prompts.
  --no-clean              Do not remove existing vendor/ and node_modules/.
  --fresh                 Use php artisan migrate:fresh --seed instead of migrate --seed.
  --no-fresh              Force regular migrate --seed.
  --telescope             Install Laravel Telescope (composer --dev) and publish assets.
  --no-telescope          Do not install Laravel Telescope.
  --nightwatch            Install Nightwatch (npm -D) and init config.
  --no-nightwatch         Do not install Nightwatch.
  -h, --help              Show this help and exit.

Examples:
  ./install.sh                      # interactive
  ./install.sh -m prod -y           # non-interactive production install
  ./install.sh --mode dev --no-clean
  ./install.sh -y --fresh           # non-interactive with migrate:fresh --seed
USAGE
}

MODE_FLAG=""
ASSUME_YES=0
CLEAN_DEPS=1
FRESH_FLAG=""
TELESCOPE_FLAG=""
NIGHTWATCH_FLAG=""

while [ "${1-}" != "" ]; do
  case "$1" in
    -m|--mode)
      MODE_FLAG="${2-}"; shift 2 || { error "Missing value for $1"; exit 2; } ;;
    -y|--yes)
      ASSUME_YES=1; shift ;;
    --no-clean)
      CLEAN_DEPS=0; shift ;;
    --fresh)
      FRESH_FLAG="yes"; shift ;;
    --no-fresh)
      FRESH_FLAG="no"; shift ;;
    --telescope)
      TELESCOPE_FLAG="yes"; shift ;;
    --no-telescope)
      TELESCOPE_FLAG="no"; shift ;;
    --nightwatch)
      NIGHTWATCH_FLAG="yes"; shift ;;
    --no-nightwatch)
      NIGHTWATCH_FLAG="no"; shift ;;
    -h|--help)
      usage; exit 0 ;;
    *)
      error "Unknown option: $1"; usage; exit 2 ;;
  esac
done

banner() {
  echo -e "${CYAN}${BOLD}────────────────────────────────────────────────────────────${RESET}"
  echo -e "${CYAN}${BOLD}  After.si Vue Starter Kit - Installer${RESET}"
  echo -e "${CYAN}${BOLD}────────────────────────────────────────────────────────────${RESET}"
}

# Enhanced error handling
cleanup_on_error() {
  local exit_code=$?
  if [ $exit_code -ne 0 ]; then
    echo
    error "Installation failed at line $1 with exit code $exit_code"
    error "Please check the error messages above and try again."
  fi
}

trap 'cleanup_on_error $LINENO' ERR

ask_yes_no() {
  local prompt="$1"; shift
  local default="$1"; shift # "y" or "n"
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

info "Checking required tools"
command -v php >/dev/null || { error "PHP is not installed. Please install PHP 8.4 or higher."; exit 1; }
command -v composer >/dev/null || { error "Composer is not installed. Please install Composer from https://getcomposer.org"; exit 1; }
command -v npm >/dev/null || { error "npm is not installed. Please install Node.js 18+ from https://nodejs.org"; exit 1; }

info "Validating tool versions"
# Check PHP version (8.4+)
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
PHP_MAJOR=$(echo "$PHP_VERSION" | cut -d. -f1)
PHP_MINOR=$(echo "$PHP_VERSION" | cut -d. -f2)
if [ "$PHP_MAJOR" -lt 8 ] || { [ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 4 ]; }; then
  error "PHP 8.4+ is required. Found: $PHP_VERSION"
  exit 1
fi
echo "  ${DIM}PHP: $PHP_VERSION${RESET}"

# Check Node.js version (18+)
NODE_VERSION=$(node -v 2>/dev/null | sed 's/v//' || echo "0.0.0")
NODE_MAJOR=$(echo "$NODE_VERSION" | cut -d. -f1)
if [ "$NODE_MAJOR" -lt 18 ]; then
  error "Node.js 18+ is required. Found: $NODE_VERSION"
  exit 1
fi
echo "  ${DIM}Node.js: $NODE_VERSION${RESET}"

# Check Composer version
COMPOSER_VERSION=$(composer --version 2>/dev/null | head -n1 | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -n1 || echo "0.0.0")
echo "  ${DIM}Composer: $COMPOSER_VERSION${RESET}"

info "Checking required PHP extensions"
REQUIRED_EXTENSIONS=("pdo" "mbstring" "openssl" "tokenizer" "xml" "ctype" "json" "fileinfo" "curl")
MISSING_EXTENSIONS=()
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
  if ! php -m | grep -qi "^${ext}$"; then
    MISSING_EXTENSIONS+=("$ext")
  fi
done
if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
  error "Missing required PHP extensions: ${MISSING_EXTENSIONS[*]}"
  error "Please install them using your system's package manager."
  exit 1
fi
echo "  ${DIM}All required PHP extensions are installed${RESET}"

echo
if [ -n "$MODE_FLAG" ]; then
  case "$MODE_FLAG" in
    prod|production|p|P|1) MODE="p" ;;
    dev|development|d|D|2) MODE="d" ;;
    *) error "Invalid mode: $MODE_FLAG"; exit 2 ;;
  esac
else
  if [ "$ASSUME_YES" -eq 1 ]; then
    MODE="d"
  else
    echo ""
    echo "${BOLD}Select setup mode:${RESET}"
    echo "  1) Production"
    echo "  2) Development (default)"
    read -r -p "Enter choice [1-2] (default 2): " MODE_CHOICE || MODE_CHOICE=""
    MODE_CHOICE=${MODE_CHOICE:-2}
    case "$MODE_CHOICE" in
      1) MODE="p" ;;
      2) MODE="d" ;;
      p|P) MODE="p" ;;
      d|D) MODE="d" ;;
      *) warn "Unknown choice '$MODE_CHOICE', defaulting to development"; MODE="d" ;;
    esac
  fi
fi
IS_PROD=0
case "$MODE" in
  p|P) IS_PROD=1 ;;
  *) IS_PROD=0 ;;
esac

# Decide on migrate strategy
DO_FRESH=0
if [ -n "$FRESH_FLAG" ]; then
  if [ "$FRESH_FLAG" = "yes" ]; then DO_FRESH=1; else DO_FRESH=0; fi
else
  if [ "$ASSUME_YES" -eq 1 ]; then
    DO_FRESH=0
  else
    echo ""
    echo "${BOLD}Database migration:${RESET}"
    echo "  1) Reset database (migrate:fresh --seed)"
    echo "  2) Regular migrate (migrate --seed) (default)"
    read -r -p "Enter choice [1-2] (default 2): " FRESH_CHOICE || FRESH_CHOICE=""
    FRESH_CHOICE=${FRESH_CHOICE:-2}
    case "$FRESH_CHOICE" in
      1) DO_FRESH=1 ;;
      2) DO_FRESH=0 ;;
      *) warn "Unknown choice '$FRESH_CHOICE', defaulting to regular migrate"; DO_FRESH=0 ;;
    esac
  fi
fi

info "Clearing Laravel caches"
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

info "Preparing environment file"
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    success "Created .env file from .env.example"
  else
    error ".env and .env.example not found. Aborting."
    exit 1
  fi
else
  echo "  ${DIM}.env file already exists${RESET}"
fi

info "Generating application key"
if ! php artisan key:generate --force 2>/dev/null; then
  warn "Application key generation failed. This may be normal if key already exists."
fi

info "Setting permissions"
# Directories Laravel needs to write to
for dir in storage bootstrap/cache; do
  mkdir -p "$dir"
  if [ ! -w "$dir" ]; then
    echo "  ${DIM}Fixing directory permissions for $dir${RESET}"
    chmod -R ug+rwX "$dir" || true
  fi
done

# App env file should not be world-readable
if [ -f .env ]; then
  chmod 600 .env || true
fi

info "Ensuring SQLite database file exists at database/database.sqlite"
mkdir -p database
if [ ! -f database/database.sqlite ]; then
  : > database/database.sqlite
fi

# SQLite DB should be writable by user and group
if [ -f database/database.sqlite ]; then
  chmod 664 database/database.sqlite || true
fi

info "Initializing git submodules"
git submodule sync --quiet
git submodule update --init --recursive
if [ $? -eq 0 ]; then
  success "Git submodules initialized successfully"
else
  error "Failed to initialize git submodules"
  exit 1
fi

info "Installing PHP dependencies (composer install)"
if [ "$CLEAN_DEPS" -eq 1 ] && [ -d vendor ]; then
  echo "  ${DIM}Removing existing vendor directory${RESET}"
  rm -rf vendor
fi
if [ "$IS_PROD" -eq 1 ]; then
  echo "  ${DIM}Production mode: Installing without dev dependencies${RESET}"
  composer install --no-dev --classmap-authoritative --no-interaction --prefer-dist --ansi
else
  echo "  ${DIM}Development mode: Installing all dependencies${RESET}"
  composer install --no-interaction --prefer-dist --ansi
fi
if [ $? -eq 0 ]; then
  success "PHP dependencies installed successfully"
else
  error "Failed to install PHP dependencies"
  exit 1
fi

info "Installing Node dependencies (npm)"
if [ "$CLEAN_DEPS" -eq 1 ] && [ -d node_modules ]; then
  echo "  ${DIM}Removing existing node_modules directory${RESET}"
  rm -rf node_modules
fi
if [ "$IS_PROD" -eq 1 ]; then
  echo "  ${DIM}Production mode: Installing without dev dependencies${RESET}"
  if [ -f package-lock.json ]; then
    npm ci --omit=dev
  else
    npm i --omit=dev
  fi
else
  echo "  ${DIM}Development mode: Installing all dependencies${RESET}"
  npm i
fi
if [ $? -eq 0 ]; then
  success "Node dependencies installed successfully"
else
  error "Failed to install Node dependencies"
  exit 1
fi

info "Updating Node dependencies (npm update)"
if [ "$IS_PROD" -eq 1 ]; then
  npm update --omit=dev || true
else
  npm update || true
fi

info "Auditing and fixing npm vulnerabilities"
if [ "$IS_PROD" -eq 1 ]; then
  npm audit fix --force --omit=dev || true
else
  npm audit fix --force || true
fi

echo
info "Optional developer tooling"

# Telescope prompt (default: skip in both dev and prod)
INSTALL_TELESCOPE=0
if [ -n "$TELESCOPE_FLAG" ]; then
  [ "$TELESCOPE_FLAG" = "yes" ] && INSTALL_TELESCOPE=1 || INSTALL_TELESCOPE=0
else
  if [ "$IS_PROD" -eq 1 ]; then
    if ask_yes_no "Install Laravel Telescope? [y/N]" "n"; then INSTALL_TELESCOPE=1; fi
  else
    if ask_yes_no "Install Laravel Telescope? [y/N]" "n"; then INSTALL_TELESCOPE=1; fi
  fi
fi

if [ "$INSTALL_TELESCOPE" -eq 1 ]; then
  info "Installing Laravel Telescope (dev dependency)"
  if composer require laravel/telescope --dev --no-interaction --ansi; then
    php artisan telescope:install || true
    success "Laravel Telescope installed"
    if [ "$IS_PROD" -eq 1 ]; then
      warn "Remember to restrict Telescope in production (gate or env)."
    fi
  else
    warn "Failed to install Laravel Telescope. Continuing..."
  fi
fi

# Nightwatch prompt (default: install in dev mode, skip in prod)
INSTALL_NIGHTWATCH=0
if [ -n "$NIGHTWATCH_FLAG" ]; then
  [ "$NIGHTWATCH_FLAG" = "yes" ] && INSTALL_NIGHTWATCH=1 || INSTALL_NIGHTWATCH=0
else
  if [ "$IS_PROD" -eq 1 ]; then
    if ask_yes_no "Install Nightwatch for E2E tests? [y/N]" "n"; then INSTALL_NIGHTWATCH=1; fi
  else
    if ask_yes_no "Install Nightwatch for E2E tests? [Y/n]" "y"; then INSTALL_NIGHTWATCH=1; fi
  fi
fi

if [ "$INSTALL_NIGHTWATCH" -eq 1 ]; then
  info "Installing Nightwatch (dev)"
  if npm i -D nightwatch chromedriver; then
    if npx nightwatch --init 2>/dev/null || npx nightwatch --generate-config 2>/dev/null; then
      success "Nightwatch installed and configured"
      echo "  ${DIM}Review generated config before running tests${RESET}"
    else
      success "Nightwatch installed (config generation skipped)"
    fi
  else
    warn "Failed to install Nightwatch. Continuing..."
  fi
fi

info "Validating database connection"
# For SQLite, check if file exists and is writable
if grep -q "DB_CONNECTION=sqlite" .env 2>/dev/null || [ -f "database/database.sqlite" ]; then
  if [ -f "database/database.sqlite" ] && [ -w "database/database.sqlite" ]; then
    echo "  ${DIM}SQLite database file is accessible${RESET}"
  else
    warn "SQLite database file may not be writable. Continuing..."
  fi
elif php artisan db:show --quiet 2>/dev/null; then
  echo "  ${DIM}Database connection successful${RESET}"
else
  warn "Database connection validation skipped (may require manual configuration)"
fi

info "Running migrations with seeders"
if [ "$DO_FRESH" -eq 1 ]; then
  echo "  ${DIM}Using: php artisan migrate:fresh --seed --force${RESET}"
  php artisan migrate:fresh --seed --force
else
  echo "  ${DIM}Using: php artisan migrate --seed --force${RESET}"
  php artisan migrate --seed --force
fi

info "Updating boost files"
php artisan boost:update || true

info "Building frontend (npm run build)"
if npm run build; then
  success "Frontend build completed successfully"
else
  error "Frontend build failed"
  exit 1
fi

if [ "$IS_PROD" -eq 1 ]; then
  info "Optimizing application for production"
  php artisan config:cache --force || true
  php artisan route:cache --force || true
  php artisan view:cache --force || true
  php artisan event:cache --force || true
  php artisan optimize --force || true
  success "Production optimizations complete"
else
  info "Development mode selected"
  if grep -q '"dev"\s*:' composer.json 2>/dev/null; then
    echo "  ${DIM}Note: Run 'composer run dev' to start the development server${RESET}"
  else
    echo "  ${DIM}No composer 'dev' script found in composer.json. Skipping.${RESET}"
  fi
fi

echo
info "Verifying installation"
VERIFY_FAILED=0

# Check if key exists
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  warn "Application key may not be set correctly"
  VERIFY_FAILED=1
fi

# Check if vendor exists
if [ ! -d "vendor" ]; then
  error "Vendor directory not found"
  VERIFY_FAILED=1
fi

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
  error "node_modules directory not found"
  VERIFY_FAILED=1
fi

# Check if build assets exist
if [ ! -d "public/build" ] && [ ! -f "public/hot" ]; then
  warn "Frontend assets not built. Run 'npm run build' if needed."
fi

# Test basic Laravel command
if php artisan --version >/dev/null 2>&1; then
  echo "  ${DIM}Laravel is working correctly${RESET}"
else
  error "Laravel artisan command failed"
  VERIFY_FAILED=1
fi

if [ "$VERIFY_FAILED" -eq 0 ]; then
  success "Installation verification passed"
else
  warn "Some verification checks failed. Please review the installation."
fi

echo
success "Installation complete!"
if [ "$IS_PROD" -eq 1 ]; then
  echo "  ${DIM}Production mode: Application is optimized and ready to deploy${RESET}"
else
  echo "  ${DIM}Development mode: Run 'composer run dev' to start the development server${RESET}"
fi


