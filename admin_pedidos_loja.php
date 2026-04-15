<?php
require_once 'includes/session.php';
require_admin_user();
require_once 'conection.php';
require_once 'includes/pedidos_loja.php';
ensure_pedidos_loja_schema($conexao);

$mensagem = '';
$tipoMsg  = '';

// ── Processar atualizacao de status / rastreio ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao     = trim($_POST['acao']      ?? '');
    $pedidoId = (int) ($_POST['pedido_id'] ?? 0);

    if ($acao === 'atualizar' && $pedidoId > 0) {
        $novoStatus   = trim($_POST['status_pedido']   ?? '');
        $rastreio     = trim($_POST['codigo_rastreio'] ?? '');
        $statusValidos = ['aguardando_pagamento','pago','separando','enviado','entregue','cancelado'];

        if (!in_array($novoStatus, $statusValidos)) {
            $mensagem = 'Status invalido.';
            $tipoMsg  = 'erro';
        } else {
            $stmt = $conexao->prepare(
                "UPDATE pedidos_loja SET status_pedido = ?, codigo_rastreio = ?, atualizado_em = NOW()
                 WHERE id = ?"
            );
            if ($stmt) {
                $stmt->bind_param('ssi', $novoStatus, $rastreio, $pedidoId);
                if ($stmt->execute()) {
                    $mensagem = 'Pedido #' . $pedidoId . ' atualizado com sucesso.';
                    $tipoMsg  = 'ok';
                } else {
                    $mensagem = 'Erro ao atualizar o pedido.';
                    $tipoMsg  = 'erro';
                }
                $stmt->close();
            }
        }
    }
}

// ── Filtros de busca ──────────────────────────────────────────────────────
$filtroStatus = trim($_GET['status'] ?? '');
$busca        = trim($_GET['busca']  ?? '');

$pedidos = fetch_pedidos_loja($conexao, $filtroStatus, $busca);

// ── Detalhe de pedido individual ──────────────────────────────────────────
$detalhe = null;
$itensDetalhe = [];
if (isset($_GET['ver']) && (int) $_GET['ver'] > 0) {
    $verId = (int) $_GET['ver'];
    $stmtV = $conexao->prepare(
        "SELECT * FROM pedidos_loja WHERE id = ?"
    );
    if ($stmtV) {
        $stmtV->bind_param('i', $verId);
        $stmtV->execute();
        $resV = $stmtV->get_result();
        $detalhe = $resV->fetch_assoc();
        $stmtV->close();
    }
    if ($detalhe) {
        $itensDetalhe = fetch_itens_pedido($conexao, $verId);
    }
}

