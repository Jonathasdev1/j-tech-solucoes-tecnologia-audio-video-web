<?php
require_once 'includes/session.php';
require_admin_user();
require_once 'conection.php';

$busca       = trim($_GET['busca']    ?? '');
$filtroStatus = trim($_GET['status'] ?? '');
$mensagem    = trim($_GET['mensagem'] ?? '');

// ── Consulta de pedidos com dados do cliente ──────────────────────────────
$where  = '1=1';
$params = [];
$types  = '';

if ($busca !== '') {
    $where   .= ' AND (v.nome LIKE ? OR p.numero_pedido LIKE ? OR p.descricao LIKE ?)';
    $termoBusca = '%' . $busca . '%';
    $params[] = $termoBusca;
    $params[] = $termoBusca;
    $params[] = $termoBusca;
    $types   .= 'sss';
}

if ($filtroStatus !== '') {
    $where   .= ' AND p.status_pedido = ?';
    $params[] = $filtroStatus;
    $types   .= 's';
}

$sql = "SELECT
            p.idpedido,
            p.numero_pedido,
            p.categoria,
            p.descricao,
            p.valor,
            p.status_pedido,
            p.observacao,
            p.criado_em,
            p.atualizado_em,
            v.idvisitante,
            v.nome AS nome_cliente,
            v.perfil AS perfil_cliente
        FROM pedidos p
        INNER JOIN visitante v ON p.idvisitante = v.idvisitante
        WHERE {$where}
        ORDER BY p.criado_em DESC";

$stmt = $conexao->prepare($sql);
if ($stmt === false) {
    $conexao->close();
    header('Location: painel_admin.php?mensagem=' . urlencode('Erro ao carregar pedidos.'));
    exit;
}

if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
$pedidos   = [];
while ($linha = $resultado->fetch_assoc()) {
    $pedidos[] = $linha;
}
$stmt->close();

// ── Totais por status para o mini-resumo ─────────────────────────────────
$totaisResult = $conexao->query(
    "SELECT status_pedido, COUNT(*) AS qtd, SUM(valor) AS soma
     FROM pedidos GROUP BY status_pedido"
);
$totais = ['Pendente' => 0, 'Aprovado' => 0, 'Concluido' => 0, 'Cancelado' => 0];
$somaTotal = 0.0;
if ($totaisResult) {
    while ($t = $totaisResult->fetch_assoc()) {
        $totais[$t['status_pedido']] = (int) $t['qtd'];
        $somaTotal += (float) $t['soma'];
    }
    $totaisResult->free();
}
$totalPedidos = array_sum($totais);

$conexao->close();

// ── Helpers de formatação ─────────────────────────────────────────────────
$statusCor = [
    'Pendente'  => ['bg' => '#fff3cd', 'txt' => '#856404', 'dot' => '#f0ad00'],
    'Aprovado'  => ['bg' => '#d1ecf1', 'txt' => '#0c5460', 'dot' => '#17a2b8'],
    'Concluido' => ['bg' => '#d4edda', 'txt' => '#155724', 'dot' => '#28a745'],
    'Cancelado' => ['bg' => '#f8d7da', 'txt' => '#721c24', 'dot' => '#dc3545'],
];

