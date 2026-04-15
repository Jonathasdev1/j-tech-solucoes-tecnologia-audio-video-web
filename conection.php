<?php
function ensure_core_auth_schema(mysqli $conexao): void
{
    $conexao->query(
        "CREATE TABLE IF NOT EXISTS visitante (
            idvisitante INT(11) NOT NULL AUTO_INCREMENT,
            nome VARCHAR(150) NOT NULL,
            senha VARCHAR(255) NOT NULL,
            perfil ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            qtd_logins INT NOT NULL DEFAULT 0,
            ultimo_login_em DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (idvisitante),
            UNIQUE KEY uq_nome (nome)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $adminHash = '$2y$10$.zCiWfu95kbyXx./Q8g3o.R00biwoippO46djZEcXAzuiOI/RGJlq';
    $stmt = $conexao->prepare('INSERT IGNORE INTO visitante (nome, senha, perfil) VALUES (?, ?, ?)');
    if ($stmt) {
        $nomeAdmin = 'admin';
        $perfilAdmin = 'admin';
        $stmt->bind_param('sss', $nomeAdmin, $adminHash, $perfilAdmin);
        $stmt->execute();
        $stmt->close();
    }
}

// DESTAQUE: usa variaveis de ambiente em producao (Render) com fallback para XAMPP local.
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'visitante-j-tech';
$dbport = (int) (getenv('DB_PORT') ?: 3306);

// Cria a conexao com o banco de dados.
$conexao = new mysqli($servername, $username, $password, $dbname, $dbport);
if ($conexao->connect_error) {
    die('Connection failed: ' . $conexao->connect_error);
}

$conexao->set_charset('utf8mb4');
ensure_core_auth_schema($conexao);
?>