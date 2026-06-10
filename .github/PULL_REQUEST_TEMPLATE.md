## Resumo

<!-- O que foi feito e por quê — não o que o código faz (isso está no diff) -->

## Tipo de mudança

- [ ] `feat` — nova funcionalidade
- [ ] `fix` — correção de bug
- [ ] `refactor` — refatoração sem mudança de comportamento externo
- [ ] `docs` — documentação apenas
- [ ] `test` — adição ou correção de testes
- [ ] `security` — correção de vulnerabilidade
- [ ] `chore` — atualização de dependências ou configuração

## Como testar

1. Subir stack: `.\INICIAR.ps1`
2. Acessar `http://localhost:8080/...`
3. Realizar:
   - [ ] Passo 1
   - [ ] Passo 2
4. Verificar que: ...

## Checklist

- [ ] Testes Cypress passando (`npm test`)
- [ ] Sem credenciais hardcoded (senhas, tokens, chaves de API)
- [ ] `.env.production` não commitado
- [ ] Input externo validado com `filter_input()` ou prepared statements
- [ ] Dados pessoais de pesquisadores tratados via `LgpdComplianceService`
- [ ] Autenticação usa `AuthManager` — não array hardcoded
- [ ] Mensagem de commit em português com prefixo semântico
- [ ] Branch criada a partir de `main` atualizado

## Impacto

<!-- Alguma mudança de banco? Migration necessária? Variável de ambiente nova? -->
