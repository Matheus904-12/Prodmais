# Guia de Transferência para a Locaweb — Prodmais UMC

Este guia existe porque a infraestrutura atual (AWS RDS + AWS OpenSearch + Render) está
provisionada na conta pessoal do desenvolvedor, e paga por ele. O plano é transferir o
Prodmais para a infraestrutura oficial da universidade: hospedagem na **Locaweb**, banco
de dados relacional (MySQL) também na Locaweb, e um domínio oficial da UMC a ser registrado.

**Este documento é um roteiro pra quando a migração for de fato executada — não é uma
mudança em curso.** Nada na aplicação foi alterado por causa dele. Ele existe pra reduzir
o trabalho de decisão no dia da migração: as perguntas em aberto já estão levantadas, os
passos já estão esboçados, falta só confirmar os detalhes específicos do plano contratado
e executar.

**Este arquivo não contém nenhuma credencial.** Quando a migração acontecer, credenciais
reais (senhas, hosts, IDs) devem ir em `CREDENCIAIS.html` na raiz do projeto — arquivo
local, `.gitignore`d, nunca sobe pro repositório — nunca neste guia.

> Existe também `docs/GUIA_TRANSFERENCIA_INFRAESTRUTURA.md`, escrito para o cenário
> alternativo de migrar pra uma conta **AWS institucional** (RDS + OpenSearch), mantendo
> Render ou outra hospedagem compatível com Docker. Os dois guias descrevem caminhos
> diferentes — use este aqui se a decisão for Locaweb, aquele se for AWS.

---

## 1. Visão geral do que precisa migrar

| Componente | Hoje | Depois |
|---|---|---|
| Banco relacional | AWS RDS MySQL, conta pessoal (us-east-2) | MySQL gerenciado pela Locaweb, conta da UMC |
| Motor de busca | AWS OpenSearch, conta pessoal (us-east-2) | Em aberto — ver seção 3 |
| Aplicação (PHP/Apache) | Web service no Render, conta pessoal | Hospedagem Locaweb |
| Domínio | Subdomínio gratuito `*.onrender.com` | Domínio oficial da UMC (a registrar) |
| Código-fonte | Repositório GitHub, organização `Prodmais-UMC` | Sem mudança — só o deploy muda de lugar |

---

## 2. Decisão que precisa ser tomada antes de tudo: qual plano da Locaweb?

Essa é a decisão que mais afeta o resto do guia, e por isso vem primeiro. A Locaweb vende
categorias de hospedagem bem diferentes entre si, e o Prodmais (PHP 8.2, extensões
específicas — `mbstring`, `curl`, `pdo_mysql`, `zip` —, possivelmente um processo à parte
pro motor de busca) só roda de verdade num dos dois caminhos abaixo:

- **Hospedagem compartilhada / cPanel** — a mais barata e mais comum pra sites institucionais.
  Normalmente não dá acesso root/SSH livre, não roda Docker, e a versão de PHP disponível é
  escolhida por painel (precisa confirmar se PHP 8.2 está disponível — hospedagens
  compartilhadas costumam atrasar versões novas do PHP). **Não dá pra rodar Elasticsearch
  aqui** — só serve se a decisão da seção 3 for "sem Elasticsearch" ou "Elasticsearch em
  outro lugar".
- **Cloud Server / VPS da Locaweb** — servidor com acesso root/SSH, onde dá pra instalar
  Docker e rodar a aplicação praticamente como ela já roda hoje (o `Dockerfile` do projeto
  serve quase sem alteração). Mais caro que hospedagem compartilhada, mas é o único caminho
  que permite manter a arquitetura atual (Docker + Elasticsearch próprio, se for essa a
  escolha).

**Ação antes de migrar:** confirmar com quem for contratar qual dessas categorias a UMC vai
comprar. Se for hospedagem compartilhada, a seção 3 só tem uma opção viável (sem
Elasticsearch próprio). Se for Cloud Server/VPS, todas as opções da seção 3 ficam abertas.

---

## 3. Decisão que precisa ser tomada: o que fazer com o Elasticsearch

A aplicação já foi construída prevendo que o Elasticsearch pode não estar disponível — existe
um fallback nativo para MySQL puro (`ElasticsearchService`, ligado/desligado pela variável de
ambiente `ES_USE`). Isso dá três caminhos possíveis, do mais simples ao mais complexo:

