@echo off
echo ========================================
echo  INSTALADOR AUTOMATICO DO KIBANA 8.10.4
echo ========================================
echo.

:: Verificar se ja existe
if exist "C:\kibana-8.10.4" (
    echo [OK] Kibana ja esta instalado em C:\kibana-8.10.4
    echo.
    choice /C SN /M "Deseja iniciar o Kibana agora"
    if errorlevel 2 goto :END
    if errorlevel 1 goto :START
)

echo [INFO] Kibana nao encontrado. Iniciando download...
echo.
echo Por favor, siga estas etapas:
echo.
echo 1. Abra: https://www.elastic.co/downloads/kibana
echo 2. Baixe: Kibana 8.10.4 Windows x64 (ZIP)
echo 3. Extraia o arquivo ZIP para C:\
echo 4. Renomeie a pasta para: kibana-8.10.4
echo 5. Execute este script novamente
echo.
echo Pressione qualquer tecla para abrir a pagina de download...
pause > nul
start https://www.elastic.co/downloads/kibana
goto :END

:START
echo.
echo ========================================
echo  INICIANDO KIBANA...
echo ========================================
echo.
echo [INFO] Configurando conexao com Elasticsearch...

:: Criar arquivo de configuracao se nao existir
if not exist "C:\kibana-8.10.4\config\kibana.yml.backup" (
    copy "C:\kibana-8.10.4\config\kibana.yml" "C:\kibana-8.10.4\config\kibana.yml.backup"
)

:: Configurar kibana.yml
echo server.port: 5601 > C:\kibana-8.10.4\config\kibana.yml
echo server.host: "localhost" >> C:\kibana-8.10.4\config\kibana.yml
echo elasticsearch.hosts: ["http://localhost:9200"] >> C:\kibana-8.10.4\config\kibana.yml
echo.

echo [OK] Configuracao concluida!
echo [INFO] Iniciando Kibana em http://localhost:5601
echo.
echo AGUARDE: O Kibana pode levar ate 2 minutos para iniciar...
echo.

:: Iniciar Kibana
cd C:\kibana-8.10.4\bin
start "Kibana 8.10.4" cmd /k kibana.bat

echo.
echo ========================================
echo  KIBANA INICIADO COM SUCESSO!
echo ========================================
echo.
echo Acesse: http://localhost:5601
echo.
echo Aguarde alguns minutos e depois importe os dashboards:
echo - Arquivo: inc/dashboards/dashboard_ppgs_prod_cv.ndjson
echo.

timeout /t 10
start http://localhost:5601

:END
echo.
pause
