#!/usr/bin/env bash
# Initialize this repository after cloning from the GitHub template: replace
# your-vendor, saloon-api-sdk-boilerplate, class names, env prefix, and base URL.
# Then rename connector/sdk/provider/config files to match.
#
# Non-interactive (e.g. CI): set COMPOSER_VENDOR, PACKAGE_SLUG, SHORT_PREFIX,
# ENV_PREFIX, DEFAULT_BASE_URL and run without a TTY.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

DRY_RUN=0
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN=1
fi

die() {
  echo "error: $*" >&2
  exit 1
}

if ! grep -q 'your-vendor/saloon-api-sdk-boilerplate' composer.json 2>/dev/null; then
  die "This tree does not look like the boilerplate (missing your-vendor/saloon-api-sdk-boilerplate in composer.json). Refusing to run twice."
fi

# kebab-case / slug → PascalCase (e.g. hubspot-api → HubspotApi)
pascal_from_slug() {
  perl -e 'print join "", map {ucfirst lc} split /[-_]+/, shift' -- "${1:-}"
}

COMPOSER_VENDOR="${COMPOSER_VENDOR:-}"
PACKAGE_SLUG="${PACKAGE_SLUG:-}"
SHORT_PREFIX="${SHORT_PREFIX:-}"
ENV_PREFIX="${ENV_PREFIX:-}"
# Do not default DEFAULT_BASE_URL before prompts — a preset skips the prompt.
DEFAULT_BASE_URL="${DEFAULT_BASE_URL:-}"

prompt() {
  local var="$1"
  local text="$2"
  if [[ -z "${!var:-}" ]]; then
    read -r -p "$text: " "$var"
  fi
}

if [[ "$DRY_RUN" -eq 0 ]]; then
  prompt COMPOSER_VENDOR "Composer vendor (composer.json first segment, e.g. laravel-gtm)"
  prompt PACKAGE_SLUG "Composer package slug (second segment, e.g. hubspot-sdk — replaces saloon-api-sdk-boilerplate everywhere)"
  prompt SHORT_PREFIX "Short class prefix, PascalCase (e.g. Hubspot — Sdk/Connector/ServiceProvider are added for you)"
  prompt ENV_PREFIX "Env var prefix (e.g. HUBSPOT_API for HUBSPOT_API_BASE_URL)"
  prompt DEFAULT_BASE_URL "Default API base URL (e.g. https://api.example.com)"
fi

if [[ "$DRY_RUN" -eq 1 ]]; then
  COMPOSER_VENDOR="laravel-gtm"
  PACKAGE_SLUG="demo-sdk"
  SHORT_PREFIX="Demo"
  ENV_PREFIX="DEMO_API"
  DEFAULT_BASE_URL="https://api.demo.test"
fi

# Blank interactive answer or non-interactive without env: use placeholder for replacements.
DEFAULT_BASE_URL="${DEFAULT_BASE_URL:-https://api.example.com}"

[[ -n "$COMPOSER_VENDOR" ]] || die "COMPOSER_VENDOR is required"
[[ -n "$PACKAGE_SLUG" ]] || die "PACKAGE_SLUG is required"
[[ -n "$SHORT_PREFIX" ]] || die "SHORT_PREFIX is required"
[[ -n "$ENV_PREFIX" ]] || die "ENV_PREFIX is required"

if [[ ! "$COMPOSER_VENDOR" =~ ^[a-z0-9][a-z0-9-]*[a-z0-9]$ ]] && [[ ! "$COMPOSER_VENDOR" =~ ^[a-z0-9]$ ]]; then
  die "COMPOSER_VENDOR must be lowercase letters, digits, and hyphens (e.g. laravel-gtm), got: $COMPOSER_VENDOR"
fi

if [[ ! "$PACKAGE_SLUG" =~ ^[a-z0-9][a-z0-9-]*[a-z0-9]$ ]] && [[ ! "$PACKAGE_SLUG" =~ ^[a-z0-9]$ ]]; then
  die "PACKAGE_SLUG must be lowercase letters, digits, and hyphens (e.g. hubspot-sdk), got: $PACKAGE_SLUG"
fi

if [[ ! "$SHORT_PREFIX" =~ ^[A-Z][a-zA-Z0-9]*$ ]]; then
  die "SHORT_PREFIX must start with a letter and be alphanumeric (PascalCase), got: $SHORT_PREFIX"
fi

PASCAL_VENDOR="$(pascal_from_slug "$COMPOSER_VENDOR")"
NAMESPACE="${PASCAL_VENDOR}\\${SHORT_PREFIX}Sdk"

SERVICE_PROVIDER_CLASS="${SHORT_PREFIX}ServiceProvider"
CONNECTOR_CLASS="${SHORT_PREFIX}Connector"
SDK_CLASS="${SHORT_PREFIX}Sdk"

