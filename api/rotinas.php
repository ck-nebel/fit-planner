<?php
// =============================================
// API DE ROTINAS DE TREINO (CRUD)
// GET    /api/rotinas.php                → lista rotinas do usuário
// GET    /api/rotinas.php?id=2           → detalhes de uma rotina
// POST   /api/rotinas.php                → cria rotina
// PUT    /api/rotinas.php?id=2           → edita rotina
// DELETE /api/rotinas.php?id=2           → remove rotina
// =============================================
require_once __DIR__ . '/../config/database.php';

$userId = getAuthUser();
$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ---- GET ----
if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM Rotinas WHERE id_rotina = ? AND id_usuario = ?');
        $stmt->execute([$id, $userId]);
        $rotina = $stmt->fetch();
        if (!$rotina) respond(['error' => 'Rotina não encontrada.'], 404);

        $stmt = $pdo->prepare('
            SELECT ir.id_item_rotina, ir.id_exercicio,
                   e.nome AS exercicio_nome, e.maquina_equipamento,
                   g.nome AS grupo_nome
            FROM ItensRotina ir
            JOIN Exercicios e ON e.id_exercicio = ir.id_exercicio
            JOIN GruposMusculares g ON g.id_grupo = e.id_grupo
            WHERE ir.id_rotina = ?
        ');
        $stmt->execute([$id]);
        $rotina['itens'] = $stmt->fetchAll();

        respond(['rotina' => $rotina]);
    }

    $stmt = $pdo->prepare('SELECT * FROM Rotinas WHERE id_usuario = ? ORDER BY nome_rotina');
    $stmt->execute([$userId]);
    respond(['rotinas' => $stmt->fetchAll()]);
}

// ---- POST (criar) ----
if ($method === 'POST') {
    $body = getBody();
    $nome = trim($body['nome_rotina'] ?? '');
    $desc = trim($body['descricao'] ?? '');
    $itens = $body['exercicios'] ?? []; // array de id_exercicio

    if (!$nome) respond(['error' => 'Nome da rotina é obrigatório.'], 400);

    $stmt = $pdo->prepare('INSERT INTO Rotinas (id_usuario, nome_rotina, descricao) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $nome, $desc]);
    $idRotina = (int)$pdo->lastInsertId();

    if (!empty($itens)) {
        $ins = $pdo->prepare('INSERT INTO ItensRotina (id_rotina, id_exercicio) VALUES (?, ?)');
        foreach ($itens as $idEx) {
            $ins->execute([$idRotina, (int)$idEx]);
        }
    }

    respond(['message' => 'Rotina criada!', 'id_rotina' => $idRotina], 201);
}

// ---- PUT (editar) ----
if ($method === 'PUT' && $id) {
    $body = getBody();
    $nome = trim($body['nome_rotina'] ?? '');
    $desc = trim($body['descricao'] ?? '');
    $itens = $body['exercicios'] ?? null;

    // Verifica se rotina pertence ao usuário
    $check = $pdo->prepare('SELECT id_rotina FROM Rotinas WHERE id_rotina = ? AND id_usuario = ?');
    $check->execute([$id, $userId]);
    if (!$check->fetch()) respond(['error' => 'Rotina não encontrada.'], 404);

    if ($nome) {
        $stmt = $pdo->prepare('UPDATE Rotinas SET nome_rotina = ?, descricao = ? WHERE id_rotina = ?');
        $stmt->execute([$nome, $desc, $id]);
    }

    if ($itens !== null) {
        $pdo->prepare('DELETE FROM ItensRotina WHERE id_rotina = ?')->execute([$id]);
        $ins = $pdo->prepare('INSERT INTO ItensRotina (id_rotina, id_exercicio) VALUES (?, ?)');
        foreach ($itens as $idEx) {
            $ins->execute([$id, (int)$idEx]);
        }
    }

    respond(['message' => 'Rotina atualizada!']);
}

// ---- DELETE ----
if ($method === 'DELETE' && $id) {
    $check = $pdo->prepare('SELECT id_rotina FROM Rotinas WHERE id_rotina = ? AND id_usuario = ?');
    $check->execute([$id, $userId]);
    if (!$check->fetch()) respond(['error' => 'Rotina não encontrada.'], 404);

    $pdo->prepare('DELETE FROM Rotinas WHERE id_rotina = ?')->execute([$id]);
    respond(['message' => 'Rotina excluída!']);
}

respond(['error' => 'Método não suportado.'], 405);
