<?php
require_once 'includes/session.php';
start_secure_session();

// DESTAQUE: checkout exige usuario autenticado para finalizar o pedido.
$usuarioLogado = isset($_SESSION['usuario']) && trim((string) $_SESSION['usuario']) !== '';
if (!$usuarioLogado) {
    header('Location: frontend.php?mensagem=' . urlencode('Faca login para finalizar o pedido.'));
    exit;
}

require_once 'conection.php';
require_once 'includes/pedidos_loja.php';
ensure_pedidos_loja_schema($conexao);

$usuarioId   = (int) ($_SESSION['usuario_id'] ?? 0);
$nomeUsuario = (string) ($_SESSION['usuario'] ?? '');

$erros    = [];
$campos   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Leitura de inputs ─────────────────────────────────────────
    $cartData       = trim($_POST['cart_data'] ?? '');
    $nomeCliente    = trim($_POST['nome_cliente']    ?? '');
    $cpfRaw         = preg_replace('/\D/', '', trim($_POST['cpf_cliente']    ?? ''));
    $cepRaw         = preg_replace('/\D/', '', trim($_POST['cep']            ?? ''));
    $logradouro     = trim($_POST['logradouro']      ?? '');
    $numeroEnd      = trim($_POST['numero_end']      ?? '');
    $complemento    = trim($_POST['complemento']     ?? '');
    $bairro         = trim($_POST['bairro']          ?? '');
    $cidade         = trim($_POST['cidade']          ?? '');
    $estadoRaw      = strtoupper(trim($_POST['estado'] ?? ''));
    $formaPagamento = trim($_POST['forma_pagamento'] ?? 'pix');

    $campos = compact('nomeCliente', 'logradouro', 'numeroEnd', 'complemento', 'bairro', 'cidade', 'estadoRaw');

    // ── Validacao ─────────────────────────────────────────────────
    if ($nomeCliente === '')                 $erros[] = 'Nome completo e obrigatorio.';
    if (strlen($cpfRaw) !== 11)             $erros[] = 'CPF invalido (informe 11 digitos).';
    if (strlen($cepRaw) !== 8)              $erros[] = 'CEP invalido (informe 8 digitos).';
    if ($logradouro === '')                  $erros[] = 'Logradouro e obrigatorio.';
    if ($numeroEnd === '')                   $erros[] = 'Numero do endereco e obrigatorio.';
    if ($bairro === '')                      $erros[] = 'Bairro e obrigatorio.';
    if ($cidade === '')                      $erros[] = 'Cidade e obrigatoria.';
    if (strlen($estadoRaw) !== 2)           $erros[] = 'Estado invalido (sigla com 2 letras).';
    if (!in_array($formaPagamento, ['pix', 'boleto', 'cartao'])) {
        $formaPagamento = 'pix';
    }

    // ── Carrinho ──────────────────────────────────────────────────
    $itens = [];
    if ($cartData !== '') {
        $decoded = json_decode($cartData, true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (!empty($item['name']) && isset($item['price'])) {
                    $itens[] = [
                        'name'  => (string) $item['name'],
                        'price' => (float)  $item['price'],
                        'qty'   => max(1, (int) ($item['qty'] ?? 1)),
                        'color' => (string) ($item['color'] ?? ''),
                    ];
                }
            }
        }
    }
    if (empty($itens)) {
        $erros[] = 'Carrinho vazio ou invalido. Adicione produtos antes de finalizar.';
    }

    // ── Salvar pedido ─────────────────────────────────────────────
    if (empty($erros)) {
        $totalItens  = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $itens));
        $frete       = 0.00;
        $totalPedido = $totalItens + $frete;

        $numeroPedido = 'JT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $stmtP = $conexao->prepare(
            "INSERT INTO pedidos_loja
             (numero_pedido, usuario_id, nome_cliente, cpf_cliente, cep,
              logradouro, numero_end, complemento, bairro, cidade, estado,
              frete, total_itens, total_pedido, forma_pagamento)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmtP === false) {
            $erros[] = 'Erro interno ao preparar pedido.';
        } else {
            $stmtP->bind_param(
                'siissssssssddds',
                $numeroPedido, $usuarioId, $nomeCliente, $cpfRaw, $cepRaw,
                $logradouro, $numeroEnd, $complemento, $bairro, $cidade, $estadoRaw,
                $frete, $totalItens, $totalPedido, $formaPagamento
            );

            if ($stmtP->execute()) {
                $pedidoId = (int) $conexao->insert_id;

                $stmtI = $conexao->prepare(
                    "INSERT INTO pedido_itens
                     (pedido_id, produto_nome, produto_preco, quantidade, cor, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );

                if ($stmtI) {
                    foreach ($itens as $item) {
                        $sub = $item['price'] * $item['qty'];
                        $stmtI->bind_param('isdids',
                            $pedidoId, $item['name'], $item['price'],
                            $item['qty'], $item['color'], $sub
                        );
                        $stmtI->execute();
                    }
                    $stmtI->close();
                }

                $stmtP->close();
                $conexao->close();

                // DESTAQUE: redireciona para confirmacao e limpa o carrinho pelo lado do cliente.
                $_SESSION['ultimo_pedido_num'] = $numeroPedido;
                $_SESSION['ultimo_pedido_pag'] = $formaPagamento;
                header('Location: pedido_confirmado.php?n=' . urlencode($numeroPedido));
                exit;

            } else {
                $erros[] = 'Erro ao salvar o pedido. Tente novamente.';
                $stmtP->close();
            }
        }
    }
}

