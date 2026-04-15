<?php
require_once 'includes/session.php';
require_admin_user();
require_once 'conection.php';

// ── Gera próximo número de pedido ─────────────────────────────────────────
$anoAtual = date('Y');
$res = $conexao->query(
    "SELECT MAX(CAST(SUBSTRING_INDEX(numero_pedido, '-', -1) AS UNSIGNED)) AS ultimo
     FROM pedidos WHERE numero_pedido LIKE 'PED-{$anoAtual}-%'"
);
$ultimo    = 0;
if ($res && ($row = $res->fetch_assoc())) {
    $ultimo = (int) $row['ultimo'];
}
$res && $res->free();
$proximoNum   = str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
$numeroPedido = "PED-{$anoAtual}-{$proximoNum}";

// ── Lista de clientes ─────────────────────────────────────────────────────
$clientes = [];
$resC = $conexao->query("SELECT idvisitante, nome, perfil FROM visitante ORDER BY nome ASC");
if ($resC) {
    while ($c = $resC->fetch_assoc()) $clientes[] = $c;
    $resC->free();
}

$conexao->close();

$categorias   = ['Academia','Restaurante','Cinema','Dentista','Outro'];
$statusOpcoes = ['Pendente','Aprovado','Concluido','Cancelado'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Pedido | J-Tech Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        :root {
            --ink:   #081933;
            --teal:  #0f8c80;
            --lima:  #aef021;
            --line:  #dde7f1;
            --title: #0f2647;
            --muted: #5f7387;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: "Outfit", sans-serif;
            background:
                radial-gradient(circle at 5% 18%, rgba(174,240,33,.16), transparent 32%),
                radial-gradient(circle at 90% 10%, rgba(15,140,128,.18), transparent 28%),
                linear-gradient(132deg, var(--ink), #0d5a5e);
        }
        .page-wrap {
            min-height: 100vh; display: flex;
            justify-content: center; padding: 24px 20px 48px;
        }
        .shell {
            width: 100%; max-width: 700px;
            background: #f4f8fc; border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 16px 48px rgba(0,0,0,.28);
        }
        .shell-header {
            padding: 26px 30px;
            background: linear-gradient(125deg, #0e2450, #1b7f7c);
            color: #fff;
        }
        .shell-header h1 { font-size: 1.7rem; }
        .shell-header p  { margin-top: 5px; opacity: .9; font-size: .95rem; }
        form { padding: 28px 30px; display: flex; flex-direction: column; gap: 20px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        label { font-size: .84rem; font-weight: 700; color: var(--title); text-transform: uppercase; letter-spacing: .7px; }
        input[type=text], input[type=number], textarea, select {
            width: 100%; border: 1px solid var(--line); border-radius: 10px;
            padding: 11px 14px; font-size: .97rem; font-family: inherit;
            background: #fff; color: var(--title);
            transition: border .2s;
        }
        input[type=text]:focus, input[type=number]:focus,
        textarea:focus, select:focus {
            border-color: var(--teal); outline: none;
            box-shadow: 0 0 0 3px rgba(15,140,128,.15);
        }
        input[readonly] { background: #edf4fb; color: var(--muted); cursor: default; }
        textarea { resize: vertical; min-height: 80px; }
        .hint { font-size: .8rem; color: var(--muted); margin-top: 2px; }
        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btns { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn {
            border: none; border-radius: 10px; padding: 12px 20px;
            font-size: .97rem; font-weight: 800; cursor: pointer;
            text-decoration: none; display: inline-block; font-family: inherit;
        }
        .btn-primary   { background: var(--teal); color: #fff; }
        .btn-primary:hover { background: #0b7067; }
        .btn-secondary { background: #e3ecf5; color: var(--title); }
        .btn-secondary:hover { background: #d3e1ef; }
        @media (max-width: 580px) {
            form, .shell-header { padding: 20px 18px; }
            .row2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/top_nav.php'; ?>
<main class="page-wrap">
<div class="shell">
    <header class="shell-header">
        <h1>Novo Pedido</h1>
        <p>Preencha os dados abaixo para criar um novo pedido.</p>
    </header>

    <form method="post" action="admin_pedido_salvar.php">
        <input type="hidden" name="acao" value="novo">

        <div class="field">
            <label for="numero_pedido">Número do Pedido</label>
            <input type="text" id="numero_pedido" name="numero_pedido"
                   value="<?php echo htmlspecialchars($numeroPedido, ENT_QUOTES, 'UTF-8'); ?>"
                   readonly>
            <span class="hint">Gerado automaticamente.</span>
        </div>

        <div class="field">
            <label for="idvisitante">Cliente <span style="color:#d00">*</span></label>
            <select id="idvisitante" name="idvisitante" required>
                <option value="">Selecione o cliente...</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?php echo (int) $c['idvisitante']; ?>">
                        <?php echo htmlspecialchars($c['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        (<?php echo htmlspecialchars($c['perfil'], ENT_QUOTES, 'UTF-8'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row2">
            <div class="field">
                <label for="categoria">Categoria <span style="color:#d00">*</span></label>
                <select id="categoria" name="categoria" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="status_pedido">Status</label>
                <select id="status_pedido" name="status_pedido">
                    <?php foreach ($statusOpcoes as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $s === 'Pendente' ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="field">
            <label for="descricao">Descrição <span style="color:#d00">*</span></label>
            <textarea id="descricao" name="descricao" placeholder="Descreva o pedido..." required></textarea>
        </div>

        <div class="field">
            <label for="valor">Valor (R$) <span style="color:#d00">*</span></label>
            <input type="number" id="valor" name="valor" placeholder="0,00"
                   step="0.01" min="0" required>
        </div>

        <div class="field">
            <label for="observacao">Observação (opcional)</label>
            <textarea id="observacao" name="observacao" placeholder="Observações adicionais..."></textarea>
        </div>

        <div class="btns">
            <button class="btn btn-primary" type="submit">Salvar Pedido</button>
            <a class="btn btn-secondary" href="admin_pedidos.php">Cancelar</a>
        </div>
    </form>
</div>
</main>
</body>
</html>
