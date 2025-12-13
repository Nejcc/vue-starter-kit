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

trap 'error "Installation failed."' ERR

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
command -v php >/dev/null || { echo "php not found" >&2; exit 1; }
command -v composer >/dev/null || { echo "composer not found" >&2; exit 1; }
command -v npm >/dev/null || { echo "npm not found" >&2; exit 1; }

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

info "Preparing environment file"
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
  else
    echo ".env and .env.example not found. Aborting." >&2
    exit 1
  fi
fi

info "Ensuring SQLite database file exists at database/database.sqlite"
mkdir -p database
if [ ! -f database/database.sqlite ]; then
  : > database/database.sqlite
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

# SQLite DB should be writable by user and group
if [ -f database/database.sqlite ]; then
  chmod 664 database/database.sqlite || true
fi

info "Installing PHP dependencies (composer install)"
if [ "$CLEAN_DEPS" -eq 1 ] && [ -d vendor ]; then
  echo "  ${DIM}Removing existing vendor directory${RESET}"
  rm -rf vendor
fi
if [ "$IS_PROD" -eq 1 ]; then
  composer install --no-dev --classmap-authoritative --no-interaction --prefer-dist --ansi
else
  composer install --no-interaction --prefer-dist --ansi
fi

info "Installing Node dependencies (npm)"
if [ "$CLEAN_DEPS" -eq 1 ] && [ -d node_modules ]; then
  echo "  ${DIM}Removing existing node_modules directory${RESET}"
  rm -rf node_modules
fi
if [ "$IS_PROD" -eq 1 ]; then
  if [ -f package-lock.json ]; then
    npm ci --omit=dev
  else
    npm i --omit=dev
  fi
else
  npm i
fi

info "Auditing and fixing npm vulnerabilities"
if [ "$IS_PROD" -eq 1 ]; then
  npm audit fix --omit=dev || true
else
  npm audit fix || true
fi

info "Building frontend (npm run build)"
npm run build

info "Generating application key"
php artisan key:generate --force

info "Running migrations with seeders"
if [ "$DO_FRESH" -eq 1 ]; then
  echo "  ${DIM}Using: php artisan migrate:fresh --seed --force${RESET}"
  php artisan migrate:fresh --seed --force
else
  echo "  ${DIM}Using: php artisan migrate --seed --force${RESET}"
  php artisan migrate --seed --force
fi

info "Updating bootstrap files"
php artisan bootstrap:update --force || true

if [ "$IS_PROD" -eq 1 ]; then
  info "Optimizing application for production"
  php artisan optimize --force || true
else
  info "Development mode selected"
  if grep -q '"dev"\s*:' composer.json 2>/dev/null; then
    echo "  ${DIM}Running: composer dev${RESET}"
    composer run dev || composer dev || true
  else
    echo "  ${DIM}No composer 'dev' script found in composer.json. Skipping.${RESET}"
  fi
fi

echo
info "Optional developer tooling"

# Telescope prompt (default: install in dev mode, skip in prod)
INSTALL_TELESCOPE=0
if [ -n "$TELESCOPE_FLAG" ]; then
  [ "$TELESCOPE_FLAG" = "yes" ] && INSTALL_TELESCOPE=1 || INSTALL_TELESCOPE=0
else
  if [ "$IS_PROD" -eq 1 ]; then
    if ask_yes_no "Install Laravel Telescope? [y/N]" "n"; then INSTALL_TELESCOPE=1; fi
  else
    if ask_yes_no "Install Laravel Telescope? [Y/n]" "y"; then INSTALL_TELESCOPE=1; fi
  fi
fi

if [ "$INSTALL_TELESCOPE" -eq 1 ]; then
  info "Installing Laravel Telescope (dev dependency)"
  composer require laravel/telescope --dev --no-interaction --ansi || true
  php artisan telescope:install || true
  php artisan migrate --force || true
  warn "Remember to restrict Telescope in production (gate or env)."
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
  npm i -D nightwatch chromedriver || true
  npx nightwatch --init || npx nightwatch --generate-config || true
  success "Nightwatch installed. Review generated config before running tests."
fi

success "Install complete. You can now run the application."


