<?php

function jtech_table_exists(mysqli $conexao, string $table): bool
{
    $sql = 'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1';
    $stmt = $conexao->prepare($sql);

    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param('s', $table);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = (bool) $result->fetch_row();
    $stmt->close();

    return $exists;
}

function jtech_column_exists(mysqli $conexao, string $table, string $column): bool
{
    $sql = 'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1';
    $stmt = $conexao->prepare($sql);

    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = (bool) $result->fetch_row();
    $stmt->close();

    return $exists;
}

function ensure_analytics_schema(mysqli $conexao): void
{
    // Migra nome da tabela para pt-BR, sem perder dados existentes.
    if (!jtech_table_exists($conexao, 'logs_visitas_site') && jtech_table_exists($conexao, 'site_visit_logs')) {
        $conexao->query('RENAME TABLE site_visit_logs TO logs_visitas_site');
    }

    $conexao->query(
        "CREATE TABLE IF NOT EXISTS logs_visitas_site (
            id INT NOT NULL AUTO_INCREMENT,
            data_visita DATE NOT NULL,
            id_sessao VARCHAR(128) NOT NULL,
            pagina VARCHAR(120) NOT NULL,
            autenticado TINYINT(1) NOT NULL DEFAULT 0,
            id_usuario INT NULL,
            primeira_visita_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ultima_visita_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            total_visualizacoes INT NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY uq_diario_sessao_pagina (data_visita, id_sessao, pagina),
            KEY idx_pagina (pagina),
            KEY idx_autenticado (autenticado),
            KEY idx_usuario (id_usuario)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // Migra colunas antigas (inglês -> pt-BR) na tabela de logs.
    if (jtech_column_exists($conexao, 'logs_visitas_site', 'visit_date') && !jtech_column_exists($conexao, 'logs_visitas_site', 'data_visita')) {
        $conexao->query('ALTER TABLE logs_visitas_site CHANGE visit_date data_visita DATE NOT NULL');
    }

    if (jtech_column_exists($conexao, 'logs_visitas_site', 'session_id') && !jtech_column_exists($conexao, 'logs_visitas_site', 'id_sessao')) {
        $conexao->query('ALTER TABLE logs_visitas_site CHANGE session_id id_sessao VARCHAR(128) NOT NULL');
    }

    if (jtech_column_exists($conexao, 'logs_visitas_site', 'page') && !jtech_column_exists($conexao, 'logs_visitas_site', 'pagina')) {
        $conexao->query('ALTER TABLE logs_visitas_site CHANGE page pagina VARCHAR(120) NOT NULL');
    }

    if (jtech_column_exists($conexao, 'logs_visitas_site', 'is_authenticated') && !jtech_column_exists($conexao, 'logs_visitas_site', 'autenticado')) {
        $conexao->query('ALTER TABLE logs_visitas_site CHANGE is_authenticated autenticado TINYINT(1) NOT NULL DEFAULT 0');
    }

    if (jtech_column_exists($conexao, 'logs_visitas_site', 'user_id') && !jtech_column_exists($conexao, 'logs_visitas_site', 'id_usuario')) {
        $conexao->query('ALTER TABLE logs_visitas_site CHANGE user_id id_usuario INT NULL');
    }

    if (jtech_column_exists($conexao, 'logs_visitas_site', 'first_seen_at') && !jtech_column_exists($conexao, 'logs_visitas_site', 'primeira_visita_em')) {
        $conexao->query('ALTER TABLE logs_visitas_site CHANGE first_seen_at primeira_visita_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    if (jtech_column_exists($conexao, 'logs_visitas_site', 'last_seen_at') && !jtech_column_exists($conexao, 'logs_visitas_site', 'ultima_visita_em')) {
        $conexao->query('ALTER TABLE logs_visitas_site CHANGE last_seen_at ultima_visita_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    if (jtech_column_exists($conexao, 'logs_visitas_site', 'views_count') && !jtech_column_exists($conexao, 'logs_visitas_site', 'total_visualizacoes')) {
        $conexao->query('ALTER TABLE logs_visitas_site CHANGE views_count total_visualizacoes INT NOT NULL DEFAULT 1');
    }

    // Migra colunas antigas (inglês -> pt-BR) na tabela de usuarios.
    if (jtech_column_exists($conexao, 'visitante', 'created_at') && !jtech_column_exists($conexao, 'visitante', 'criado_em')) {
        $conexao->query('ALTER TABLE visitante CHANGE created_at criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    if (jtech_column_exists($conexao, 'visitante', 'login_count') && !jtech_column_exists($conexao, 'visitante', 'qtd_logins')) {
        $conexao->query('ALTER TABLE visitante CHANGE login_count qtd_logins INT NOT NULL DEFAULT 0');
    }

    if (jtech_column_exists($conexao, 'visitante', 'last_login_at') && !jtech_column_exists($conexao, 'visitante', 'ultimo_login_em')) {
        $conexao->query('ALTER TABLE visitante CHANGE last_login_at ultimo_login_em DATETIME NULL DEFAULT NULL');
    }

    // Garante as colunas pt-BR quando o ambiente for novo.
    if (!jtech_column_exists($conexao, 'visitante', 'criado_em')) {
        $conexao->query('ALTER TABLE visitante ADD COLUMN criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    if (!jtech_column_exists($conexao, 'visitante', 'qtd_logins')) {
        $conexao->query('ALTER TABLE visitante ADD COLUMN qtd_logins INT NOT NULL DEFAULT 0');
    }

    if (!jtech_column_exists($conexao, 'visitante', 'ultimo_login_em')) {
        $conexao->query('ALTER TABLE visitante ADD COLUMN ultimo_login_em DATETIME NULL DEFAULT NULL');
    }
}

function register_site_visit(mysqli $conexao, string $page, bool $isAuthenticated, int $userId = 0): void
{
    if (function_exists('start_secure_session')) {
        start_secure_session();
    } elseif (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $sessionId = session_id();
    if ($sessionId === '') {
        return;
    }

    $authInt = $isAuthenticated ? 1 : 0;
    $dbUserId = $userId > 0 ? $userId : null;

    $sql = "INSERT INTO logs_visitas_site (data_visita, id_sessao, pagina, autenticado, id_usuario, primeira_visita_em, ultima_visita_em, total_visualizacoes)
            VALUES (CURDATE(), ?, ?, ?, ?, NOW(), NOW(), 1)
            ON DUPLICATE KEY UPDATE
                ultima_visita_em = NOW(),
                total_visualizacoes = total_visualizacoes + 1,
                autenticado = GREATEST(autenticado, VALUES(autenticado)),
                id_usuario = COALESCE(id_usuario, VALUES(id_usuario))";

    $stmt = $conexao->prepare($sql);
    if ($stmt === false) {
        return;
    }

    $stmt->bind_param('ssii', $sessionId, $page, $authInt, $dbUserId);
    $stmt->execute();
    $stmt->close();
}

function register_user_login_metrics(mysqli $conexao, int $userId): void
{
    $stmt = $conexao->prepare('UPDATE visitante SET qtd_logins = COALESCE(qtd_logins, 0) + 1, ultimo_login_em = NOW() WHERE idvisitante = ?');
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }

    register_site_visit($conexao, 'login', true, $userId);
}

function get_admin_dashboard_metrics(mysqli $conexao): array
{
    $metrics = [
        'new_today' => 0,
        'new_week' => 0,
        'new_month' => 0,
        'returning_users' => 0,
        'visitors_only' => 0,
        'registered_total' => 0,
    ];

    $result = $conexao->query('SELECT COUNT(*) AS total FROM visitante');
    if ($result) {
        $row = $result->fetch_assoc();
        $metrics['registered_total'] = (int) ($row['total'] ?? 0);
        $result->free();
    }

    $result = $conexao->query('SELECT COUNT(*) AS total FROM visitante WHERE DATE(criado_em) = CURDATE()');
    if ($result) {
        $row = $result->fetch_assoc();
        $metrics['new_today'] = (int) ($row['total'] ?? 0);
        $result->free();
    }

    $result = $conexao->query('SELECT COUNT(*) AS total FROM visitante WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
    if ($result) {
        $row = $result->fetch_assoc();
        $metrics['new_week'] = (int) ($row['total'] ?? 0);
        $result->free();
    }

    $result = $conexao->query('SELECT COUNT(*) AS total FROM visitante WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
    if ($result) {
        $row = $result->fetch_assoc();
        $metrics['new_month'] = (int) ($row['total'] ?? 0);
        $result->free();
    }

    $result = $conexao->query('SELECT COUNT(*) AS total FROM visitante WHERE COALESCE(qtd_logins, 0) > 1');
    if ($result) {
        $row = $result->fetch_assoc();
        $metrics['returning_users'] = (int) ($row['total'] ?? 0);
        $result->free();
    }

    $visitorsOnlySql = "SELECT COUNT(DISTINCT v.id_sessao) AS total
                        FROM logs_visitas_site v
                        WHERE v.pagina = 'visitante.php'
                        AND v.id_sessao NOT IN (
                            SELECT DISTINCT a.id_sessao
                            FROM logs_visitas_site a
                            WHERE a.autenticado = 1
                        )";
    $result = $conexao->query($visitorsOnlySql);
    if ($result) {
        $row = $result->fetch_assoc();
        $metrics['visitors_only'] = (int) ($row['total'] ?? 0);
        $result->free();
    }

    return $metrics;
}
