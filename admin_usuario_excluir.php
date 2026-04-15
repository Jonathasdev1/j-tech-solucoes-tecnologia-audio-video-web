<?php
require_once 'includes/session.php';

// DESTAQUE: endpoint de exclusao protegido para admin.
require_admin_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel_admin.php?mensagem=' . urlencode('Metodo invalido para exclusao.'));
    exit;
}

$idVisitante = (int) ($_POST['idvisitante'] ?? 0);
if ($idVisitante <= 0) {
    header('Location: painel_admin.php?mensagem=' . urlencode('Usuario invalido para exclusao.'));
    exit;
}

$usuarioIdLogado = (int) ($_SESSION['usuario_id'] ?? 0);
if ($usuarioIdLogado > 0 && $usuarioIdLogado === $idVisitante) {
    header('Location: painel_admin.php?mensagem=' . urlencode('Voce nao pode excluir seu proprio usuario admin.'));
    exit;
}

require_once 'conection.php';

$stmtDelete = $conexao->prepare('DELETE FROM visitante WHERE idvisitante = ?');
if ($stmtDelete === false) {
    $conexao->close();
    header('Location: painel_admin.php?mensagem=' . urlencode('Erro ao excluir usuario.'));
    exit;
}

$stmtDelete->bind_param('i', $idVisitante);
$ok = $stmtDelete->execute();
$stmtDelete->close();
$conexao->close();

if (!$ok) {
    header('Location: painel_admin.php?mensagem=' . urlencode('Nao foi possivel excluir o usuario selecionado.'));
    exit;
}

header('Location: painel_admin.php?mensagem=' . urlencode('Usuario excluido com sucesso.'));
exit;
