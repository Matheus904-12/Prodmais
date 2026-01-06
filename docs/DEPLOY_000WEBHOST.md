# 🚀 Deploy no 000webhost - 100% GRATUITO

## ⭐ Por que 000webhost?

- ✅ 100% GRATUITO (sem cartão)
- ✅ PHP 8.x nativo
- ✅ MySQL incluído
- ✅ 300 MB storage
- ✅ Sem sleep/dormência
- ✅ cPanel para gerenciar
- ✅ File Manager integrado

---

## 📋 Passo a Passo (10 minutos)

### 1. Criar Conta (2 min)

1. Acesse: https://www.000webhost.com
2. Clique em **"Sign Up Free"**
3. Preencha:
   - Email
   - Senha
   - Nome do site: `prodmaisumc`
4. ✅ Confirme email

### 2. Criar Website (1 min)

1. No painel, clique **"Create New Website"**
2. Escolha **"Build Website"**
3. Selecione: **"Empty Website"**
4. Seu site será: `prodmaisumc.000webhostapp.com`

### 3. Fazer Upload (5 minutos)

**Opção A: Via File Manager (mais fácil)**

1. No painel, clique **"File Manager"**
2. Delete pasta `public_html`
3. Crie nova pasta `public_html`
4. Entre em `public_html`
5. Faça upload de TODOS os arquivos do projeto
6. ✅ Pronto!

**Opção B: Via FTP**

1. Use FileZilla
2. Host: `files.000webhost.com`
3. Username: seu email
4. Password: sua senha
5. Faça upload para `/public_html`

### 4. Configurar .htaccess (2 min)

Criar arquivo `.htaccess` em `public_html`:

```apache
# Redirect to public folder
RewriteEngine On

# Se não for um arquivo ou diretório existente
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Redireciona para public/
RewriteRule ^(.*)$ public/$1 [L]

# Permite acesso direto à pasta public
RewriteRule ^public/(.*)$ public/$1 [L]
```

---

## 📁 Estrutura no 000webhost

```
public_html/
├── .htaccess          ← IMPORTANTE
├── index.php          ← Redireciona para public/
├── bin/
├── config/
├── data/
├── public/            ← Seu código aqui
│   ├── index.php
│   ├── admin.php
│   ├── login.php
│   └── ...
├── src/
├── vendor/
└── composer.json
```

---

## 🔧 Configurações Importantes

### Criar index.php na raiz (public_html)

```php
<?php
// Redirect all requests to public folder
if (!file_exists('public/index.php')) {
    die('Error: public/index.php not found');
}

// Change to public directory
chdir('public');

// Include the main application
require 'index.php';
```

### Permissões de Pastas

Via File Manager:
- `data/` → 755
- `data/uploads/` → 755
- `data/logs.sqlite` → 644

---

## ⚡ Otimizações

### 1. Compactar arquivos antes do upload

```powershell
# No seu PC, criar ZIP
Compress-Archive -Path * -DestinationPath prodmais.zip

# Upload apenas o ZIP
# Extrair no File Manager do 000webhost
```

### 2. Não fazer upload de:

- `node_modules/`
- `cypress/`
- `.git/`
- `*.md` (exceto README)
- `img/screenshots/`

---

## 🆘 Problemas Comuns

### Site não abre?

1. Verifique se `.htaccess` existe
2. Verifique se `index.php` está na raiz
3. Verifique permissões das pastas

### Erro 500?

1. Verifique logs em: Painel > Error Logs
2. Verifique versão do PHP (deve ser 8.0+)
3. Verifique se vendor/ foi enviado

### Upload lento?

1. Compacte arquivos em ZIP
2. Faça upload do ZIP
3. Extraia no File Manager

---

## 📊 Limitações do Plano Grátis

| Recurso | Limite |
|---------|--------|
| Storage | 300 MB |
| Bandwidth | 3 GB/mês |
| Databases | 2 MySQL |
| Email | Não incluído |
| Elasticsearch | ❌ Não suporta |

**Solução:** Sistema usa **fallback mode** (JSON local)

---

## ✅ Checklist Final

- [ ] Conta criada no 000webhost
- [ ] Website criado
- [ ] Arquivos enviados via File Manager ou FTP
- [ ] `.htaccess` configurado
- [ ] `index.php` na raiz criado
- [ ] Permissões ajustadas (755 para data/)
- [ ] Site acessível: `prodmaisumc.000webhostapp.com`
- [ ] Login funciona (admin: matheus.lucindo)
- [ ] Dashboard carrega corretamente

---

## 🎓 Para Apresentar na Universidade

**URL final:** `https://prodmaisumc.000webhostapp.com`

- ✅ HTTPS automático
- ✅ Sempre online (não dorme)
- ✅ Sem custo
- ✅ Sem limitação de tempo

---

## ⏱️ Tempo Total: ~10-15 minutos

1. Criar conta: 2 min
2. Criar website: 1 min
3. Upload arquivos: 5-7 min
4. Configurar: 2 min
5. Testar: 1 min

**Resultado: Site 100% funcional e GRATUITO! 🎉**

---

*Guia atualizado: 22 de outubro de 2025*
