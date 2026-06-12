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

// Redirecionar se já autenticado
if (!empty($_SESSION['user_id'])) {
    $papel_atual = $_SESSION['papel'] ?? '';
    $dest_already = in_array($papel_atual, ['admin', 'pesquisador']) ? '/admin.php' : '/dashboard.php';
    header('Location: ' . $dest_already);
    exit;
}

require_once __DIR__ . '/../src/Domain/Security/AuthManager.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'user',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $password = $_POST['password'] ?? '';

    $host    = getenv('MYSQL_HOST') ?: 'db';
    $db_name = getenv('MYSQL_DB')   ?: 'prodmais_umc';
    $db_user = getenv('MYSQL_USER') ?: 'prodmais';
    $db_pass = getenv('MYSQL_PASS') ?: 'prodmais123';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $auth    = new AuthManager($pdo);
        $result  = $auth->login($username, $password);

        if ($result['sucesso']) {
            $destino = in_array($_SESSION['papel'] ?? '', ['admin', 'pesquisador']) ? '/admin.php' : '/dashboard.php';
            header('Location: ' . $destino);
            exit;
        }

        $error = $result['mensagem'];
    } catch (PDOException $e) {
        error_log('Login DB error: ' . $e->getMessage());
        $error = 'Erro de conexão com o banco de dados. Tente novamente.';
    }
}

LoginPage::display(['error' => $error]);
