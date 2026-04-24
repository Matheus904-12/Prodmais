<?php
/**
 * PRODMAIS - Wrapper de Login
 * Encaminha para a View de Autenticação na arquitetura modular
 */

require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\View\Pages\Auth\LoginPage;

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

// Lógica de Autenticação (Exemplo simplificado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    // Aqui viria a chamada ao AuthManager
    $error = 'Credenciais incorretas para demonstração.';
}

// Renderizar a página usando o Componente Modular
LoginPage::display(['error' => $error]);
