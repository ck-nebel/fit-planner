-- ============================================================
-- FIT PLANNER — Script de criação do banco de dados
-- Execute este arquivo no seu MySQL/MariaDB antes de usar o sistema
-- Comando: mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS fit_planner
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fit_planner;

-- Usuários do sistema
CREATE TABLE IF NOT EXISTS Usuarios (
    id_usuario    INT          PRIMARY KEY AUTO_INCREMENT,
    nome          VARCHAR(100) NOT NULL,
    email         VARCHAR(100) UNIQUE NOT NULL,
    senha_hash    VARCHAR(255) NOT NULL,
    data_cadastro DATETIME     DEFAULT CURRENT_TIMESTAMP
);

-- Grupos musculares (peito, costas, pernas, etc.)
CREATE TABLE IF NOT EXISTS GruposMusculares (
    id_grupo INT         PRIMARY KEY AUTO_INCREMENT,
    nome     VARCHAR(50) NOT NULL
);

-- Exercícios vinculados a grupos musculares
CREATE TABLE IF NOT EXISTS Exercicios (
    id_exercicio        INT          PRIMARY KEY AUTO_INCREMENT,
    id_grupo            INT          NOT NULL,
    nome                VARCHAR(100) NOT NULL,
    maquina_equipamento VARCHAR(100),
    FOREIGN KEY (id_grupo) REFERENCES GruposMusculares(id_grupo)
);

-- Rotinas de treino (templates reutilizáveis)
CREATE TABLE IF NOT EXISTS Rotinas (
    id_rotina   INT          PRIMARY KEY AUTO_INCREMENT,
    id_usuario  INT          NOT NULL,
    nome_rotina VARCHAR(50)  NOT NULL,
    descricao   VARCHAR(200),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

-- Exercícios que compõem uma rotina
CREATE TABLE IF NOT EXISTS ItensRotina (
    id_item_rotina INT PRIMARY KEY AUTO_INCREMENT,
    id_rotina      INT NOT NULL,
    id_exercicio   INT NOT NULL,
    FOREIGN KEY (id_rotina)    REFERENCES Rotinas(id_rotina)   ON DELETE CASCADE,
    FOREIGN KEY (id_exercicio) REFERENCES Exercicios(id_exercicio)
);

-- Treinos (agendados ou avulsos)
CREATE TABLE IF NOT EXISTS Treinos (
    id_treino           INT      PRIMARY KEY AUTO_INCREMENT,
    id_usuario          INT      NOT NULL,
    data_agendada       DATETIME,
    data_realizada      DATETIME,
    tempo_total_minutos INT,
    observacoes         TEXT,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

-- Execução detalhada de cada exercício num treino
CREATE TABLE IF NOT EXISTS ExecucaoExercicios (
    id_execucao              INT          PRIMARY KEY AUTO_INCREMENT,
    id_treino                INT          NOT NULL,
    id_exercicio             INT          NOT NULL,
    series_realizadas        INT,
    repeticoes_media         INT,
    carga_kg                 DECIMAL(5,2),
    tempo_descanso_segundos  INT,
    FOREIGN KEY (id_treino)    REFERENCES Treinos(id_treino)     ON DELETE CASCADE,
    FOREIGN KEY (id_exercicio) REFERENCES Exercicios(id_exercicio)
);

-- Refeições diárias
CREATE TABLE IF NOT EXISTS Refeicoes (
    id_refeicao        INT      PRIMARY KEY AUTO_INCREMENT,
    id_usuario         INT      NOT NULL,
    data_hora          DATETIME NOT NULL,
    descricao_alimento TEXT     NOT NULL,
    calorias_estimadas INT,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

INSERT INTO GruposMusculares (nome) VALUES
    ('Peito'),
    ('Costas'),
    ('Pernas'),
    ('Ombros'),
    ('Bíceps'),
    ('Tríceps'),
    ('Abdômen'),
    ('Glúteos'),
    ('Panturrilha');

INSERT INTO Exercicios (id_grupo, nome, maquina_equipamento) VALUES
    -- Peito
    (1, 'Supino Reto',            'Barra/Halteres'),
    (1, 'Supino Inclinado',       'Barra/Halteres'),
    (1, 'Crucifixo',              'Halteres'),
    (1, 'Peck Deck',              'Máquina Peck Deck'),
    (1, 'Flexão de Braço',        'Peso Corporal'),
    -- Costas
    (2, 'Puxada Frontal',         'Máquina Puxada'),
    (2, 'Remada Curvada',         'Barra'),
    (2, 'Remada Unilateral',      'Halter'),
    (2, 'Pull-up / Barra Fixa',   'Barra Fixa'),
    (2, 'Serrote',                'Halter'),
    -- Pernas
    (3, 'Agachamento Livre',      'Barra'),
    (3, 'Leg Press 45°',          'Máquina Leg Press'),
    (3, 'Extensora',              'Máquina Extensora'),
    (3, 'Mesa Flexora',           'Máquina Flexora'),
    (3, 'Avanço / Lunge',         'Halteres'),
    -- Ombros
    (4, 'Desenvolvimento',        'Barra/Halteres'),
    (4, 'Elevação Lateral',       'Halteres'),
    (4, 'Elevação Frontal',       'Halteres/Anilha'),
    (4, 'Remada Alta',            'Barra'),
    -- Bíceps
    (5, 'Rosca Direta',           'Barra'),
    (5, 'Rosca Alternada',        'Halteres'),
    (5, 'Rosca Scott',            'Máquina Scott'),
    (5, 'Rosca Martelo',          'Halteres'),
    -- Tríceps
    (6, 'Tríceps Pulley',         'Máquina Pulley'),
    (6, 'Tríceps Testa',          'Barra/Halteres'),
    (6, 'Mergulho / Paralelas',   'Paralelas'),
    (6, 'Kickback',               'Halter'),
    -- Abdômen
    (7, 'Abdominal Crunch',       'Peso Corporal'),
    (7, 'Prancha',                'Peso Corporal'),
    (7, 'Abdominal Infra',        'Peso Corporal'),
    (7, 'Abdominal Oblíquo',      'Peso Corporal'),
    -- Glúteos
    (8, 'Hip Thrust',             'Barra/Banco'),
    (8, 'Stiff',                  'Barra/Halteres'),
    (8, 'Abdução de Quadril',     'Máquina Abdutora'),
    -- Panturrilha
    (9, 'Panturrilha em Pé',      'Máquina/Degrau'),
    (9, 'Panturrilha Sentado',    'Máquina Panturrilha');