export INIT_SERVICE_PROVIDER="$SERVICE_PROVIDER_CLASS"
export INIT_SDK="$SDK_CLASS"
export INIT_CONNECTOR="$CONNECTOR_CLASS"
export INIT_NAMESPACE="$NAMESPACE"
export INIT_COMPOSER_VENDOR="$COMPOSER_VENDOR"
export INIT_PACKAGE_SLUG="$PACKAGE_SLUG"
export INIT_ENV_PREFIX="$ENV_PREFIX"
export INIT_BASE_URL="$DEFAULT_BASE_URL"

# JSON strings need \\ for each namespace separator in composer.json (PSR-4, providers).
INIT_NAMESPACE_JSON="$(printf '%s' "$NAMESPACE" | perl -pe 's/\\/\\\\/g')"
export INIT_NAMESPACE_JSON

replace_in_files() {
  local f
  while IFS= read -r -d '' f; do
    if [[ "$DRY_RUN" -eq 1 ]]; then
      echo "would patch: $f"
      continue
    fi
    perl -pi -e '
      BEGIN {
        our $sp = $ENV{INIT_SERVICE_PROVIDER};
        our $sdk = $ENV{INIT_SDK};
        our $conn = $ENV{INIT_CONNECTOR};
        our $ns = $ENV{INIT_NAMESPACE};
        our $ns_json = $ENV{INIT_NAMESPACE_JSON};
        our $cv = $ENV{INIT_COMPOSER_VENDOR};
        our $slug = $ENV{INIT_PACKAGE_SLUG};
        our $envp = $ENV{INIT_ENV_PREFIX};
        our $url = $ENV{INIT_BASE_URL};
      }
      s/SaloonApiSdkServiceProvider/$sp/g;
      s/YourVendor\\\\SaloonApiSdk/$ns_json/g;
      s/YourVendor\\SaloonApiSdk/$ns/g;
      s/SaloonApiSdk/$sdk/g;
      s/SaloonConnector/$conn/g;
      s/your-vendor/$cv/g;
      s/saloon-api-sdk-boilerplate/$slug/g;
      s/SALOON_API_SDK_/${envp}_/g;
      s#https://api\.example\.com#$url#g;
    ' "$f"
  done < <(find . -type f \
    ! -path './vendor/*' \
    ! -path './.git/*' \
    ! -path './composer.lock' \
    ! -path './init-saloon-sdk.sh' \
    \( -name '*.php' -o -name '*.json' -o -name '*.md' -o -name '*.blade.php' -o -name '*.yml' -o -name '*.yaml' -o -name '.gitattributes' -o -name '.gitignore' \) -print0)
}

rename_paths() {
  local -a moves=(
    "src/SaloonConnector.php:src/${CONNECTOR_CLASS}.php"
    "src/SaloonApiSdk.php:src/${SDK_CLASS}.php"
    "src/Laravel/SaloonApiSdkServiceProvider.php:src/Laravel/${SERVICE_PROVIDER_CLASS}.php"
    "config/saloon-api-sdk-boilerplate.php:config/${PACKAGE_SLUG}.php"
    "tests/Unit/SaloonConnectorTest.php:tests/Unit/${CONNECTOR_CLASS}Test.php"
    "tests/Unit/SaloonApiSdkTest.php:tests/Unit/${SDK_CLASS}Test.php"
    "resources/boost/skills/saloon-api-sdk-boilerplate-development:resources/boost/skills/${PACKAGE_SLUG}-development"
  )
  local pair src dst
  for pair in "${moves[@]}"; do
    src="${pair%%:*}"
    dst="${pair##*:}"
    if [[ "$DRY_RUN" -eq 1 ]]; then
      echo "would mv: $src -> $dst"
      continue
    fi
    [[ -e "$src" ]] || die "missing path: $src"
    mkdir -p "$(dirname "$dst")"
    if [[ -d .git ]] && git ls-files --error-unmatch "$src" &>/dev/null; then
      git mv -f "$src" "$dst"
    else
      mv "$src" "$dst"
    fi
  done
}

echo "==> Replacing boilerplate strings"
replace_in_files

echo "==> Renaming files"
rename_paths

if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "Dry run complete (no changes made)."
  printf 'Derived PHP root namespace: %s\n' "$NAMESPACE"
  echo "composer.json JSON-escapes backslashes in namespace strings; the script writes the correct doubling."
  exit 0
fi

echo "==> Refreshing Composer lock and autoload"
rm -f composer.lock
composer install --no-interaction --no-progress

echo "==> Running checks"
composer validate --strict
composer test
composer analyse
composer format
composer lint

echo ""
echo "Done. Review git diff, update README badges, and commit."
rm -f "${ROOT_DIR}/init-saloon-sdk.sh"
echo "Removed init-saloon-sdk.sh (one-time use)."
