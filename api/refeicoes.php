<?php
// =============================================
// API DE REFEIÇÕES
// GET    /api/refeicoes.php              → lista refeições do usuário
// POST   /api/refeicoes.php              → registra refeição
// DELETE /api/refeicoes.php?id=3         → remove refeição
// =============================================
require_once __DIR__ . '/../config/database.php';

$userId = getAuthUser();
$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ---- GET ----
if ($method === 'GET') {
    $data = $_GET['data'] ?? null; // filtro por data YYYY-MM-DD

    if ($data) {
        $stmt = $pdo->prepare('
            SELECT * FROM Refeicoes
            WHERE id_usuario = ? AND DATE(data_hora) = ?
            ORDER BY data_hora ASC
        ');
        $stmt->execute([$userId, $data]);
    } else {
        $stmt = $pdo->prepare('
            SELECT * FROM Refeicoes
            WHERE id_usuario = ?
            ORDER BY data_hora DESC
            LIMIT 50
        ');
        $stmt->execute([$userId]);
    }

    $refeicoes = $stmt->fetchAll();

    // Calcula total de calorias do dia se filtrou por data
    $totalCalorias = 0;
    foreach ($refeicoes as $r) {
        $totalCalorias += (int)($r['calorias_estimadas'] ?? 0);
    }

    respond([
        'refeicoes'       => $refeicoes,
        'total_calorias'  => $totalCalorias,
    ]);
}

// ---- POST ----
if ($method === 'POST') {
    $body      = getBody();
    $dataHora  = $body['data_hora'] ?? date('Y-m-d H:i:s');
    $descricao = trim($body['descricao_alimento'] ?? '');
    $calorias  = isset($body['calorias_estimadas']) ? (int)$body['calorias_estimadas'] : null;

    if (!$descricao) respond(['error' => 'Descrição do alimento é obrigatória.'], 400);

    $stmt = $pdo->prepare('
        INSERT INTO Refeicoes (id_usuario, data_hora, descricao_alimento, calorias_estimadas)
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$userId, $dataHora, $descricao, $calorias]);

    respond([
        'message'     => 'Refeição registrada!',
        'id_refeicao' => (int)$pdo->lastInsertId(),
    ], 201);
}

// ---- DELETE ----
if ($method === 'DELETE' && $id) {
    $check = $pdo->prepare('SELECT id_refeicao FROM Refeicoes WHERE id_refeicao = ? AND id_usuario = ?');
    $check->execute([$id, $userId]);
    if (!$check->fetch()) respond(['error' => 'Refeição não encontrada.'], 404);

    $pdo->prepare('DELETE FROM Refeicoes WHERE id_refeicao = ?')->execute([$id]);
    respond(['message' => 'Refeição excluída!']);
}

respond(['error' => 'Método não suportado.'], 405);
