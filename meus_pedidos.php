<?php
require_once 'includes/session.php';
start_secure_session();

$usuarioLogado = isset($_SESSION['usuario']) && trim((string) $_SESSION['usuario']) !== '';
if (!$usuarioLogado) {
    header('Location: frontend.php?mensagem=' . urlencode('Faca login para ver seus pedidos.'));
    exit;
}

require_once 'conection.php';
require_once 'includes/pedidos_loja.php';
ensure_pedidos_loja_schema($conexao);

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
$pedidos   = fetch_pedidos_usuario($conexao, $usuarioId);

// Itens para detalhe expand
$detalhe      = null;
$itensDetalhe = [];
if (isset($_GET['ver']) && (int) $_GET['ver'] > 0) {
    $verId = (int) $_GET['ver'];
    $stmtV = $conexao->prepare("SELECT * FROM pedidos_loja WHERE id = ? AND usuario_id = ?");
    if ($stmtV) {
        $stmtV->bind_param('ii', $verId, $usuarioId);
        $stmtV->execute();
        $detalhe = $stmtV->get_result()->fetch_assoc();
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
    .mp-page {
        min-height: 100vh;
        background: #eef3f8;
        padding: 24px 16px 40px;
    }

    .mp-shell {
        max-width: 880px;
        margin: 0 auto;
    }

    .mp-topo {
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
        gap: 10px;
    }

    .mp-topo h1 { margin: 0; font-size: clamp(1.3rem,3vw,1.8rem); font-weight: 800; }
    .mp-topo p  { margin: 4px 0 0; opacity: 0.9; font-size: 0.88rem; }

    .pedido-card {
        background: #fff;
        border-radius: 14px;
        margin-bottom: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .pedido-head {
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        border-bottom: 2px solid #eef3f8;
    }

    .pedido-num {
        font-size: 0.95rem;
        font-weight: 800;
        color: #10214e;
    }

    .pedido-data {
        font-size: 0.78rem;
        color: #9eaab4;
        margin-top: 2px;
    }

    .badge {
        display: inline-block;
        padding: 4px 11px;
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #fff;
    }

    .pedido-body {
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pedido-info dt { font-size: 0.75rem; color: #9eaab4; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .pedido-info dd { font-size: 0.92rem; color: #364657; font-weight: 600; margin: 0 0 8px 0; }

    .pedido-total {
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f8c80;
        text-align: right;
    }

    .pedido-total small { display: block; font-size: 0.72rem; color: #9eaab4; font-weight: 400; text-transform: uppercase; }

    .rastreio-box {
        margin: 0 18px 14px;
        background: #e8f7f5;
        border-radius: 8px;
        padding: 9px 14px;
        font-size: 0.83rem;
        color: #0a6b63;
        font-weight: 700;
    }

    .rastreio-box i { margin-right: 6px; }

    .btn-ver {
        background: #eef3f8;
        color: #364657;
        border: 1.5px solid #d0dbe8;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: background 0.13s;
    }

    .btn-ver:hover { background: #dde6ef; color: #364657; }

    .itens-tabela { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .itens-tabela th { background: #eef3f8; padding: 8px 10px; text-align: left; color: #364657; font-weight: 700; }
    .itens-tabela td { padding: 8px 10px; border-bottom: 1px solid #f0f4f8; color: #364657; }

    .sem-pedidos {
        text-align: center;
        padding: 60px 20px;
        color: #9eaab4;
    }

    .sem-pedidos i { font-size: 3rem; display: block; margin-bottom: 12px; }

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
        max-width: 640px;
        width: 100%;
        max-height: 88vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    }

    .modal-topo {
        background: linear-gradient(120deg, #10214e, #0f8c80);
        color: #fff;
        padding: 16px 20px;
        border-radius: 16px 16px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-topo h2 { margin: 0; font-size: 1rem; font-weight: 800; }
    .modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: #fff;
        font-size: 1.2rem;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        line-height: 30px;
        text-align: center;
    }

    .modal-close:hover { background: rgba(255,255,255,0.35); }
    .modal-body { padding: 18px 20px; }
</style>

<main class="mp-page">
    <div class="mp-shell">
        <header class="mp-topo">
            <div>
                <h1><i class="fa fa-list-alt" style="margin-right:10px;opacity:0.85;"></i>Meus Pedidos</h1>
                <p>Acompanhe todos os seus pedidos e rastreios.</p>
            </div>
            <a class="w3-button w3-round-large w3-border w3-border-white" style="color:#fff;font-size:0.85rem;"
               href="produto.index.php">
                <i class="fa fa-shopping-bag" style="margin-right:6px;"></i>Continuar Comprando
            </a>
        </header>

        <?php if (empty($pedidos)): ?>
            <div class="pedido-card">
                <div class="sem-pedidos">
                    <i class="fa fa-inbox"></i>
                    <p style="font-size:1rem;font-weight:700;">Voce ainda nao fez nenhum pedido.</p>
                    <a class="w3-button w3-round-large w3-teal" href="produto.index.php" style="margin-top:12px;">
                        <i class="fa fa-shopping-bag" style="margin-right:6px;"></i>Explorar Produtos
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($pedidos as $p):
                $badge = cor_status_pedido($p['status_pedido']);
                $label = label_status_pedido($p['status_pedido']);
                $dataBr = date('d/m/Y \a\s H:i', strtotime($p['criado_em']));
            ?>
                <div class="pedido-card">
                    <div class="pedido-head">
                        <div>
                            <div class="pedido-num">
                                <i class="fa fa-file-text-o" style="margin-right:6px;color:#0f8c80;"></i>
                                <?php echo htmlspecialchars($p['numero_pedido'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="pedido-data"><?php echo $dataBr; ?></div>
                        </div>
                        <span class="badge <?php echo $badge; ?>"><?php echo $label; ?></span>
                    </div>

                    <div class="pedido-body">
                        <dl class="pedido-info">
                            <dt>Pagamento</dt>
                            <dd><?php echo strtoupper(htmlspecialchars($p['forma_pagamento'], ENT_QUOTES, 'UTF-8')); ?></dd>
                        </dl>
                        <div>
                            <div class="pedido-total">
                                <small>Total do Pedido</small>
                                R$ <?php echo number_format((float) $p['total_pedido'], 2, ',', '.'); ?>
                            </div>
                            <a href="meus_pedidos.php?ver=<?php echo (int) $p['id']; ?>" class="btn-ver" style="margin-top:8px;display:inline-block;">
                                <i class="fa fa-eye" style="margin-right:4px;"></i>Ver Itens
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($p['codigo_rastreio'])): ?>
                        <div class="rastreio-box">
                            <i class="fa fa-truck"></i>Codigo de Rastreio:
                            <strong><?php echo htmlspecialchars($p['codigo_rastreio'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</main>

<!-- Modal itens do pedido -->
<?php if ($detalhe !== null): ?>
<div class="modal-overlay is-open" id="modal-itens">
    <div class="modal-box">
        <div class="modal-topo">
            <h2><i class="fa fa-file-text-o" style="margin-right:8px;"></i>
                <?php echo htmlspecialchars($detalhe['numero_pedido'], ENT_QUOTES, 'UTF-8'); ?>
            </h2>
            <button class="modal-close"
                    onclick="window.location.href='meus_pedidos.php';"
                    aria-label="Fechar">&times;</button>
        </div>
        <div class="modal-body">

            <p style="font-size:0.85rem;color:#364657;margin-bottom:14px;">
                <strong>Status:</strong>
                <span class="badge <?php echo cor_status_pedido($detalhe['status_pedido']); ?>">
                    <?php echo label_status_pedido($detalhe['status_pedido']); ?>
                </span>
                &nbsp;
                <strong>Pagamento:</strong> <?php echo strtoupper(htmlspecialchars($detalhe['forma_pagamento'], ENT_QUOTES, 'UTF-8')); ?>
                &nbsp;
                <strong>Total:</strong> R$ <?php echo number_format((float) $detalhe['total_pedido'], 2, ',', '.'); ?>
            </p>

            <?php if (!empty($itensDetalhe)): ?>
                <table class="itens-tabela">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Cor</th>
                            <th>Qtd</th>
                            <th>Preco</th>
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
            <?php else: ?>
                <p style="color:#9eaab4;font-style:italic;">Itens nao encontrados para este pedido.</p>
            <?php endif; ?>

            <?php if (!empty($detalhe['codigo_rastreio'])): ?>
                <div class="rastreio-box" style="margin: 14px 0 0;">
                    <i class="fa fa-truck"></i>Rastreio:
                    <strong><?php echo htmlspecialchars($detalhe['codigo_rastreio'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            <?php endif; ?>

            <div style="text-align:center;margin-top:16px;">
                <a href="meus_pedidos.php" class="btn-ver">
                    <i class="fa fa-arrow-left" style="margin-right:4px;"></i>Voltar aos Pedidos
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
