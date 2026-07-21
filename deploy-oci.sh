#!/bin/bash
# ==========================================================
# PRODMAIS UMC - Deploy automático em OCI Always Free
# Execute como root ou com sudo em Ubuntu 22.04 ARM
# Uso: sudo bash deploy-oci.sh [dominio.com.br]
#      Sem domínio, o site fica disponível via HTTP no IP do servidor.
# ==========================================================

set -e
REPO_URL="https://github.com/Matheus904-12/Prodmais.git"
APP_DIR="/opt/prodmais"
COMPOSE_FILE="docker-compose.prod.yml"
DOMAIN="${1:-}"

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
    MYSQL_ROOT_PASS_GEN=$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 24)
    MYSQL_PASS_GEN=$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 24)
    cat > .env << ENVEOF
# Prodmais UMC - Produção OCI (gerado em $(date -Iseconds))
MYSQL_ROOT_PASS=${MYSQL_ROOT_PASS_GEN}
MYSQL_DB=prodmais_umc
MYSQL_USER=prodmais_admin
MYSQL_PASS=${MYSQL_PASS_GEN}
ELASTICSEARCH_HOST=elasticsearch:9200
APP_ENV=production
APP_DEBUG=false
APP_URL=${DOMAIN:+https://$DOMAIN}
SESSION_SECURE=${DOMAIN:+true}
DOMAIN=${DOMAIN:-localhost}
ENVEOF
    chmod 600 .env
    ok "Arquivo .env criado com senhas geradas aleatoriamente (guarde uma cópia segura)"
else
    ok "Arquivo .env já existe — mantendo como está"
fi
# shellcheck disable=SC1091
set -a; source .env; set +a

log "Configurando firewall..."
if command -v ufw &>/dev/null; then
    ufw allow 22/tcp  comment "SSH"   2>/dev/null || true
    ufw allow 80/tcp  comment "HTTP"  2>/dev/null || true
    ufw allow 443/tcp comment "HTTPS" 2>/dev/null || true
    ufw --force enable 2>/dev/null || true
    ok "Firewall configurado (80/443 via Caddy, 8080 não é mais exposto)"
fi

log "Subindo containers Docker..."
docker compose -f "$COMPOSE_FILE" pull --quiet
docker compose -f "$COMPOSE_FILE" up -d --build

log "Aguardando MySQL inicializar..."
until docker compose -f "$COMPOSE_FILE" exec -T db mysqladmin ping -h localhost --silent 2>/dev/null; do
    printf '.'
    sleep 3
done
echo ""
ok "MySQL pronto"

log "Criando usuário administrador..."
if [ -z "$ADMIN_PASSWORD" ]; then
    ADMIN_PASSWORD=$(openssl rand -base64 18 | tr -dc 'A-Za-z0-9' | head -c 18)
fi
ADMIN_USERNAME="${ADMIN_USERNAME:-admin}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@umc.br}"
ADMIN_NOME="${ADMIN_NOME:-Administrador Prodmais}"
docker compose -f "$COMPOSE_FILE" exec -T \
    -e ADMIN_USERNAME="$ADMIN_USERNAME" -e ADMIN_EMAIL="$ADMIN_EMAIL" \
    -e ADMIN_PASSWORD="$ADMIN_PASSWORD" -e ADMIN_NOME="$ADMIN_NOME" \
    web php bin/criar_admin.php || echo "  (usuário provavelmente já existe — ok se for um redeploy)"

log "Verificando saúde da aplicação..."
sleep 5
HEALTH_URL="http://localhost/api/health.php"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: ${DOMAIN:-localhost}" "$HEALTH_URL" 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    ok "Aplicação respondendo"
else
    echo "Aguardando aplicação (código HTTP: $HTTP_CODE)..."
    sleep 10
fi

PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || echo "SEU_IP")
ACCESS_URL="${DOMAIN:+https://$DOMAIN}"
ACCESS_URL="${ACCESS_URL:-http://$PUBLIC_IP}"

echo ""
echo "=============================================="
echo "   PRODMAIS UMC INSTALADO COM SUCESSO!"
echo "=============================================="
echo ""
echo "  Acesso: $ACCESS_URL"
echo "  Health: $ACCESS_URL/api/health.php"
echo "  Login:  $ACCESS_URL/login.php"
echo ""
echo "  Usuário admin: $ADMIN_USERNAME"
echo "  Senha:         $ADMIN_PASSWORD"
echo "  >>> ANOTE ESSA SENHA AGORA — ela não será exibida novamente <<<"
echo ""
if [ -z "$DOMAIN" ]; then
    echo "  Sem domínio configurado: acesso via HTTP simples (sem HTTPS)."
    echo "  Para HTTPS automático, aponte um domínio para $PUBLIC_IP e rode:"
    echo "  cd $APP_DIR && DOMAIN=seu-dominio.com.br docker compose -f $COMPOSE_FILE up -d"
fi
echo ""
echo "  Para ver logs:"
echo "  cd $APP_DIR && docker compose -f $COMPOSE_FILE logs -f web"
echo ""
echo "  Para parar:"
echo "  cd $APP_DIR && docker compose -f $COMPOSE_FILE down"
echo "=============================================="
