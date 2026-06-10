#!/bin/bash
# ==========================================================
# PRODMAIS UMC - Deploy automático em OCI Always Free
# Execute como root ou com sudo em Ubuntu 22.04 ARM
# Uso: curl -fsSL <url-do-script> | sudo bash
#      OU: sudo bash deploy-oci.sh
# ==========================================================

set -e
REPO_URL="https://github.com/Matheus904-12/Prodmais.git"
APP_DIR="/opt/prodmais"
COMPOSE_FILE="docker-compose.prod.yml"

log() { echo -e "\n\033[1;34m>>> $1\033[0m"; }
ok()  { echo -e "\033[1;32m✓ $1\033[0m"; }
err() { echo -e "\033[1;31m✗ $1\033[0m"; exit 1; }

log "Atualizando sistema..."
apt-get update -qq && apt-get upgrade -y -qq

log "Instalando Docker..."
if ! command -v docker &>/dev/null; then
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
    usermod -aG docker ubuntu 2>/dev/null || true
    ok "Docker instalado"
else
    ok "Docker já instalado: $(docker --version)"
fi

log "Instalando Docker Compose v2..."
if ! docker compose version &>/dev/null; then
    apt-get install -y docker-compose-plugin -qq
fi
ok "Docker Compose: $(docker compose version)"

log "Instalando Git..."
apt-get install -y git curl -qq
ok "Git: $(git --version)"

log "Clonando repositório..."
if [ -d "$APP_DIR/.git" ]; then
    cd "$APP_DIR" && git pull origin main
    ok "Repositório atualizado"
else
    git clone "$REPO_URL" "$APP_DIR"
    ok "Repositório clonado em $APP_DIR"
fi

cd "$APP_DIR"

log "Criando diretórios de runtime..."
mkdir -p data/uploads data/logs data/cache data/lattes_xml data/backups
chmod -R 777 data/
ok "Diretórios criados"

log "Configurando variáveis de ambiente..."
if [ ! -f .env ]; then
    cat > .env << 'ENVEOF'
# Prodmais UMC - Produção OCI
MYSQL_ROOT_PASS=ProdmaisRoot@2025!
MYSQL_DB=prodmais_umc
MYSQL_USER=prodmais_admin
MYSQL_PASS=ProdmaisApp@2025!
ELASTICSEARCH_HOST=elasticsearch:9200
APP_ENV=production
APP_DEBUG=false
APP_URL=http://SEU_IP_OCI:8080
SESSION_SECURE=false
ENVEOF
    ok "Arquivo .env criado — edite APP_URL com seu IP público"
else
    ok "Arquivo .env já existe"
fi

log "Configurando firewall..."
if command -v ufw &>/dev/null; then
    ufw allow 22/tcp   comment "SSH" 2>/dev/null || true
    ufw allow 80/tcp   comment "HTTP" 2>/dev/null || true
    ufw allow 8080/tcp comment "Prodmais App" 2>/dev/null || true
    ufw allow 443/tcp  comment "HTTPS" 2>/dev/null || true
    ufw --force enable 2>/dev/null || true
    ok "Firewall configurado"
fi

log "Subindo containers Docker..."
docker compose -f "$COMPOSE_FILE" pull --quiet
docker compose -f "$COMPOSE_FILE" up -d

log "Aguardando MySQL inicializar..."
until docker compose -f "$COMPOSE_FILE" exec -T db mysqladmin ping -h localhost --silent 2>/dev/null; do
    printf '.'
    sleep 3
done
echo ""
ok "MySQL pronto"

log "Verificando saúde da aplicação..."
sleep 5
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/api/health.php 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    ok "Aplicação respondendo em http://localhost:8080"
else
    echo "Aguardando aplicação (código HTTP: $HTTP_CODE)..."
    sleep 10
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/api/health.php 2>/dev/null || echo "000")
fi

PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || echo "SEU_IP")

echo ""
echo "=============================================="
echo "   PRODMAIS UMC INSTALADO COM SUCESSO!"
echo "=============================================="
echo ""
echo "  Acesso: http://$PUBLIC_IP:8080"
echo "  Health: http://$PUBLIC_IP:8080/api/health.php"
echo "  Admin:  http://$PUBLIC_IP:8080/login.php"
echo ""
echo "  Login padrão: admin / Admin@2025"
echo "  ALTERE A SENHA APÓS O PRIMEIRO LOGIN!"
echo ""
echo "  Para ver logs:"
echo "  docker compose -f $COMPOSE_FILE logs -f web"
echo ""
echo "  Para parar:"
echo "  cd $APP_DIR && docker compose -f $COMPOSE_FILE down"
echo "=============================================="
