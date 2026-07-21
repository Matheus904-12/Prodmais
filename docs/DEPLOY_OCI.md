# Deploy no OCI Always Free — Guia Completo

Rota recomendada: gratuita para sempre (não é trial), sem limite de uso/pausa
como Railway ou Render. Usa a VM ARM Ampere A1 (4 vCPU, 24GB RAM) do tier
Always Free da Oracle Cloud.

## 1. Criar a VM na OCI

1. Crie uma conta em [cloud.oracle.com](https://cloud.oracle.com) (cartão é pedido só pra verificação, não é cobrado no tier Always Free)
2. **Compute → Instances → Create Instance**
3. Imagem: **Ubuntu 22.04** (ARM)
4. Shape: **VM.Standard.A1.Flex** — 4 OCPUs, 24GB RAM (Always Free)
5. Adicione sua chave SSH pública
6. Em **Networking**, confirme que a VM recebe um IP público

## 2. Abrir as portas no Security List da OCI

Na VCN da instância, **Security Lists → Default Security List → Add Ingress Rules**:
- Porta 80 (HTTP), origem `0.0.0.0/0`
- Porta 443 (HTTPS), origem `0.0.0.0/0`
- Porta 22 (SSH) já vem liberada por padrão

Sem isso, o firewall do `ufw` dentro da VM não é suficiente — a OCI bloqueia por fora.

## 3. Rodar o script de deploy

Conecte via SSH e rode como root:

```bash
ssh ubuntu@SEU_IP_PUBLICO
sudo su
curl -fsSL https://raw.githubusercontent.com/Matheus904-12/Prodmais/main/deploy-oci.sh -o deploy-oci.sh
bash deploy-oci.sh                    # sem domínio, acesso via HTTP no IP
# OU, se já tiver domínio apontado pro IP da VM:
bash deploy-oci.sh seu-dominio.com.br # HTTPS automático via Let's Encrypt
```

O script instala Docker, clona o repositório, gera senhas aleatórias para o
MySQL, sobe todos os containers (`web`, `db`, `elasticsearch`, `caddy`) e cria
o primeiro usuário admin — a senha gerada aparece **uma única vez** no final
da execução, anote imediatamente.

### Banco de dados gerenciado externo (recomendado para produção real)

Por padrão o script sobe um MySQL local em container — bom pra testar rápido,
mas sem backup automático nem alta disponibilidade. Para produção com dados
reais de pesquisadores, use um banco gerenciado (ex: OCI MySQL Database
Service, PlanetScale, DigitalOcean Managed MySQL) e passe as credenciais
antes de rodar o script — o container local de MySQL nem sobe:

```bash
sudo EXTERNAL_DB_HOST=seu-host.mysql.dbaas.com.br \
     EXTERNAL_DB_USER=prodmais \
     EXTERNAL_DB_PASS=sua_senha_segura \
     EXTERNAL_DB_NAME=prodmais_umc \
     bash deploy-oci.sh seu-dominio.com.br
```

O script importa `sql/schema.sql` e `sql/schema_auth.sql` direto no host
externo antes de criar o admin.

## 4. Apontar o domínio (opcional, recomendado)

No provedor de DNS do seu domínio, crie um registro **A** apontando para o IP
público da VM. Depois rode o script passando o domínio (passo 3) — o Caddy
detecta automaticamente e emite o certificado HTTPS via Let's Encrypt.

## 5. Validar

```bash
curl https://seu-dominio.com.br/api/health.php   # ou http://IP/api/health.php sem domínio
```

Resposta esperada: `{"status":"healthy", ...}`.

## Manutenção

**Atualizar após novo push no `main`:**
```bash
cd /opt/prodmais
git pull origin main
docker compose -f docker-compose.prod.yml up -d --build
```

**Ver logs:**
```bash
docker compose -f docker-compose.prod.yml logs -f web
```

**Backup do banco:**
```bash
docker compose -f docker-compose.prod.yml exec db \
  mysqldump -u root -p"$MYSQL_ROOT_PASS" prodmais_umc > backup-$(date +%F).sql
```

**Rotacionar a senha de um admin existente** (sem re-rodar o script inteiro):
```bash
docker compose -f docker-compose.prod.yml exec -T \
  -e ADMIN_USERNAME=admin -e ADMIN_EMAIL=admin@umc.br \
  -e ADMIN_PASSWORD='NovaSenhaForte123!' -e ADMIN_NOME='Administrador' \
  web php bin/criar_admin.php
```
(isso cria um admin novo se o username/email não existir; para trocar a senha
de um usuário já existente, atualize `password_hash` diretamente no banco com
`password_hash()` do PHP, ou implemente a tela de "esqueci minha senha" já
existente no sistema.)
