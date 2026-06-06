<?php
// =============================================
// API DE AUTENTICAÇÃO
// POST /api/auth.php?action=register
// POST /api/auth.php?action=login
// =============================================
require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';
$body   = getBody();

if ($action === 'register') {
    $nome  = trim($body['nome'] ?? '');
    $email = trim($body['email'] ?? '');
    $senha = $body['senha'] ?? '';

    if (!$nome || !$email || !$senha) {
        respond(['error' => 'Nome, e-mail e senha são obrigatórios.'], 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(['error' => 'E-mail inválido.'], 400);
    }
    if (strlen($senha) < 6) {
        respond(['error' => 'A senha deve ter pelo menos 6 caracteres.'], 400);
    }

    $pdo = getConnection();

    $stmt = $pdo->prepare('SELECT id_usuario FROM Usuarios WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        respond(['error' => 'E-mail já cadastrado.'], 409);
    }

    $hash = password_hash($senha, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO Usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
    $stmt->execute([$nome, $email, $hash]);
    $id = $pdo->lastInsertId();

    respond([
        'message'    => 'Usuário criado com sucesso!',
        'id_usuario' => (int)$id,
        'nome'       => $nome,
        'email'      => $email,
    ], 201);
}

if ($action === 'login') {
    $email = trim($body['email'] ?? '');
    $senha = $body['senha'] ?? '';

    if (!$email || !$senha) {
        respond(['error' => 'E-mail e senha são obrigatórios.'], 400);
    }

    $pdo  = getConnection();
    $stmt = $pdo->prepare('SELECT id_usuario, nome, email, senha_hash FROM Usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($senha, $user['senha_hash'])) {
        respond(['error' => 'E-mail ou senha incorretos.'], 401);
    }

    // Registra sessão PHP
    $_SESSION['id_usuario'] = (int)$user['id_usuario'];
    $_SESSION['nome']       = $user['nome'];
    $_SESSION['email']      = $user['email'];

    respond([
        'message'    => 'Login realizado com sucesso!',
        'id_usuario' => (int)$user['id_usuario'],
        'nome'       => $user['nome'],
        'email'      => $user['email'],
    ]);
}

if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    respond(['message' => 'Logout realizado.']);
}

respond(['error' => 'Ação inválida.'], 400);
