<?php
// =============================================
// CONFIGURAÇÃO DO BANCO DE DADOS
// Altere os valores abaixo para seu ambiente
// =============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // seu usuário MySQL
define('DB_PASS', '');           // sua senha MySQL
define('DB_NAME', 'fit_planner');

// Inicia sessão PHP (cookie automático — funciona em qualquer Apache/XAMPP)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

// Headers padrão para todas as respostas da API
header('Content-Type: application/json; charset=utf-8');
// Permite cookies de sessão no fetch (credentials: include)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Função auxiliar para retornar JSON
function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Função para obter body da requisição
function getBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

// Autenticação via sessão PHP
function getAuthUser(): int {
    if (!empty($_SESSION['id_usuario'])) {
        return (int) $_SESSION['id_usuario'];
    }
    respond(['error' => 'Não autenticado.'], 401);
}
