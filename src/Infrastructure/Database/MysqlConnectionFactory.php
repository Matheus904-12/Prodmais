<?php
/**
 * Cria conexão PDO MySQL a partir de variáveis de ambiente.
 *
 * Suporta MYSQL_SSL_CA opcional — necessário para provedores gerenciados
 * que exigem SSL (ex: Aiven, "SSL mode: REQUIRED"). Sem isso, o mysqlnd
 * não negocia TLS e o provedor recusa a conexão.
 */
function criarConexaoMysql(): PDO
{
    $host   = getenv('MYSQL_HOST') ?: 'db';
    $dbName = getenv('MYSQL_DB') ?: 'prodmais_umc';
    $dbUser = getenv('MYSQL_USER') ?: 'prodmais';
    $dbPass = getenv('MYSQL_PASS') ?: 'prodmais123';
    $sslCa  = getenv('MYSQL_SSL_CA') ?: null;

    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($sslCa && file_exists($sslCa)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
    }

    return new PDO(
        "mysql:host=$host;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        $options
    );
}
