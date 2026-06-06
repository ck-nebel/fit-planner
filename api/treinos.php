<?php
// =============================================
// API DE TREINOS
// GET    /api/treinos.php                → histórico de treinos
// GET    /api/treinos.php?id=5           → detalhes de um treino
// POST   /api/treinos.php                → registra/agenda treino
// PUT    /api/treinos.php?id=5           → atualiza (marcar como realizado, etc.)
// DELETE /api/treinos.php?id=5           → remove treino
// =============================================
require_once __DIR__ . '/../config/database.php';

$userId = getAuthUser();
$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ---- GET ----
if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM Treinos WHERE id_treino = ? AND id_usuario = ?');
        $stmt->execute([$id, $userId]);
        $treino = $stmt->fetch();
        if (!$treino) respond(['error' => 'Treino não encontrado.'], 404);

        $stmt = $pdo->prepare('
            SELECT ee.*,
                   e.nome AS exercicio_nome, e.maquina_equipamento,
                   g.nome AS grupo_nome
            FROM ExecucaoExercicios ee
            JOIN Exercicios e ON e.id_exercicio = ee.id_exercicio
            JOIN GruposMusculares g ON g.id_grupo = e.id_grupo
            WHERE ee.id_treino = ?
        ');
        $stmt->execute([$id]);
        $treino['execucoes'] = $stmt->fetchAll();

        respond(['treino' => $treino]);
    }

    // Lista: agendados e realizados separados
    $tipo = $_GET['tipo'] ?? 'todos'; // agendados | realizados | todos
    $sql  = 'SELECT * FROM Treinos WHERE id_usuario = ?';
    if ($tipo === 'agendados')  $sql .= ' AND data_realizada IS NULL ORDER BY data_agendada ASC';
    elseif ($tipo === 'realizados') $sql .= ' AND data_realizada IS NOT NULL ORDER BY data_realizada DESC';
    else $sql .= ' ORDER BY COALESCE(data_realizada, data_agendada) DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    respond(['treinos' => $stmt->fetchAll()]);
}

// ---- POST (criar treino ou agendar) ----
if ($method === 'POST') {
    $body            = getBody();
    $dataAgendada    = $body['data_agendada'] ?? null;    // null = treino avulso
    $dataRealizada   = $body['data_realizada'] ?? null;
    $tempoTotal      = isset($body['tempo_total_minutos']) ? (int)$body['tempo_total_minutos'] : null;
    $obs             = trim($body['observacoes'] ?? '');
    $execucoes       = $body['execucoes'] ?? [];           // array de exercícios executados
    $idRotina        = isset($body['id_rotina']) ? (int)$body['id_rotina'] : null;

    $stmt = $pdo->prepare('
        INSERT INTO Treinos (id_usuario, data_agendada, data_realizada, tempo_total_minutos, observacoes)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$userId, $dataAgendada, $dataRealizada, $tempoTotal, $obs]);
    $idTreino = (int)$pdo->lastInsertId();

    // Se passou uma rotina, carrega os exercícios dela
    if ($idRotina && empty($execucoes)) {
        $stmt = $pdo->prepare('SELECT id_exercicio FROM ItensRotina WHERE id_rotina = ?');
        $stmt->execute([$idRotina]);
        foreach ($stmt->fetchAll() as $item) {
            $execucoes[] = ['id_exercicio' => $item['id_exercicio']];
        }
    }

    // Insere execuções dos exercícios
    if (!empty($execucoes)) {
        $ins = $pdo->prepare('
            INSERT INTO ExecucaoExercicios
                (id_treino, id_exercicio, series_realizadas, repeticoes_media, carga_kg, tempo_descanso_segundos)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        foreach ($execucoes as $ex) {
            $ins->execute([
                $idTreino,
                (int)($ex['id_exercicio'] ?? 0),
                isset($ex['series_realizadas'])       ? (int)$ex['series_realizadas']       : null,
                isset($ex['repeticoes_media'])         ? (int)$ex['repeticoes_media']         : null,
                isset($ex['carga_kg'])                 ? (float)$ex['carga_kg']               : null,
                isset($ex['tempo_descanso_segundos'])  ? (int)$ex['tempo_descanso_segundos']  : null,
            ]);
        }
    }

    respond(['message' => 'Treino salvo!', 'id_treino' => $idTreino], 201);
}

// ---- PUT (atualizar / marcar como realizado) ----
if ($method === 'PUT' && $id) {
    $body = getBody();

    $check = $pdo->prepare('SELECT id_treino FROM Treinos WHERE id_treino = ? AND id_usuario = ?');
    $check->execute([$id, $userId]);
    if (!$check->fetch()) respond(['error' => 'Treino não encontrado.'], 404);

    $fields = [];
    $params = [];

    if (array_key_exists('data_realizada', $body)) {
        $fields[] = 'data_realizada = ?';
        $params[]  = $body['data_realizada'];
    }
    if (array_key_exists('tempo_total_minutos', $body)) {
        $fields[] = 'tempo_total_minutos = ?';
        $params[]  = (int)$body['tempo_total_minutos'];
    }
    if (array_key_exists('observacoes', $body)) {
        $fields[] = 'observacoes = ?';
        $params[]  = $body['observacoes'];
    }

    if (!empty($fields)) {
        $params[] = $id;
        $pdo->prepare('UPDATE Treinos SET ' . implode(', ', $fields) . ' WHERE id_treino = ?')
            ->execute($params);
    }

    // Atualiza execuções se informadas
    if (!empty($body['execucoes'])) {
        $pdo->prepare('DELETE FROM ExecucaoExercicios WHERE id_treino = ?')->execute([$id]);
        $ins = $pdo->prepare('
            INSERT INTO ExecucaoExercicios
                (id_treino, id_exercicio, series_realizadas, repeticoes_media, carga_kg, tempo_descanso_segundos)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        foreach ($body['execucoes'] as $ex) {
            $ins->execute([
                $id,
                (int)($ex['id_exercicio'] ?? 0),
                isset($ex['series_realizadas'])       ? (int)$ex['series_realizadas']       : null,
                isset($ex['repeticoes_media'])         ? (int)$ex['repeticoes_media']         : null,
                isset($ex['carga_kg'])                 ? (float)$ex['carga_kg']               : null,
                isset($ex['tempo_descanso_segundos'])  ? (int)$ex['tempo_descanso_segundos']  : null,
            ]);
        }
    }

    respond(['message' => 'Treino atualizado!']);
}

// ---- DELETE ----
if ($method === 'DELETE' && $id) {
    $check = $pdo->prepare('SELECT id_treino FROM Treinos WHERE id_treino = ? AND id_usuario = ?');
    $check->execute([$id, $userId]);
    if (!$check->fetch()) respond(['error' => 'Treino não encontrado.'], 404);

    $pdo->prepare('DELETE FROM Treinos WHERE id_treino = ?')->execute([$id]);
    respond(['message' => 'Treino excluído!']);
}

respond(['error' => 'Método não suportado.'], 405);
