#!/bin/sh
set -e

echo "Deploy webhook received"
# Implement webhook handling or trigger redeploy script
sh /usr/local/bin/redeploy.sh || true
