# Guia de Deploy: Oracle Cloud (OCI) + Docker

Este guia explica como subir o **Prodmais** no Oracle Cloud Infrastructure de forma gratuita e profissional.

## 💰 Custos (OCI Always Free)
O Oracle Cloud oferece o nível gratuito mais generoso do mercado:
*   **Processador ARM Ampere:** Até 4 OCPUs e 24 GB de RAM.
*   **Armazenamento:** 200 GB de disco.
*   **Tráfego:** 10 TB por mês.
> [!TIP]
> **Custo: R$ 0,00.** Perfeito para projetos universitários e de pesquisa.

---

## 🚀 Passo a Passo (Deploy em 5 Minutos)

### 1. Criar Instância
1.  No painel da OCI, vá em **Compute > Instances > Create Instance**.
2.  Escolha a imagem **Ubuntu 22.04**.
3.  Em "Shape", selecione **Ampere (ARM)** para aproveitar os 24GB de RAM.
4.  Baixe a chave SSH e crie a instância.

### 2. Configurar Rede
1.  Vá em **Virtual Cloud Networks > [Sua VCN] > Security Lists**.
2.  Adicione as seguintes regras de entrada (Ingress Rules):
    *   `8080` (Site Principal)
    *   `9200` (Elasticsearch - Opcional, mantenha fechado por segurança)
    *   `5601` (Kibana)

### 3. Instalar Docker na Instância
Conecte via SSH e rode:
```bash
sudo apt update && sudo apt install docker.io docker-compose -y
sudo systemctl enable --now docker
```

### 4. Subir o Projeto
```bash
git clone [URL_DO_REPOSITORIO]
cd Prodmais
docker-compose up -d --build
```

### 5. Configurar Firewall do Ubuntu
```bash
sudo ufw allow 8080/tcp
sudo ufw allow 5601/tcp
```

---

## 🌐 Acesso
Após o deploy, o sistema estará disponível no IP Público da sua instância:
`http://[IP_DA_INSTANCIA]:8080`
