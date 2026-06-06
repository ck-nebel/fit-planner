<?php
// =============================================
// API DE GRUPOS MUSCULARES E EXERCÍCIOS
// GET /api/exercicios.php                → lista grupos e exercícios
// GET /api/exercicios.php?grupo=3        → exercícios de um grupo
// =============================================
require_once __DIR__ . '/../config/database.php';

getAuthUser(); // garante que está logado

$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$grupo  = isset($_GET['grupo']) ? (int)$_GET['grupo'] : null;

if ($method === 'GET') {
    if ($grupo) {
        // Retorna exercícios de um grupo específico
        $stmt = $pdo->prepare('
            SELECT e.id_exercicio, e.nome, e.maquina_equipamento,
                   g.nome AS grupo_nome
            FROM Exercicios e
            JOIN GruposMusculares g ON g.id_grupo = e.id_grupo
            WHERE e.id_grupo = ?
            ORDER BY e.nome
        ');
        $stmt->execute([$grupo]);
        respond(['exercicios' => $stmt->fetchAll()]);
    } else {
        // Retorna todos os grupos com seus exercícios agrupados
        $grupos = $pdo->query('SELECT id_grupo, nome FROM GruposMusculares ORDER BY nome')->fetchAll();
        $exs    = $pdo->query('SELECT id_exercicio, id_grupo, nome, maquina_equipamento FROM Exercicios ORDER BY nome')->fetchAll();

        $map = [];
        foreach ($exs as $e) {
            $map[$e['id_grupo']][] = $e;
        }
        foreach ($grupos as &$g) {
            $g['exercicios'] = $map[$g['id_grupo']] ?? [];
        }
        respond(['grupos' => $grupos]);
    }
}

respond(['error' => 'Método não suportado.'], 405);
