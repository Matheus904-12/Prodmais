#!/bin/bash
# ==========================================================
# Retry automático de criação da instância OCI (VM.Standard.A1.Flex)
#
# Resolve o erro "Capacidade insuficiente para a forma
# VM.Standard.A1.Flex" — a Oracle libera capacidade Always Free
# de tempos em tempos; este script fica tentando até conseguir.
#
# COMO USAR (recomendado: OCI Cloud Shell — já vem autenticado,
# sem precisar configurar chave de API):
#   1. No console OCI, clique no ícone ">_" (Cloud Shell) no topo.
#   2. Cole este arquivo lá (ou copie o conteúdo pra um arquivo novo).
#   3. Preencha COMPARTMENT_ID e SUBNET_ID abaixo (veja onde achar
#      cada um logo depois desta seção de comentários).
#   4. bash retry-criar-instancia-oci.sh
#   5. Deixe rodando — ele tenta a cada 60s até conseguir criar.
#
# Onde achar os IDs que faltam preencher:
#   COMPARTMENT_ID: página inicial do console → clique no nome da
#     tenancy ("matheuslucindo904") no topo → copia o OCID mostrado
#     ("raiz"/root compartment).
#   SUBNET_ID: Networking → Virtual Cloud Networks → vcn-prodmais →
#     Sub-redes → clique em subnet-publica-prodmais → copia o OCID
#     na página de detalhes.
# ==========================================================

set -uo pipefail

# >>> PREENCHA ESTES DOIS VALORES ANTES DE RODAR <<<
COMPARTMENT_ID="ocid1.tenancy.oc1..SUBSTITUA_AQUI"
SUBNET_ID="ocid1.subnet.oc1.sa-saopaulo-1.SUBSTITUA_AQUI"

INSTANCE_NAME="prodmais-prod"
SHAPE="VM.Standard.A1.Flex"
OCPUS=2
MEMORY_GB=12
RETRY_INTERVAL_SECONDS=60

log()  { echo -e "\033[1;34m[$(date '+%H:%M:%S')]\033[0m $1"; }
ok()   { echo -e "\033[1;32m✓ $1\033[0m"; }
err()  { echo -e "\033[1;31m✗ $1\033[0m"; }

if [[ "$COMPARTMENT_ID" == *SUBSTITUA* || "$SUBNET_ID" == *SUBSTITUA* ]]; then
    err "Preencha COMPARTMENT_ID e SUBNET_ID no início do script antes de rodar."
    exit 1
fi

log "Buscando domínio de disponibilidade..."
AD=$(oci iam availability-domain list --compartment-id "$COMPARTMENT_ID" \
    --query 'data[0].name' --raw-output)
ok "Domínio de disponibilidade: $AD"

log "Buscando imagem Ubuntu 22.04 (ARM) mais recente..."
IMAGE_ID=$(oci compute image list --compartment-id "$COMPARTMENT_ID" \
    --operating-system "Canonical Ubuntu" \
    --operating-system-version "22.04" \
    --shape "$SHAPE" \
    --sort-by TIMECREATED --sort-order DESC \
    --query 'data[0].id' --raw-output)
ok "Imagem: $IMAGE_ID"

# Gera um par de chaves SSH novo aqui no Cloud Shell, se ainda não existir
SSH_KEY_PATH="$HOME/.ssh/prodmais_oci"
if [ ! -f "${SSH_KEY_PATH}.pub" ]; then
    log "Gerando par de chaves SSH em ${SSH_KEY_PATH}..."
    mkdir -p "$HOME/.ssh"
    ssh-keygen -t rsa -b 4096 -N "" -f "$SSH_KEY_PATH" >/dev/null
    ok "Chave gerada. A PRIVADA fica em ${SSH_KEY_PATH} — baixe pelo menu do Cloud Shell (⋮ → Download) antes de sair, ela não aparece de novo."
fi

ATTEMPT=0
while true; do
    ATTEMPT=$((ATTEMPT + 1))
    log "Tentativa #$ATTEMPT — criando instância..."

    OUTPUT=$(oci compute instance launch \
        --compartment-id "$COMPARTMENT_ID" \
        --availability-domain "$AD" \
        --shape "$SHAPE" \
        --shape-config "{\"ocpus\": $OCPUS, \"memoryInGBs\": $MEMORY_GB}" \
        --display-name "$INSTANCE_NAME" \
        --image-id "$IMAGE_ID" \
        --subnet-id "$SUBNET_ID" \
        --assign-public-ip true \
        --ssh-authorized-keys-file "${SSH_KEY_PATH}.pub" \
        --wait-for-state RUNNING \
        --max-wait-seconds 300 2>&1)
    STATUS=$?

    if [ $STATUS -eq 0 ]; then
        ok "Instância criada com sucesso!"
        PUBLIC_IP=$(echo "$OUTPUT" | grep -o '"public-ip": "[^"]*"' | head -1 | cut -d'"' -f4)
        echo ""
        echo "=============================================="
        echo "  IP público: ${PUBLIC_IP:-<ver no console, aba Instances>}"
        echo "  Chave privada: ${SSH_KEY_PATH} (baixe do Cloud Shell agora)"
        echo ""
        echo "  Conecte com:"
        echo "  ssh -i ${SSH_KEY_PATH} ubuntu@${PUBLIC_IP:-SEU_IP}"
        echo "=============================================="
        break
    fi

    if echo "$OUTPUT" | grep -qi "capacity\|OutOfHostCapacity\|LimitExceeded"; then
        err "Sem capacidade disponível ainda. Tentando de novo em ${RETRY_INTERVAL_SECONDS}s..."
        sleep "$RETRY_INTERVAL_SECONDS"
        continue
    fi

    err "Erro inesperado, não relacionado a capacidade — parando:"
    echo "$OUTPUT"
    exit 1
done
