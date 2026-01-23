#!/bin/bash
cd /var/www/jaycampbell.dev || exit 1
git pull origin main
npm run build || true

