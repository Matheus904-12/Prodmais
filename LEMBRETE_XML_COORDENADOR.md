# ⚠️ ATENÇÃO - XML DO COORDENADOR

**Status:** Arquivo XML recebido mas está VAZIO

**ID do arquivo recebido:** 2745899638505571.xml

---

## 📋 PRÓXIMOS PASSOS:

### **Opção 1: Upload pela Interface Web (RECOMENDADO)**

1. Acesse: http://localhost:8080/importar_lattes.php
2. Faça login (admin / admin123)
3. Selecione o PPG do coordenador
4. Faça upload do arquivo XML correto
5. Aguarde a importação

### **Opção 2: Baixar Novamente do Lattes**

1. Acesse: https://wwws.cnpq.br/cvlattesweb/
2. Login com CPF do coordenador
3. **Curriculum Vitae → Exportar → Formato XML**
4. Salve o arquivo
5. Faça upload pela interface web

---

## 🔍 VERIFICAÇÃO

Execute para ver os XMLs disponíveis:
```powershell
Get-ChildItem "C:\app3\Prodmais\data\lattes_xml" -Filter "*.xml"
```

---

## ⚡ TESTE RÁPIDO

Para testar o sistema antes da demonstração:
1. Use o XML de exemplo que já está na pasta:
   - `lattes_20251028231453_6901405dda0b2.xml`
2. Faça upload pela interface web
3. Verifique se aparece em "Pesquisadores"

---

**Data:** 08/03/2026 - 22:11
**Demonstração:** AMANHÃ (09/03/2026)
