<?php
require_once 'includes/session.php';
require_admin_user();
require_once 'conection.php';

// Aceita somente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $conexao->close();
    header('Location: admin_pedidos.php');
    exit;
}

$idpedido = (int) ($_POST['idpedido'] ?? 0);

if ($idpedido <= 0) {
    $conexao->close();
    header('Location: admin_pedidos.php?erro=' . urlencode('ID de pedido inválido.'));
    exit;
}

// Obtém o número do pedido para a mensagem de confirmação
$stmtSel = $conexao->prepare("SELECT numero_pedido FROM pedidos WHERE idpedido = ? LIMIT 1");
$stmtSel->bind_param('i', $idpedido);
$stmtSel->execute();
$stmtSel->bind_result($numeroPedido);
$stmtSel->fetch();
$stmtSel->close();

if (!$numeroPedido) {
    $conexao->close();
    header('Location: admin_pedidos.php?erro=' . urlencode('Pedido não encontrado.'));
    exit;
}

$stmt = $conexao->prepare("DELETE FROM pedidos WHERE idpedido = ?");
$stmt->bind_param('i', $idpedido);
$ok = $stmt->execute();
$stmt->close();
$conexao->close();

if ($ok) {
    header('Location: admin_pedidos.php?mensagem=' . urlencode("Pedido {$numeroPedido} excluído com sucesso."));
} else {
    header('Location: admin_pedidos.php?erro=' . urlencode('Erro ao excluir o pedido.'));
}
exit;