$conexao->close();
include 'cabecalho.php';
?>
<style>
    .adm-page {
        min-height: 100vh;
        background: #eef3f8;
        padding: 24px 16px 40px;
    }

    .adm-shell {
        max-width: 1200px;
        margin: 0 auto;
    }

    .adm-topo {
        background: linear-gradient(120deg, #10214e, #0f8c80);
        color: #fff;
        border-radius: 16px;
        padding: 22px 26px;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.14);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .adm-topo h1 { margin: 0; font-size: clamp(1.3rem,3vw,1.8rem); font-weight: 800; }
    .adm-topo p  { margin: 4px 0 0; opacity: 0.9; font-size: 0.88rem; }

    .filtros-bar {
        background: #fff;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 16px;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .filtros-bar input, .filtros-bar select {
        padding: 7px 10px;
        border: 1.5px solid #d0dbe8;
        border-radius: 8px;
        font-size: 0.88rem;
        outline: none;
    }

    .filtros-bar input:focus, .filtros-bar select:focus { border-color: #0f8c80; }

    .tabela-wrap {
        background: #fff;
        border-radius: 14px;
        overflow-x: auto;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead tr { background: #10214e; color: #fff; }
    thead th { padding: 11px 12px; text-align: left; font-size: 0.82rem; font-weight: 700; white-space: nowrap; }
    tbody tr:nth-child(even) { background: #f7fafc; }
    tbody tr:hover { background: #e8f5f4; }
    tbody td { padding: 10px 12px; font-size: 0.85rem; color: #364657; vertical-align: middle; }

    .badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
    }

    .badge-pag { background: #cc6600; }

    .btn-ver {
        background: #0f8c80;
        color: #fff;
        border: none;
        padding: 5px 12px;
        border-radius: 7px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-ver:hover { background: #0a6b63; color: #fff; }

    /* Modal detalhe */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.55);
        z-index: 5000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.is-open { display: flex; }

    .modal-box {
        background: #fff;
        border-radius: 16px;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    }

    .modal-topo {
        background: linear-gradient(120deg, #10214e, #0f8c80);
        color: #fff;
        padding: 18px 22px;
        border-radius: 16px 16px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-topo h2 { margin: 0; font-size: 1.1rem; font-weight: 800; }

    .modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: #fff;
        font-size: 1.2rem;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        line-height: 32px;
        text-align: center;
    }

    .modal-close:hover { background: rgba(255,255,255,0.35); }

    .modal-body { padding: 20px 22px; }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 18px; margin-bottom: 16px; }
    .info-item small { display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: #9eaab4; font-weight: 700; }
    .info-item strong { font-size: 0.92rem; color: #10214e; }

    @media (max-width: 600px) {
        .info-grid { grid-template-columns: 1fr; }
    }

    .itens-tabela { width: 100%; border-collapse: collapse; font-size: 0.85rem; margin-bottom: 14px; }
    .itens-tabela th { background: #eef3f8; padding: 8px 10px; text-align: left; color: #364657; font-weight: 700; }
    .itens-tabela td { padding: 8px 10px; border-bottom: 1px solid #f0f4f8; color: #364657; }

    .form-atualiza { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; padding-top: 12px; border-top: 1px solid #eef3f8; }
    .form-atualiza select, .form-atualiza input[type="text"] {
        padding: 7px 10px;
        border: 1.5px solid #d0dbe8;
        border-radius: 8px;
        font-size: 0.88rem;
        flex: 1;
        min-width: 140px;
    }

    .btn-salvar {
        background: #0f8c80;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        font-size: 0.88rem;
    }

    .btn-salvar:hover { background: #0a6b63; }

    .msg-ok   { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px; padding: 10px 14px; margin-bottom: 14px; font-size: 0.88rem; }
    .msg-erro { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; padding: 10px 14px; margin-bottom: 14px; font-size: 0.88rem; }

    .sem-pedidos { text-align: center; padding: 40px 20px; color: #9eaab4; font-style: italic; }
</style>

<main class="adm-page">
    <div class="adm-shell">

        <header class="adm-topo">
            <div>
                <h1><i class="fa fa-shopping-bag" style="margin-right:10px;opacity:0.85;"></i>Pedidos da Loja</h1>
                <p>Gerencie, atualize e acompanhe todos os pedidos.</p>
            </div>
            <a class="w3-button w3-round-large w3-border w3-border-white" style="color:#fff;font-size:0.85rem;" href="painel_admin.php">
                <i class="fa fa-arrow-left" style="margin-right:6px;"></i>Painel Admin
            </a>
        </header>

        <?php if ($mensagem !== ''): ?>
            <div class="msg-<?php echo $tipoMsg === 'ok' ? 'ok' : 'erro'; ?>">
                <i class="fa fa-<?php echo $tipoMsg === 'ok' ? 'check' : 'exclamation-triangle'; ?>" style="margin-right:6px;"></i>
                <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <form method="GET" action="admin_pedidos_loja.php" class="filtros-bar">
            <input type="text" name="busca" placeholder="Buscar por numero, nome ou CPF..."
                   value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>"
                   style="flex:1;min-width:200px;">
            <select name="status">
                <option value="">Todos os Status</option>
                <?php
                $statusOpts = [
                    'aguardando_pagamento' => 'Aguardando Pagamento',
                    'pago'                 => 'Pago',
                    'separando'            => 'Separando',
                    'enviado'              => 'Enviado',
                    'entregue'             => 'Entregue',
                    'cancelado'            => 'Cancelado',
                ];
                foreach ($statusOpts as $val => $label):
                    $sel = ($filtroStatus === $val) ? 'selected' : '';
                ?>
                    <option value="<?php echo $val; ?>" <?php echo $sel; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-ver" style="white-space:nowrap;">
                <i class="fa fa-search" style="margin-right:4px;"></i>Filtrar
            </button>
            <?php if ($busca !== '' || $filtroStatus !== ''): ?>
                <a href="admin_pedidos_loja.php" class="btn-ver" style="background:#7a8896;">Limpar</a>
            <?php endif; ?>
        </form>

        <!-- Tabela de pedidos -->
        <div class="tabela-wrap">
            <?php if (empty($pedidos)): ?>
                <p class="sem-pedidos">
                    <i class="fa fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                    Nenhum pedido encontrado.
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Numero</th>
                            <th>Cliente</th>
                            <th>Cidade/UF</th>
                            <th>Total</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p):
                            $badge = cor_status_pedido($p['status_pedido']);
                            $label = label_status_pedido($p['status_pedido']);
                            $dataBr = date('d/m/y H:i', strtotime($p['criado_em']));
                        ?>
                            <tr>
                                <td><?php echo (int) $p['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($p['numero_pedido'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['nome_cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($p['cidade'] . '/' . $p['estado'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><strong>R$ <?php echo number_format((float) $p['total_pedido'], 2, ',', '.'); ?></strong></td>
                                <td><?php echo strtoupper(htmlspecialchars($p['forma_pagamento'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td>
                                    <span class="badge <?php echo $badge; ?>"><?php echo $label; ?></span>
                                </td>
                                <td><?php echo $dataBr; ?></td>
                                <td>
                                    <a href="admin_pedidos_loja.php?ver=<?php echo (int) $p['id']; ?><?php echo $busca !== '' ? '&busca=' . urlencode($busca) : ''; ?><?php echo $filtroStatus !== '' ? '&status=' . urlencode($filtroStatus) : ''; ?>"
                                       class="btn-ver">
                                        <i class="fa fa-eye" style="margin-right:4px;"></i>Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- ── Modal detalhe de pedido ──────────────────────────────────────── -->
<?php if ($detalhe !== null): ?>
<div class="modal-overlay is-open" id="modal-detalhe">
    <div class="modal-box">
        <div class="modal-topo">
            <h2><i class="fa fa-file-text-o" style="margin-right:8px;"></i>
                Pedido <?php echo htmlspecialchars($detalhe['numero_pedido'], ENT_QUOTES, 'UTF-8'); ?>
            </h2>
            <button class="modal-close" onclick="document.getElementById('modal-detalhe').classList.remove('is-open');"
                    aria-label="Fechar">&times;</button>
        </div>
        <div class="modal-body">

            <div class="info-grid">
                <div class="info-item">
                    <small>Cliente</small>
                    <strong><?php echo htmlspecialchars($detalhe['nome_cliente'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="info-item">
                    <small>CPF</small>
                    <strong><?php
                        $cpf = $detalhe['cpf_cliente'];
                        if (strlen($cpf) === 11) {
                            echo htmlspecialchars(substr($cpf,0,3).'.'.substr($cpf,3,3).'.'.substr($cpf,6,3).'-'.substr($cpf,9,2));
                        } else {
                            echo htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8');
                        }
                    ?></strong>
                </div>
                <div class="info-item">
                    <small>Endereco</small>
                    <strong><?php echo htmlspecialchars(
                        $detalhe['logradouro'] . ', ' . $detalhe['numero_end'] .
                        ($detalhe['complemento'] !== '' ? ' - ' . $detalhe['complemento'] : ''),
                        ENT_QUOTES, 'UTF-8'
                    ); ?></strong>
                </div>
                <div class="info-item">
                    <small>Bairro / Cidade / UF</small>
                    <strong><?php echo htmlspecialchars(
                        $detalhe['bairro'] . ' — ' . $detalhe['cidade'] . '/' . $detalhe['estado'],
                        ENT_QUOTES, 'UTF-8'
                    ); ?></strong>
                </div>
                <div class="info-item">
                    <small>CEP</small>
                    <strong><?php
                        $cep = $detalhe['cep'];
                        echo strlen($cep) === 8
                            ? htmlspecialchars(substr($cep,0,5).'-'.substr($cep,5,3))
                            : htmlspecialchars($cep, ENT_QUOTES, 'UTF-8');
                    ?></strong>
                </div>
                <div class="info-item">
                    <small>Pagamento</small>
                    <strong><?php echo strtoupper(htmlspecialchars($detalhe['forma_pagamento'], ENT_QUOTES, 'UTF-8')); ?></strong>
                </div>
                <div class="info-item">
                    <small>Total</small>
                    <strong style="color:#0f8c80;">R$ <?php echo number_format((float) $detalhe['total_pedido'], 2, ',', '.'); ?></strong>
                </div>
                <div class="info-item">
                    <small>Data do Pedido</small>
                    <strong><?php echo date('d/m/Y H:i', strtotime($detalhe['criado_em'])); ?></strong>
                </div>
                <?php if ($detalhe['codigo_rastreio'] !== ''): ?>
                <div class="info-item">
                    <small>Codigo de Rastreio</small>
                    <strong><?php echo htmlspecialchars($detalhe['codigo_rastreio'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <?php endif; ?>
            </div>

            <!-- Itens do pedido -->
            <?php if (!empty($itensDetalhe)): ?>
                <h3 style="font-size:0.88rem;font-weight:800;color:#364657;margin-bottom:8px;">
                    <i class="fa fa-list" style="margin-right:6px;color:#0f8c80;"></i>Itens do Pedido
                </h3>
                <table class="itens-tabela">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Cor</th>
                            <th>Qtd</th>
                            <th>Preco Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itensDetalhe as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['produto_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['cor'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $item['quantidade']; ?></td>
                                <td>R$ <?php echo number_format((float) $item['produto_preco'], 2, ',', '.'); ?></td>
                                <td><strong>R$ <?php echo number_format((float) $item['subtotal'], 2, ',', '.'); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Formulario de atualizacao -->
            <form method="POST" action="admin_pedidos_loja.php" class="form-atualiza">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="pedido_id" value="<?php echo (int) $detalhe['id']; ?>">
                <select name="status_pedido">
                    <?php foreach ($statusOpts as $val => $labelOpt):
                        $sel = ($detalhe['status_pedido'] === $val) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $val; ?>" <?php echo $sel; ?>>
                            <?php echo htmlspecialchars($labelOpt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="codigo_rastreio" placeholder="Codigo de rastreio (opcional)"
                       maxlength="60"
                       value="<?php echo htmlspecialchars($detalhe['codigo_rastreio'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn-salvar">
                    <i class="fa fa-save" style="margin-right:6px;"></i>Salvar
                </button>
            </form>

        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
