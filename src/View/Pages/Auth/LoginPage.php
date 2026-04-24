<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\View\Pages\Auth\LoginPage;

session_start();
$error = '';

// Auth logic (Simulated for brevity, in production this should be in a Service)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    // ... logic ...
    $error = 'Usuário ou senha inválidos.';
}

LoginPage::display(['error' => $error]);