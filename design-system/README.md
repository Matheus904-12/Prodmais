# Prodmais UMC — Design System & Arquitetura

Site estático de referência para desenvolvedores: tokens de design (cores,
tipografia, espaçamento), padrões de componentes e arquitetura de
backend/frontend do Prodmais UMC. Sem build step — é HTML + CSS puro.

## Ver localmente

Abra `index.html` direto no navegador, ou sirva com qualquer servidor estático:

```bash
cd design-system
python3 -m http.server 8000
# http://localhost:8000
```

## Publicar no Netlify

1. [app.netlify.com](https://app.netlify.com) → **Add new site → Import an existing project**
2. Conecte o repositório `Matheus904-12/Prodmais`
3. Em **Site settings → Build & deploy**:
   - **Base directory**: `design-system`
   - **Build command**: (deixe vazio)
   - **Publish directory**: `design-system`
4. Deploy — o Netlify já detecta o `netlify.toml` desta pasta

## Publicar no Vercel

1. [vercel.com/new](https://vercel.com/new) → importe `Matheus904-12/Prodmais`
2. Em **Root Directory**, selecione `design-system`
3. Framework Preset: **Other** (site estático, sem build)
4. Deploy

## Manter atualizado

Sempre que um padrão visual novo for criado (nova cor, novo componente) ou uma
decisão de arquitetura mudar, atualize `index.html` nesta pasta — é a fonte
de verdade para quem entra no projeto depois.
