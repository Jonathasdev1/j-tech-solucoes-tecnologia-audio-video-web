<?php
require_once 'includes/session.php';
require_admin_user();
require_once 'conection.php';

$acao = trim($_POST['acao'] ?? '');

// ── Sanitize inputs ──────────────────────────────────────────────────────
$numeroPedido = trim($_POST['numero_pedido']  ?? '');
$idvisitante  = (int) ($_POST['idvisitante']  ?? 0);
$categoria    = trim($_POST['categoria']      ?? '');
$descricao    = trim($_POST['descricao']      ?? '');
$valorRaw     = str_replace(',', '.', trim($_POST['valor'] ?? '0'));
$valor        = round((float) $valorRaw, 2);
$statusPedido = trim($_POST['status_pedido']  ?? 'Pendente');
$observacao   = trim($_POST['observacao']     ?? '');
$idpedido     = (int) ($_POST['idpedido']     ?? 0);

// ── Whitelist de valores ENUM ────────────────────────────────────────────
$categoriasPermitidas   = ['Academia','Restaurante','Cinema','Dentista','Outro'];
$statusPermitidos       = ['Pendente','Aprovado','Concluido','Cancelado'];

// ── Validações ────────────────────────────────────────────────────────────
$erros = [];

if ($acao !== 'novo' && $acao !== 'editar') {
    $erros[] = 'Ação inválida.';
}
if ($numeroPedido === '' || strlen($numeroPedido) > 20) {
    $erros[] = 'Número do pedido inválido.';
}
if ($idvisitante <= 0) {
    $erros[] = 'Cliente não informado.';
}
if (!in_array($categoria, $categoriasPermitidas, true)) {
    $erros[] = 'Categoria inválida.';
}
if ($descricao === '') {
    $erros[] = 'Descrição não informada.';
}
if ($valor < 0) {
    $erros[] = 'Valor inválido.';
}
if (!in_array($statusPedido, $statusPermitidos, true)) {
    $erros[] = 'Status inválido.';
}
if ($acao === 'editar' && $idpedido <= 0) {
    $erros[] = 'ID do pedido inválido.';
}

if (count($erros) > 0) {
    $volta = ($acao === 'editar')
        ? 'admin_pedido_editar.php?id=' . $idpedido . '&erro=' . urlencode(implode(' | ', $erros))
        : 'admin_pedido_novo.php?erro=' . urlencode(implode(' | ', $erros));
    $conexao->close();
    header('Location: ' . $volta);
    exit;
}

// ── Verifica se o cliente existe ─────────────────────────────────────────
$stmtC = $conexao->prepare("SELECT idvisitante FROM visitante WHERE idvisitante = ? LIMIT 1");
$stmtC->bind_param('i', $idvisitante);
$stmtC->execute();
$stmtC->store_result();
if ($stmtC->num_rows === 0) {
    $stmtC->close();
    $conexao->close();
    header('Location: admin_pedido_novo.php?erro=' . urlencode('Cliente não encontrado.'));
    exit;
}
$stmtC->close();

// ── Persistir ─────────────────────────────────────────────────────────────
if ($acao === 'novo') {
    // tipos: s=numero_pedido, i=idvisitante, s=categoria, s=descricao, d=valor, s=status_pedido, s=observacao
    $stmt = $conexao->prepare(
        "INSERT INTO pedidos (numero_pedido, idvisitante, categoria, descricao, valor, status_pedido, observacao, criado_em, atualizado_em)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
    );
    $stmt->bind_param('sissdss', $numeroPedido, $idvisitante, $categoria, $descricao, $valor, $statusPedido, $observacao);
    $ok = $stmt->execute();
    $stmt->close();
    $conexao->close();

    if ($ok) {
        header('Location: admin_pedidos.php?mensagem=' . urlencode("Pedido {$numeroPedido} criado com sucesso!"));
    } else {
        header('Location: admin_pedido_novo.php?erro=' . urlencode('Erro ao salvar o pedido.'));
    }
    exit;
}

// ── Edição ────────────────────────────────────────────────────────────────
if ($acao === 'editar') {
    // tipos: i=idvisitante, s=categoria, s=descricao, d=valor, s=status_pedido, s=observacao, i=idpedido
    $stmt = $conexao->prepare(
        "UPDATE pedidos
         SET idvisitante=?, categoria=?, descricao=?, valor=?,
             status_pedido=?, observacao=?, atualizado_em=NOW()
         WHERE idpedido=?"
    );
    $stmt->bind_param('issdssi', $idvisitante, $categoria, $descricao, $valor, $statusPedido, $observacao, $idpedido);
    $ok = $stmt->execute();
    $stmt->close();
    $conexao->close();

    if ($ok) {
        header('Location: admin_pedidos.php?mensagem=' . urlencode("Pedido {$numeroPedido} atualizado com sucesso!"));
    } else {
        header('Location: admin_pedido_editar.php?id=' . $idpedido . '&erro=' . urlencode('Erro ao atualizar o pedido.'));
    }
    exit;
}

$conexao->close();
header('Location: admin_pedidos.php');
exit;