$conexao->close();
include 'cabecalho.php';
?>
<style>
    .checkout-page {
        min-height: 100vh;
        background: #eef3f8;
        padding: 24px 16px 40px;
    }

    .checkout-shell {
        max-width: 960px;
        margin: 0 auto;
    }

    .checkout-topo {
        background: linear-gradient(120deg, #10214e, #0f8c80);
        color: #fff;
        border-radius: 16px;
        padding: 22px 24px;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.14);
    }

    .checkout-topo h1 { margin: 0; font-size: clamp(1.4rem,3vw,2rem); font-weight: 800; }
    .checkout-topo p  { margin: 6px 0 0; opacity: 0.9; }

    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 18px;
    }

    @media (max-width: 780px) {
        .checkout-grid { grid-template-columns: 1fr; }
    }

    .secc {
        background: #fff;
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }

    .secc h2 {
        margin: 0 0 16px;
        font-size: 1rem;
        font-weight: 800;
        color: #10214e;
        border-bottom: 2px solid #eef3f8;
        padding-bottom: 8px;
    }

    .form-row { margin-bottom: 12px; }
    .form-row label { display: block; font-size: 0.82rem; font-weight: 700; color: #364657; margin-bottom: 4px; }
    .form-row input, .form-row select {
        width: 100%;
        padding: 8px 10px;
        border: 1.5px solid #d0dbe8;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #232d3a;
        background: #f7fafc;
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.15s;
    }
    .form-row input:focus, .form-row select:focus { border-color: #0f8c80; background: #fff; }
    .form-row-duo { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

    .pag-opts { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px; }
    .pag-opt  { flex: 1; min-width: 90px; }
    .pag-opt input[type="radio"] { display: none; }
    .pag-opt label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 12px 8px;
        border: 2px solid #d0dbe8;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 700;
        color: #364657;
        transition: border-color 0.15s, background 0.15s;
        text-align: center;
    }
    .pag-opt label i { font-size: 1.4rem; opacity: 0.7; }
    .pag-opt input[type="radio"]:checked + label {
        border-color: #0f8c80;
        background: #e8f7f5;
        color: #0f8c80;
    }

    #cart-resume { min-height: 60px; font-size: 0.88rem; }
    .cart-r-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 8px 0;
        border-bottom: 1px solid #f0f4f8;
        gap: 8px;
        font-size: 0.85rem;
    }
    .cart-r-row:last-child { border-bottom: none; }
    .cart-r-name { color: #364657; flex: 1; }
    .cart-r-price { font-weight: 700; color: #10214e; white-space: nowrap; }
    .cart-r-empty { color: #9eaab4; font-style: italic; padding: 12px 0; text-align: center; }

    .total-line {
        display: flex;
        justify-content: space-between;
        padding: 10px 0 4px;
        font-weight: 800;
        font-size: 1.05rem;
        color: #10214e;
        border-top: 2px solid #eef3f8;
        margin-top: 8px;
    }

    .frete-line {
        display: flex;
        justify-content: space-between;
        font-size: 0.82rem;
        color: #0f8c80;
        font-weight: 700;
        padding: 4px 0;
    }

    .btn-finalizar {
        width: 100%;
        background: #0f8c80;
        color: #fff;
        font-weight: 800;
        font-size: 1rem;
        padding: 13px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        margin-top: 16px;
        transition: background 0.15s;
    }
    .btn-finalizar:hover { background: #0a6b63; }

    .alerta-erros {
        background: #fde8e8;
        border: 1.5px solid #f5c6c6;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 16px;
        color: #a10000;
        font-size: 0.88rem;
    }
    .alerta-erros ul { margin: 6px 0 0 18px; padding: 0; }
</style>

<main class="checkout-page">
    <div class="checkout-shell">
        <header class="checkout-topo">
            <h1><i class="fa fa-lock" style="margin-right:10px;opacity:0.85;"></i>Finalizar Pedido</h1>
            <p>Preencha seus dados de entrega e escolha a forma de pagamento.</p>
        </header>

        <?php if (!empty($erros)): ?>
            <div class="alerta-erros">
                <strong>Corrija os erros antes de continuar:</strong>
                <ul>
                    <?php foreach ($erros as $e): ?>
                        <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="checkout-form" method="POST" action="checkout.php">
            <!-- DESTAQUE: cart_data e preenchido pelo JS antes do envio. -->
            <input type="hidden" name="cart_data" id="ck-cart-data">

            <div class="checkout-grid">

                <!-- Coluna esquerda: endereco + pagamento -->
                <div>
                    <div class="secc" style="margin-bottom:18px;">
                        <h2><i class="fa fa-map-marker" style="margin-right:8px;color:#0f8c80;"></i>Endereco de Entrega</h2>

                        <div class="form-row">
                            <label for="ck-nome">Nome Completo *</label>
                            <input type="text" id="ck-nome" name="nome_cliente" maxlength="150" autocomplete="name"
                                   value="<?php echo htmlspecialchars($campos['nomeCliente'] ?? $nomeUsuario, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="form-row-duo form-row">
                            <div class="form-row">
                                <label for="ck-cpf">CPF *</label>
                                <input type="text" id="ck-cpf" name="cpf_cliente" maxlength="14" placeholder="000.000.000-00"
                                       autocomplete="off">
                            </div>
                            <div class="form-row">
                                <label for="ck-cep">CEP *</label>
                                <input type="text" id="ck-cep" name="cep" maxlength="9" placeholder="00000-000"
                                       autocomplete="postal-code">
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="ck-logr">Logradouro (Rua / Av.) *</label>
                            <input type="text" id="ck-logr" name="logradouro" maxlength="200" autocomplete="street-address"
                                   value="<?php echo htmlspecialchars($campos['logradouro'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-row-duo form-row">
                            <div class="form-row">
                                <label for="ck-num">Numero *</label>
                                <input type="text" id="ck-num" name="numero_end" maxlength="20"
                                       value="<?php echo htmlspecialchars($campos['numeroEnd'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-row">
                                <label for="ck-comp">Complemento</label>
                                <input type="text" id="ck-comp" name="complemento" maxlength="100" placeholder="Apto, Bloco..."
                                       value="<?php echo htmlspecialchars($campos['complemento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="ck-bairro">Bairro *</label>
                            <input type="text" id="ck-bairro" name="bairro" maxlength="100" autocomplete="address-level3"
                                   value="<?php echo htmlspecialchars($campos['bairro'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-row-duo form-row">
                            <div class="form-row">
                                <label for="ck-cidade">Cidade *</label>
                                <input type="text" id="ck-cidade" name="cidade" maxlength="100" autocomplete="address-level2"
                                       value="<?php echo htmlspecialchars($campos['cidade'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-row">
                                <label for="ck-estado">Estado * (UF)</label>
                                <input type="text" id="ck-estado" name="estado" maxlength="2" placeholder="SP"
                                       style="text-transform:uppercase;"
                                       value="<?php echo htmlspecialchars($campos['estadoRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="secc">
                        <h2><i class="fa fa-credit-card" style="margin-right:8px;color:#0f8c80;"></i>Forma de Pagamento</h2>
                        <div class="pag-opts">
                            <div class="pag-opt">
                                <input type="radio" id="pag-pix" name="forma_pagamento" value="pix" checked>
                                <label for="pag-pix"><i class="fa fa-qrcode"></i>PIX</label>
                            </div>
                            <div class="pag-opt">
                                <input type="radio" id="pag-boleto" name="forma_pagamento" value="boleto">
                                <label for="pag-boleto"><i class="fa fa-barcode"></i>Boleto</label>
                            </div>
                            <div class="pag-opt">
                                <input type="radio" id="pag-cartao" name="forma_pagamento" value="cartao">
                                <label for="pag-cartao"><i class="fa fa-credit-card"></i>Cartao</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna direita: resumo do carrinho -->
                <div>
                    <div class="secc" style="position:sticky;top:64px;">
                        <h2><i class="fa fa-shopping-cart" style="margin-right:8px;color:#0f8c80;"></i>Resumo do Pedido</h2>
                        <div id="cart-resume">
                            <p class="cart-r-empty">Carregando carrinho...</p>
                        </div>
                        <div class="frete-line">
                            <span>Frete</span>
                            <span>Gratis</span>
                        </div>
                        <div class="total-line">
                            <span>Total</span>
                            <span id="ck-total">R$ 0,00</span>
                        </div>
                        <button type="submit" class="btn-finalizar" id="btn-finalizar">
                            <i class="fa fa-lock" style="margin-right:8px;"></i>Confirmar Pedido
                        </button>
                        <div style="text-align:center;margin-top:10px;">
                            <a href="produto.index.php" style="font-size:0.8rem;color:#9eaab4;">
                                <i class="fa fa-arrow-left" style="margin-right:4px;"></i>Voltar aos Produtos
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</main>

<script>
(function () {
    // DESTAQUE: le o carrinho do localStorage e renderiza no resumo.
    var storageKey = <?php echo json_encode(
        'jtech_cart_' . ($usuarioId > 0 ? $usuarioId : session_id())
    ); ?>;

    function fmtBRL(v) {
        return 'R$ ' + Number(v).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function renderResumo(cart) {
        var wrap = document.getElementById('cart-resume');
        var totalEl = document.getElementById('ck-total');
        var dataEl  = document.getElementById('ck-cart-data');

        if (!wrap) return;

        if (!cart || cart.length === 0) {
            wrap.innerHTML = '<p class="cart-r-empty">Nenhum item no carrinho.<br>Adicione produtos antes de finalizar.</p>';
            if (totalEl) totalEl.textContent = 'R$ 0,00';
            return;
        }

        var html  = '';
        var total = 0;

        cart.forEach(function (item) {
            var sub = (Number(item.price) || 0) * (Number(item.qty) || 1);
            total  += sub;
            html   += '<div class="cart-r-row">'
                    + '<span class="cart-r-name">' + escHtml(item.name)
                    + (item.color ? ' <small style="color:#9eaab4;">(' + escHtml(item.color) + ')</small>' : '')
                    + ' &times; ' + (item.qty || 1) + '</span>'
                    + '<span class="cart-r-price">' + fmtBRL(sub) + '</span>'
                    + '</div>';
        });

        wrap.innerHTML   = html;
        if (totalEl)  totalEl.textContent = fmtBRL(total);
        if (dataEl)   dataEl.value        = JSON.stringify(cart);
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    var raw  = localStorage.getItem(storageKey);
    var cart = [];
    try { if (raw) cart = JSON.parse(raw); } catch (e) { cart = []; }
    renderResumo(cart);

    // DESTAQUE: garante que cart_data esta preenchido antes do submit.
    var form = document.getElementById('checkout-form');
    if (form) {
        form.addEventListener('submit', function () {
            var dataEl = document.getElementById('ck-cart-data');
            if (dataEl && (!dataEl.value || dataEl.value === '[]')) {
                dataEl.value = JSON.stringify(cart);
            }
        });
    }
})();
</script>

<?php include 'footer.php'; ?>
