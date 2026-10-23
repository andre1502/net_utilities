#!/usr/bin/env bash
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
STANDALONE_DIR="$DIR/standalone-test"

echo "==> [Standalone Test] Preparing standalone Laravel environment..."

mkdir -p "$STANDALONE_DIR/bootstrap/cache"
mkdir -p "$STANDALONE_DIR/storage/framework/cache"
mkdir -p "$STANDALONE_DIR/storage/framework/sessions"
mkdir -p "$STANDALONE_DIR/storage/framework/views"
mkdir -p "$STANDALONE_DIR/storage/logs"

chmod +x "$STANDALONE_DIR/artisan"

if [ ! -f "$STANDALONE_DIR/.env" ]; then
    cat << 'EOF' > "$STANDALONE_DIR/.env"
APP_NAME=StandaloneTestApp
APP_ENV=testing
APP_KEY=base64:u4O+6yX0VjH87LpYtQJ3Z8l3sH1K1v0v3K5b7b9p1x8=
APP_DEBUG=true
APP_URL=http://localhost
NET_UTILITIES_JWT_SECRET=test-secret-at-least-32-chars-long-123456
NET_UTILITIES_INTERNAL_API_HEADER=X-Internal-Token
NET_UTILITIES_INTERNAL_API_TOKEN=standalone-secret-token
EOF
fi

if [ ! -d "$STANDALONE_DIR/vendor" ]; then
    echo "==> [Standalone Test] Installing standalone Laravel dependencies..."
    (cd "$STANDALONE_DIR" && composer install --no-interaction --prefer-dist)
fi

echo "==> [Standalone Test] Running standalone Laravel integration tests..."
(cd "$STANDALONE_DIR" && ./vendor/bin/pest "$@")
