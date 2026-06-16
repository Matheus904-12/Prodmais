#!/bin/bash
set -e

# Criar diretórios de dados se não existirem
mkdir -p data/uploads data/cache data/logs data/lattes_xml data/backups

# Ajustar Document Root do Apache para /public
export APACHE_DOCUMENT_ROOT="${APACHE_DOCUMENT_ROOT:-/var/www/html/public}"

# Configurar porta (Railway e Render injetam $PORT)
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf 2>/dev/null || true
    sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-enabled/*.conf 2>/dev/null || true
fi

echo "Iniciando Apache (document root: $APACHE_DOCUMENT_ROOT)..."
exec apache2-foreground