### Opção A — Desligar o Elasticsearch, rodar só com MySQL (mais simples)
Define `ES_USE=false` no `.env` de produção. A busca passa a rodar inteiramente via MySQL
(`fallback para MySQL`, já implementado). **Funciona em hospedagem compartilhada — não
precisa de VPS nem de Docker.** A perda é a qualidade da busca full-text (relevância,
tolerância a erro de digitação, agregações rápidas do dashboard) — o volume de dados do
Prodmais não é grande, então na prática pode ser um trade-off aceitável. **Recomendado como
primeira opção a avaliar**, por ser a que menos depende de decisões técnicas complicadas no
dia da migração.

### Opção B — Self-host do Elasticsearch num Cloud Server/VPS da Locaweb
Só é possível se a seção 2 tiver decidido por VPS. Sobe um container Elasticsearch (mesma
versão usada hoje em dev, `elasticsearch:8.10.4`, ver `docker-compose.yml`) na mesma máquina
ou numa instância separada, com autenticação habilitada (**diferente do `docker-compose.yml`
de desenvolvimento, que roda com `xpack.security.enabled=false` — em produção isso precisa
estar habilitado, com usuário/senha próprios**, ou pelo menos a porta 9200 restrita por
firewall só ao IP da aplicação). Mantém a experiência de busca idêntica à atual. Exige mais
manutenção (memória reservada — o container já usa `-Xms512m -Xmx512m` —, backups próprios,
atualização de versão por conta de quem administra o VPS).

### Opção C — Arquitetura híbrida: manter o AWS OpenSearch, mover só app + banco pra Locaweb
Só faz sentido se a instituição aceitar manter uma conta AWS (institucional, não mais
pessoal) só para o motor de busca, enquanto app e MySQL saem pra Locaweb. Evita ter que
administrar Elasticsearch na Locaweb, mas mantém uma dependência de duas infraestruturas
diferentes ao mesmo tempo (mais complexidade operacional, duas faturas, dois lugares pra
monitorar). Só recomendado se a Opção A não for aceitável e a Opção B não for viável (ex.:
ninguém na equipe quer administrar um Elasticsearch próprio).

**Ação antes de migrar:** decidir entre A/B/C com quem for pagar a conta. Isso trava a
decisão da seção 2 também (A permite hospedagem compartilhada; B exige VPS; C funciona nos
dois casos, já que o Elasticsearch nem mora na Locaweb).

---

## 4. Banco de dados MySQL na Locaweb

Independente do plano escolhido na seção 2, a Locaweb fornece bancos MySQL via painel
(cPanel ou painel próprio, dependendo do plano). Os passos gerais — confirme o caminho exato
no painel específico contratado, pode variar:

1. Criar o banco de dados MySQL pelo painel da Locaweb (nome sugerido: `prodmais_umc`, mas
   hospedagens compartilhadas costumam prefixar o nome com o usuário da conta — anote o nome
   real que o painel gerar).
2. Criar um usuário MySQL dedicado com permissão só nesse banco (não usar um usuário
   compartilhado com outros sites/bancos da mesma conta).
3. Anotar o **host** de conexão — em hospedagem compartilhada normalmente é `localhost` (o
   MySQL roda na mesma máquina do PHP); em VPS pode ser um host próprio se o banco estiver
   numa instância separada.
4. Importar o schema: `sql/schema.sql` e `sql/schema_auth.sql` (nessa ordem) — via
   phpMyAdmin (upload do `.sql`) se o painel oferecer, ou via linha de comando
   (`mysql -u usuario -p nome_do_banco < sql/schema.sql`) se houver acesso SSH.
5. Se houver dados de produção pra migrar (não só o schema vazio) — `mysqldump` no banco
   AWS RDS atual, depois importar o dump gerado no banco novo da Locaweb. Validar contagem de
   linhas nas tabelas principais (`usuarios_admin`, `pesquisadores`, `producoes`) nos dois
   bancos antes de considerar a migração dos dados concluída.

---

## 5. Aplicação — dois caminhos, conforme a decisão da seção 2

### Caminho A — Hospedagem compartilhada (sem Docker)
1. Confirmar no painel que a versão de PHP disponível é 8.2 (ou próxima) com as extensões
   `mbstring`, `curl`, `pdo_mysql`, `zip`, `mysqli` habilitadas.
2. Configurar o **document root** do domínio pra apontar pra pasta `public/` do projeto —
   não pra raiz do repositório (o `.htaccess`/roteamento do Prodmais assume isso, igual ao
   `APACHE_DOCUMENT_ROOT` usado no `Dockerfile.render`).