$catIcone = [
    'Academia'    => '🏋️',
    'Restaurante' => '🍽️',
    'Cinema'      => '🎬',
    'Dentista'    => '🦷',
    'Outro'       => '📦',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos | J-Tech Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        :root {
            --ink:     #081933;
            --teal:    #0f8c80;
            --lima:    #aef021;
            --surface: #f4f8fc;
            --card:    #ffffff;
            --line:    #dde7f1;
            --title:   #0f2647;
            --muted:   #5f7387;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: "Outfit", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 5% 18%, rgba(174, 240, 33, 0.16), transparent 32%),
                radial-gradient(circle at 90% 10%, rgba(15, 140, 128, 0.18), transparent 28%),
                linear-gradient(132deg, var(--ink), #0d5a5e);
        }

        .page-wrap {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 24px 20px 40px;
        }

        /* ── Shell ── */
        .shell {
            width: 100%;
            max-width: 1200px;
            border-radius: 22px;
            overflow: hidden;
            background: var(--surface);
            box-shadow: 0 16px 48px rgba(0,0,0,0.28);
        }

        /* ── Header ── */
        .shell-header {
            padding: 26px 30px;
            background: linear-gradient(125deg, #0e2450, #1b7f7c);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .shell-header h1 { font-size: clamp(1.4rem, 3vw, 2rem); letter-spacing: .3px; }
        .shell-header p  { margin-top: 4px; opacity: .9; font-weight: 500; font-size: .97rem; }

        .btn-novo {
            background: var(--lima);
            color: var(--title);
            border: none;
            border-radius: 12px;
            padding: 11px 22px;
            font-size: .95rem;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            font-family: inherit;
            transition: background .2s;
        }
        .btn-novo:hover { background: #9bd61c; }

        /* ── Body ── */
        .shell-body { padding: 24px 30px 34px; }

        /* ── KPI strip ── */
        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 22px;
        }

        .kpi {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 6px 16px rgba(16,36,68,.07);
        }

        .kpi-lbl {
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .9px;
            color: var(--muted);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .kpi-val {
            font-size: clamp(1.3rem, 2.5vw, 1.8rem);
            font-weight: 800;
            color: var(--title);
            line-height: 1;
        }

        .kpi-sub { font-size: .8rem; color: var(--muted); margin-top: 5px; font-weight: 500; }

        .kpi.total { border-color: #a8d5f5; background: linear-gradient(125deg, #eaf5ff, #fff); }
        .kpi.pendente  { border-left: 4px solid #f0ad00; }
        .kpi.aprovado  { border-left: 4px solid #17a2b8; }
        .kpi.concluido { border-left: 4px solid #28a745; }
        .kpi.cancelado { border-left: 4px solid #dc3545; }

        /* ── Toolbar ── */
        .toolbar {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 18px;
        }

        .toolbar input, .toolbar select {
            border: 1px solid #c8d6e5;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: .95rem;
            font-family: inherit;
            background: #fff;
        }

        .toolbar input  { flex: 1; min-width: 200px; }
        .toolbar select { min-width: 150px; }

        .btn {
            border: none; border-radius: 10px; padding: 10px 14px;
            font-weight: 700; cursor: pointer; text-decoration: none;
            display: inline-block; font-family: inherit; font-size: .92rem;
        }
        .btn-primary   { background: var(--teal); color: #fff; }
        .btn-primary:hover { background: #0b7067; }
        .btn-secondary { background: #e9f0f7; color: #153459; }
        .btn-secondary:hover { background: #dfe8f1; }
        .btn-danger    { background: #fdecea; color: #b02030; }
        .btn-danger:hover { background: #f5c6cb; }
        .btn-sm { padding: 6px 12px; font-size: .83rem; }

        /* ── Mensagem ── */
        .msg-ok  { background: #edf8f1; border: 1px solid #bfe7cb; color: #17653b; border-radius: 10px; padding: 11px 14px; margin-bottom: 16px; font-weight: 600; }
        .msg-err { background: #fdecea; border: 1px solid #f5c6cb; color: #b02030; border-radius: 10px; padding: 11px 14px; margin-bottom: 16px; font-weight: 600; }

        /* ── Contador ── */
        .counter { font-weight: 700; color: #153459; margin-bottom: 12px; font-size: .95rem; }

        /* ── Tabela ── */
        .tbl-wrap { border: 1px solid var(--line); border-radius: 16px; overflow: hidden; }

        table { width: 100%; border-collapse: collapse; background: #fff; }

        thead { background: #0e2450; color: #fff; }

        th { text-align: left; padding: 12px 14px; font-size: .82rem; text-transform: uppercase; letter-spacing: .8px; font-weight: 700; white-space: nowrap; }

        td { padding: 11px 14px; border-bottom: 1px solid #edf1f5; font-size: .9rem; color: #253545; vertical-align: middle; }

        tbody tr:hover { background: #f5fdf9; }

        .num-ped { font-weight: 800; color: var(--title); font-size: .88rem; }
        .cliente { font-weight: 700; color: var(--title); }
        .cliente small { display: block; font-weight: 400; color: var(--muted); font-size: .78rem; }
        .categ { font-size: 1rem; }
        .desc-cell { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .valor-cell { font-weight: 800; color: #0e5c37; white-space: nowrap; }
        .data-cell  { white-space: nowrap; font-size: .83rem; color: var(--muted); }

        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 999px;
            font-size: .78rem; font-weight: 700; white-space: nowrap;
        }

        .badge-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

        .acoes { display: flex; gap: 6px; flex-wrap: wrap; }

        .empty { padding: 28px; text-align: center; color: var(--muted); font-size: .95rem; }

        @media (max-width: 980px) {
            .kpi-strip { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .shell-body { padding: 16px; }
            .kpi-strip  { grid-template-columns: 1fr; }
            table { font-size: .82rem; }
            td, th { padding: 9px 10px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/top_nav.php'; ?>

<main class="page-wrap">
<div class="shell">

    <header class="shell-header">
        <div>
            <h1>Pedidos</h1>
            <p>Gerencie os pedidos e compras vinculados a cada cliente.</p>
        </div>
        <a class="btn-novo" href="admin_pedido_novo.php">+ Novo Pedido</a>
    </header>

    <div class="shell-body">

        <!-- KPI strip -->
        <section class="kpi-strip" aria-label="Resumo de pedidos">
            <div class="kpi total">
                <p class="kpi-lbl">Total de pedidos</p>
                <p class="kpi-val"><?php echo $totalPedidos; ?></p>
                <p class="kpi-sub">R$ <?php echo number_format($somaTotal, 2, ',', '.'); ?> em volume</p>
            </div>
            <div class="kpi pendente">
                <p class="kpi-lbl">Pendentes</p>
                <p class="kpi-val"><?php echo $totais['Pendente']; ?></p>
                <p class="kpi-sub">Aguardando aprovacao</p>
            </div>
            <div class="kpi aprovado">
                <p class="kpi-lbl">Aprovados</p>
                <p class="kpi-val"><?php echo $totais['Aprovado']; ?></p>
                <p class="kpi-sub">Em andamento</p>
            </div>
            <div class="kpi concluido">
                <p class="kpi-lbl">Concluidos</p>
                <p class="kpi-val"><?php echo $totais['Concluido']; ?></p>
                <p class="kpi-sub">Finalizados</p>
            </div>
            <div class="kpi cancelado">
                <p class="kpi-lbl">Cancelados</p>
                <p class="kpi-val"><?php echo $totais['Cancelado']; ?></p>
                <p class="kpi-sub">Nao realizados</p>
            </div>
        </section>

        <!-- Toolbar -->
        <form class="toolbar" method="get" action="admin_pedidos.php">
            <input type="text" name="busca" placeholder="Buscar por cliente, numero ou descricao..." value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>">
            <select name="status">
                <option value="">Todos os status</option>
                <?php foreach (['Pendente','Aprovado','Concluido','Cancelado'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $filtroStatus === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Filtrar</button>
            <a class="btn btn-secondary" href="admin_pedidos.php">Limpar</a>
            <a class="btn btn-secondary" href="painel_admin.php">Voltar ao Painel</a>
        </form>

        <?php if ($mensagem !== ''): ?>
            <div class="msg-ok"><?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <p class="counter">Exibindo <?php echo count($pedidos); ?> pedido(s)</p>

        <!-- Tabela -->
        <div class="tbl-wrap">
            <?php if (count($pedidos) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Categoria</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $p): ?>
                    <?php $cor = $statusCor[$p['status_pedido']] ?? ['bg'=>'#eee','txt'=>'#333','dot'=>'#999']; ?>
                    <tr>
                        <td><?php echo (int) $p['idpedido']; ?></td>

                        <td><span class="num-ped"><?php echo htmlspecialchars($p['numero_pedido'], ENT_QUOTES, 'UTF-8'); ?></span></td>

                        <td>
                            <span class="cliente">
                                <?php echo htmlspecialchars($p['nome_cliente'], ENT_QUOTES, 'UTF-8'); ?>
                                <small>ID <?php echo (int) $p['idvisitante']; ?> · <?php echo htmlspecialchars($p['perfil_cliente'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </span>
                        </td>

                        <td class="categ" title="<?php echo htmlspecialchars($p['categoria'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $catIcone[$p['categoria']] ?? '📦'; ?>
                            <?php echo htmlspecialchars($p['categoria'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td class="desc-cell" title="<?php echo htmlspecialchars($p['descricao'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($p['descricao'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td class="valor-cell">R$ <?php echo number_format((float) $p['valor'], 2, ',', '.'); ?></td>

                        <td>
                            <span class="badge" style="background:<?php echo $cor['bg']; ?>; color:<?php echo $cor['txt']; ?>;">
                                <span class="badge-dot" style="background:<?php echo $cor['dot']; ?>;"></span>
                                <?php echo htmlspecialchars($p['status_pedido'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>

                        <td class="data-cell"><?php echo date('d/m/Y H:i', strtotime($p['criado_em'])); ?></td>

                        <td>
                            <div class="acoes">
                                <a class="btn btn-secondary btn-sm" href="admin_pedido_editar.php?id=<?php echo (int) $p['idpedido']; ?>">Editar</a>
                                <form method="post" action="admin_pedido_excluir.php" style="display:inline;"
                                      onsubmit="return confirm('Excluir pedido <?php echo htmlspecialchars($p['numero_pedido'], ENT_QUOTES, 'UTF-8'); ?>?');">
                                    <input type="hidden" name="idpedido" value="<?php echo (int) $p['idpedido']; ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty">Nenhum pedido encontrado para este filtro.</div>
            <?php endif; ?>
        </div>

    </div><!-- shell-body -->
</div><!-- shell -->
</main>
</body>
</html>
