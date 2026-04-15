<?php
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
?>