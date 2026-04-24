# 🌍 Guia de Hospedagem Docker - Prodmais

Este projeto está pronto para ser hospedado em qualquer provedor Cloud que suporte Docker (VPS, Railway, Render, Fly.io, Google Cloud, AWS).

## 🚀 Opção Recomendada: VPS (DigitalOcean / Linode / AWS EC2)

A forma mais robusta e econômica para o Prodmais (devido ao Elasticsearch).

### 1. Requisitos do Servidor
*   **Mínimo**: 4GB RAM (Elasticsearch precisa de pelo menos 1GB sozinho).
*   **SO**: Ubuntu 22.04 LTS + Docker + Docker Compose instalados.

### 2. Preparação para Produção
Crie um arquivo `docker-compose.prod.yml` no servidor (já incluído no repositório):

```bash
# No servidor
git clone https://github.com/Matheus904-12/Prodmais.git
cd Prodmais

# Subir em modo produção (sem volumes de bind, mais seguro e rápido)
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## 🛠️ Alternativas Cloud (PaaS)

### Railway.app (Facilidade máxima)
1. Conecte seu GitHub no [Railway](https://railway.app/).
2. Adicione os serviços:
   - **Web**: Apontando para o `Dockerfile`.
   - **MySQL**: Usando o plugin nativo do Railway.
   - **Elasticsearch**: Use uma imagem oficial ou o stack do Railway.
3. Configure as Variáveis de Ambiente no painel.

### Render.com
1. Crie um "Web Service" conectado ao repo.
2. O Render detectará automaticamente o `Dockerfile`.
3. Adicione um disco (Disk) para a pasta `/var/www/html/data` para não perder logs e bancos SQLite.

## 🔒 Segurança em Produção
*   **HTTPS**: Use Nginx Proxy Manager ou Traefik para certificados SSL automáticos.
*   **Elasticsearch**: No `docker-compose.yml`, nunca exponha a porta 9200 publicamente sem habilitar `xpack.security`.
*   **Passwords**: Altere todas as senhas padrão no arquivo `.env`.

---
*Guia criado para Matheus Lucindo dos Santos - Prodmais UMC*
