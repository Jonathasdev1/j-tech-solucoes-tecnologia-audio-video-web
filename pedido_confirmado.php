<?php
require_once 'includes/session.php';
start_secure_session();

$usuarioLogado = isset($_SESSION['usuario']) && trim((string) $_SESSION['usuario']) !== '';
if (!$usuarioLogado) {
    header('Location: frontend.php');
    exit;
}

$numPedido = htmlspecialchars(trim($_GET['n'] ?? ($_SESSION['ultimo_pedido_num'] ?? '')), ENT_QUOTES, 'UTF-8');
$formaPag  = $_SESSION['ultimo_pedido_pag'] ?? 'pix';

// Limpa a sessao apos exibir
unset($_SESSION['ultimo_pedido_num'], $_SESSION['ultimo_pedido_pag']);

// DESTAQUE: chave PIX ficticia - substitua pela chave real do seu negocio antes de ir para producao.
$chavePix = 'jonathas@jtech.com.br';

include 'cabecalho.php';
?>
<style>
    .confirm-page {
        min-height: 100vh;
        background: #eef3f8;
        padding: 40px 16px;
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    .confirm-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 36px rgba(0,0,0,0.10);
        max-width: 560px;
        width: 100%;
        overflow: hidden;
    }

    .confirm-topo {
        background: linear-gradient(120deg, #10214e, #0f8c80);
        color: #fff;
        padding: 30px 28px 24px;
        text-align: center;
    }

    .confirm-topo .icon-check {
        font-size: 3rem;
        display: block;
        margin-bottom: 10px;
    }

    .confirm-topo h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
    }

    .confirm-topo p {
        margin: 8px 0 0;
        opacity: 0.9;
        font-size: 0.92rem;
    }

    .confirm-body {
        padding: 24px 28px;
    }

    .pedido-num-box {
        background: #eef3f8;
        border-radius: 10px;
        padding: 14px 18px;
        text-align: center;
        margin-bottom: 22px;
    }

    .pedido-num-box small {
        display: block;
        font-size: 0.75rem;
        color: #7a8896;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    .pedido-num-box strong {
        display: block;
        font-size: 1.4rem;
        font-weight: 800;
        color: #10214e;
        letter-spacing: 0.04em;
    }

    .pix-box {
        border: 2px dashed #0f8c80;
        border-radius: 12px;
        padding: 18px 20px;
        text-align: center;
        margin-bottom: 20px;
    }

    .pix-box h3 {
        margin: 0 0 8px;
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f8c80;
    }

    .pix-chave {
        background: #f0faf9;
        border: 1.5px solid #b2e4df;
        border-radius: 8px;
        padding: 10px 14px;
        font-family: monospace;
        font-size: 1rem;
        font-weight: 700;
        color: #10214e;
        word-break: break-all;
        margin: 10px 0;
        display: block;
    }

    .pix-instrucoes {
        font-size: 0.8rem;
        color: #7a8896;
        line-height: 1.5;
    }

    .pix-instrucoes li { margin-bottom: 4px; }

    .boleto-box {
        border: 2px dashed #e69a00;
        border-radius: 12px;
        padding: 18px 20px;
        text-align: center;
        margin-bottom: 20px;
    }

    .boleto-box h3 { color: #b07400; font-size: 0.95rem; font-weight: 800; margin: 0 0 8px; }
    .boleto-box p  { font-size: 0.85rem; color: #5d4200; }

    .cartao-box {
        border: 2px dashed #3355cc;
        border-radius: 12px;
        padding: 18px 20px;
        text-align: center;
        margin-bottom: 20px;
    }

    .cartao-box h3 { color: #3355cc; font-size: 0.95rem; font-weight: 800; margin: 0 0 8px; }
    .cartao-box p  { font-size: 0.85rem; color: #232d5e; }

    .confirm-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .confirm-actions a {
        flex: 1;
        min-width: 120px;
        text-align: center;
        text-decoration: none;
        padding: 11px 14px;
        border-radius: 9px;
        font-weight: 700;
        font-size: 0.88rem;
    }

    .btn-pedidos {
        background: #0f8c80;
        color: #fff;
    }

    .btn-pedidos:hover { background: #0a6b63; color: #fff; }

    .btn-continuar {
        background: #eef3f8;
        color: #364657;
        border: 1.5px solid #d0dbe8;
    }

    .btn-continuar:hover { background: #dde6ef; color: #364657; }
</style>

<main class="confirm-page">
    <div class="confirm-card">

        <div class="confirm-topo">
            <i class="fa fa-check-circle icon-check"></i>
            <h1>Pedido Realizado!</h1>
            <p>Recebemos seu pedido. Agora so falta o pagamento.</p>
        </div>

        <div class="confirm-body">

            <?php if ($numPedido !== ''): ?>
                <div class="pedido-num-box">
                    <small>Numero do Pedido</small>
                    <strong><?php echo $numPedido; ?></strong>
                </div>
            <?php endif; ?>

            <?php if ($formaPag === 'pix'): ?>
                <div class="pix-box">
                    <h3><i class="fa fa-qrcode" style="margin-right:6px;"></i>Pague via PIX</h3>
                    <p style="font-size:0.82rem;color:#364657;margin:0 0 6px;">
                        Copie a chave PIX abaixo e realize o pagamento no seu banco:
                    </p>
                    <code class="pix-chave"><?php echo htmlspecialchars($chavePix, ENT_QUOTES, 'UTF-8'); ?></code>
                    <p style="font-size:0.78rem;color:#0f8c80;font-weight:700;margin:8px 0 10px;">
                        Valor: confira no seu extrato o numero do pedido acima
                    </p>
                    <ul class="pix-instrucoes" style="text-align:left;list-style:none;padding:0;margin:0;">
                        <li><i class="fa fa-check" style="color:#0f8c80;margin-right:6px;"></i>Abra o app do seu banco</li>
                        <li><i class="fa fa-check" style="color:#0f8c80;margin-right:6px;"></i>Escolha "Pagar com PIX" e cole a chave</li>
                        <li><i class="fa fa-check" style="color:#0f8c80;margin-right:6px;"></i>Informe no campo de descricao o numero do pedido</li>
                        <li><i class="fa fa-check" style="color:#0f8c80;margin-right:6px;"></i>Seu pedido sera confirmado em ate 1h apos o pagamento</li>
                    </ul>
                </div>

            <?php elseif ($formaPag === 'boleto'): ?>
                <div class="boleto-box">
                    <h3><i class="fa fa-barcode" style="margin-right:6px;"></i>Pagamento por Boleto</h3>
                    <p>O boleto bancario sera gerado em breve e enviado para o e-mail cadastrado.</p>
                    <p><strong>Vencimento: 3 dias uteis.</strong></p>
                </div>

            <?php else: ?>
                <div class="cartao-box">
                    <h3><i class="fa fa-credit-card" style="margin-right:6px;"></i>Pagamento por Cartao</h3>
                    <p>Em breve voce recebera o link seguro para processar o pagamento com cartao de credito ou debito.</p>
                </div>
            <?php endif; ?>

            <div style="font-size:0.82rem;color:#9eaab4;text-align:center;padding:10px 0 4px;border-top:1px solid #eef3f8;">
                Duvidas? Entre em contato via WhatsApp ou e-mail. Acompanhe o status em "Meus Pedidos".
            </div>

            <div class="confirm-actions">
                <a href="meus_pedidos.php" class="btn-pedidos">
                    <i class="fa fa-list" style="margin-right:6px;"></i>Meus Pedidos
                </a>
                <a href="produto.index.php" class="btn-continuar">
                    <i class="fa fa-shopping-bag" style="margin-right:6px;"></i>Continuar Comprando
                </a>
            </div>
        </div>

    </div>
</main>

<script>
// DESTAQUE: limpa o carrinho do localStorage apos o pedido ser confirmado.
(function () {
    var keys = Object.keys(localStorage).filter(function (k) { return k.startsWith('jtech_cart_'); });
    keys.forEach(function (k) { localStorage.removeItem(k); });
})();
</script>

<?php include 'footer.php'; ?>
