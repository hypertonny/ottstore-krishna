#!/bin/bash
# ==============================================================================
# Blue-Green Zero-Downtime Deployment & Automated Rollback Engine
# ==============================================================================
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

SLOT_FILE="$PROJECT_DIR/active_slot.txt"
UPSTREAM_FILE="$PROJECT_DIR/nginx/active_upstream.conf"

# Read active slot (default to blue)
if [ -f "$SLOT_FILE" ]; then
    ACTIVE_SLOT=$(cat "$SLOT_FILE" | tr -d '[:space:]')
else
    ACTIVE_SLOT="blue"
fi

if [ "$ACTIVE_SLOT" == "blue" ]; then
    TARGET_SLOT="green"
else
    TARGET_SLOT="blue"
fi

TARGET_SERVICE="web_${TARGET_SLOT}"
TARGET_CONTAINER="ott_web_${TARGET_SLOT}"
ACTIVE_SERVICE="web_${ACTIVE_SLOT}"
ACTIVE_CONTAINER="ott_web_${ACTIVE_SLOT}"

echo "======================================================================"
echo "🚀 INITIATING ZERO-DOWNTIME BLUE-GREEN DEPLOYMENT"
echo "Active Slot:   [$ACTIVE_SLOT] ($ACTIVE_CONTAINER)"
echo "Target Slot:   [$TARGET_SLOT] ($TARGET_CONTAINER)"
echo "Timestamp:     $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
echo "======================================================================"

# Step 1: Ensure Nginx and Database are running
echo "--- [1/5] Ensuring Core Infrastructure (Nginx & DB) are active ---"
docker compose up -d db nginx

# Step 2: Build and start candidate container without affecting active traffic
echo "--- [2/5] Building and starting candidate container: $TARGET_SERVICE ---"
docker compose build "$TARGET_SERVICE"
docker compose up -d --no-deps "$TARGET_SERVICE"

# Step 3: Run comprehensive automated health checks on candidate container
echo "--- [3/5] Executing health checks on $TARGET_CONTAINER ---"
HEALTHY=0
for i in {1..20}; do
    STATUS=$(docker inspect --format='{{.State.Health.Status}}' "$TARGET_CONTAINER" 2>/dev/null || echo "starting")
    echo "Health check attempt $i/20: Status = $STATUS"
    if [ "$STATUS" == "healthy" ]; then
        # Additional functional verification: test homepage and DB query response
        HOME_RESP=$(docker exec "$TARGET_CONTAINER" curl -s -f http://localhost/ 2>/dev/null || echo "")
        ADMIN_CODE=$(docker exec "$TARGET_CONTAINER" curl -s -o /dev/null -w "%{http_code}" http://localhost/admin/ 2>/dev/null || echo "000")
        
        if [[ "$HOME_RESP" =~ "OTT STORE" ]] && [[ "$ADMIN_CODE" == "200" ]]; then
            echo "✅ Functional verification passed: Storefront & Admin are operational."
            HEALTHY=1
            break
        else
            echo "⚠️ HTTP responds but functional markers not yet ready..."
        fi
    fi
    sleep 2
done

# Step 4: Decision Gate (Atomic Switchover or Automated Rollback)
if [ "$HEALTHY" -eq 1 ]; then
    echo "--- [4/5] Health checks PASSED. Switching traffic to $TARGET_SLOT ---"
    
    # Update active upstream pointer
    echo "server ${TARGET_SERVICE}:80;" > "$UPSTREAM_FILE"
    
    # Reload Nginx configuration instantaneously (0 downtime hot-reload)
    docker exec ott_nginx nginx -s reload
    
    echo "Traffic atomically routed to $TARGET_SLOT."
    sleep 3
    
    # Step 5: Gracefully shut down previous slot
    echo "--- [5/5] Gracefully stopping previous container: $ACTIVE_CONTAINER ---"
    docker compose stop "$ACTIVE_SERVICE" || true
    
    # Save new active slot
    echo "$TARGET_SLOT" > "$SLOT_FILE"
    
    echo "======================================================================"
    echo "🎉 DEPLOYMENT SUCCESSFUL!"
    echo "Production is now running on: [$TARGET_SLOT]"
    echo "======================================================================"
    exit 0
else
    echo "======================================================================"
    echo "❌ HEALTH CHECKS FAILED ON [$TARGET_SLOT]! INITIATING AUTOMATIC ROLLBACK"
    echo "======================================================================"
    
    # Stop failing candidate container
    echo "Stopping failed candidate container: $TARGET_CONTAINER..."
    docker compose stop "$TARGET_SERVICE" || true
    
    # Active slot remains untouched in Nginx
    echo "Production traffic remains safe on active slot: [$ACTIVE_SLOT]"
    echo "Zero downtime was experienced by visitors."
    exit 1
fi