3. Enviar o código pro servidor — via Git (se o painel oferecer deploy por Git) ou FTP/SFTP
   como alternativa.
4. Rodar `composer install --no-dev` no servidor (se houver SSH/Composer disponíveis) ou
   subir a pasta `vendor/` já pronta via FTP, caso o plano não dê acesso a linha de comando.
5. Criar o `.env` de produção (ver seção 6) diretamente no servidor — nunca commitar esse
   arquivo no Git.

### Caminho B — Cloud Server / VPS (com Docker)
1. Instalar Docker e Docker Compose no VPS.
2. Usar o `Dockerfile` do projeto (a raiz, não o `Dockerfile.render`, que é específico do
   Render) — ou adaptar o `Dockerfile.render` trocando a porta dinâmica `$PORT` por uma porta
   fixa, já que na Locaweb não existe a variável de ambiente `$PORT` que o Render injeta.
3. Subir o container da aplicação, apontando as variáveis de ambiente (seção 6) pro MySQL
   já criado na seção 4, e — se a Opção B da seção 3 tiver sido escolhida — pro Elasticsearch
   também rodando no mesmo VPS.
4. Configurar um proxy reverso (Nginx ou Apache na frente do container) pra HTTPS — ver
   seção 7 sobre certificado SSL.

---

## 6. Variáveis de ambiente a configurar

Baseado no `.env.example` do projeto — ajustar pros valores reais gerados nas seções 4 e 5:

```
MYSQL_HOST=<host do MySQL na Locaweb — geralmente "localhost" em hospedagem compartilhada>
MYSQL_DB=<nome real do banco criado no painel>
MYSQL_USER=<usuário dedicado criado no painel>
MYSQL_PASS=<senha do usuário>

# Se a Opção A da seção 3 foi escolhida (sem Elasticsearch):
ES_USE=false

# Se a Opção B ou C foi escolhida (com Elasticsearch, self-host ou AWS OpenSearch):
ES_USE=true
ES_HOST=<endpoint do Elasticsearch — VPS local ou domínio do OpenSearch, conforme a opção>

APP_ENV=production
APP_DEBUG=false
APP_URL=https://<domínio oficial da UMC, ver seção 7>
SESSION_SECURE=true

RESEND_API_KEY=<chave de produção, gerada em resend.com>
RESEND_FROM=Prodmais UMC <endereço@dominio-da-umc>
```

---

## 7. Domínio oficial da UMC

1. Registrar o domínio em nome da instituição (não em nome pessoal) — a própria Locaweb
   também vende registro de domínio, ou pode ser feito num registrador separado (Registro.br,
   se for um domínio `.br`) e só apontado pra hospedagem Locaweb depois.
2. Configurar o domínio no painel de hospedagem, apontando pra pasta/container certo
   (conforme o caminho escolhido na seção 5).
3. Ativar certificado SSL — a Locaweb costuma oferecer Let's Encrypt gratuito integrado ao
   painel; confirmar se está incluso no plano contratado ou se precisa contratar à parte.
4. Atualizar `APP_URL` no `.env` e `SESSION_SECURE=true` (exige HTTPS funcionando).

---

## 8. Transferir a titularidade do repositório

O código já está na organização `Prodmais-UMC` no GitHub — nenhuma mudança necessária aqui,
independente de qual infraestrutura for escolhida.

---

## 9. Desligar os recursos da conta pessoal

**Só depois de validar que tudo está rodando 100% na Locaweb:**
- AWS RDS: Delete instance.
- AWS OpenSearch: Delete domain (a menos que a Opção C da seção 3 tenha sido escolhida —
  nesse caso ele continua ativo, só numa conta AWS institucional em vez de pessoal).
- Render: Delete service.

---

## 10. Checklist rápido pro dia da migração

- [ ] Plano da Locaweb confirmado (hospedagem compartilhada ou Cloud Server/VPS — seção 2)
- [ ] Decisão sobre Elasticsearch tomada (Opção A, B ou C — seção 3)
- [ ] Banco MySQL criado na Locaweb, schema importado, dados migrados e validados (seção 4)
- [ ] Aplicação hospedada (caminho A ou B, conforme a seção 2) e respondendo
- [ ] Variáveis de ambiente configuradas (seção 6)
- [ ] `/api/health` retornando OK
- [ ] Login + busca testados manualmente de ponta a ponta
- [ ] Domínio oficial da UMC registrado, apontado, com SSL ativo (seção 7)
- [ ] Recursos da conta pessoal (RDS, OpenSearch, Render) desligados só após validação completa
