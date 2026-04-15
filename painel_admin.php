<?php
require_once 'includes/session.php';

// DESTAQUE: somente admin pode abrir o painel.
require_admin_user();

require_once 'conection.php';
require_once 'includes/analytics.php';

ensure_analytics_schema($conexao);
$metrics = get_admin_dashboard_metrics($conexao);

$busca = trim($_GET['busca'] ?? '');
$mensagem = trim($_GET['mensagem'] ?? '');

if ($busca !== '') {
    $stmt = $conexao->prepare('SELECT idvisitante, nome, perfil FROM visitante WHERE nome LIKE ? ORDER BY nome ASC');
    if ($stmt === false) {
        $conexao->close();
        header('Location: acessoNegado.php?mensagem=' . urlencode('Erro ao carregar lista de cadastrados.'));
        exit;
    }

    $termoBusca = '%' . $busca . '%';
    $stmt->bind_param('s', $termoBusca);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $stmt = $conexao->prepare('SELECT idvisitante, nome, perfil FROM visitante ORDER BY nome ASC');
    if ($stmt === false) {
        $conexao->close();
        header('Location: acessoNegado.php?mensagem=' . urlencode('Erro ao carregar lista de cadastrados.'));
        exit;
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
}

$visitantes = [];
while ($linha = $resultado->fetch_assoc()) {
    $visitantes[] = $linha;
}

$totalVisitantes = count($visitantes);

$stmt->close();
$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Painel Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        :root {
            --bg-ink: #081933;
            --bg-teal: #0c5e62;
            --surface: #f6f9fd;
            --card: #ffffff;
            --title: #0f2647;
            --body: #395167;
            --line: #dde7f1;
            --ok: #bef83b;
            --accent: #0f8c80;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 8% 20%, rgba(190, 248, 59, 0.18), transparent 34%),
                radial-gradient(circle at 88% 12%, rgba(79, 216, 205, 0.16), transparent 30%),
                linear-gradient(132deg, var(--bg-ink), var(--bg-teal));
            font-family: "Outfit", "Segoe UI", sans-serif;
        }

        .admin-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
        }

        .admin-card {
            width: 100%;
            max-width: 1140px;
            border-radius: 22px;
            overflow: hidden;
            background: rgba(246, 249, 253, 0.97);
            box-shadow: 0 16px 42px rgba(0, 0, 0, 0.28);
        }

        .admin-header {
            padding: 26px 30px;
            background: linear-gradient(125deg, #0e2450, #1b7f7c);
            color: #ffffff;
        }

        .admin-header h1 {
            margin: 0;
            font-size: clamp(1.5rem, 3.4vw, 2.2rem);
            letter-spacing: 0.3px;
        }

        .admin-header p {
            margin: 8px 0 0;
            opacity: 0.92;
            font-weight: 500;
        }

        .admin-body {
            padding: 24px 30px 34px;
            background: var(--surface);
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .kpi-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            box-shadow: 0 8px 18px rgba(16, 36, 68, 0.08);
            position: relative;
            overflow: hidden;
        }

        .kpi-card::after {
            content: '';
            position: absolute;
            inset: auto -30px -40px auto;
            width: 110px;
            height: 110px;
            background: radial-gradient(circle, rgba(15, 140, 128, 0.18), transparent 64%);
            border-radius: 50%;
        }

        .kpi-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #59708b;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .kpi-value {
            margin: 0;
            color: var(--title);
            font-size: clamp(1.4rem, 2.8vw, 2rem);
            font-weight: 800;
            line-height: 1;
        }

        .kpi-note {
            margin-top: 8px;
            font-size: 0.82rem;
            color: var(--body);
            font-weight: 500;
        }

        .kpi-card.is-highlight {
            border-color: #b5dd57;
            background: linear-gradient(125deg, #f8ffe9, #ffffff);
        }

        .toolbar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 18px;
        }

        .toolbar input {
            flex: 1;
            min-width: 220px;
            border: 1px solid #c8d6e5;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.98rem;
            font-family: inherit;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--accent);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #0b7067;
        }

        .btn-secondary {
            background: #e9f0f7;
            color: #153459;
        }

        .btn-secondary:hover {
            background: #dfe8f1;
        }

        .counter {
            margin-bottom: 14px;
            font-weight: 700;
            color: #153459;
        }

        .message-box {
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #edf8f1;
            border: 1px solid #bfe7cb;
            color: #17653b;
            font-weight: 600;
        }

        .table-wrap {
            border: 1px solid #e2e8ef;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        thead {
            background: #14335b;
            color: #ffffff;
        }

        th,
        td {
            text-align: left;
            padding: 12px 14px;
            border-bottom: 1px solid #edf1f5;
        }

        tbody tr:hover {
            background: #f5faf9;
        }

        .empty {
            padding: 18px;
            color: #5f7387;
        }

        @media (max-width: 980px) {
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }
        }

        @media (max-width: 620px) {
            .admin-wrap {
                padding: 14px;
            }

            .admin-header,
            .admin-body {
                padding-left: 16px;
                padding-right: 16px;
            }

            .kpi-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/top_nav.php'; ?>
    <main class="admin-wrap">
        <section class="admin-card">
            <header class="admin-header">
                <h1>Painel Admin</h1>
                <p>Visao geral do crescimento da base e comportamento dos usuarios.</p>
            </header>

            <div class="admin-body">
                <section class="kpi-grid" aria-label="Indicadores de crescimento">
                    <article class="kpi-card is-highlight">
                        <p class="kpi-label">Novos usuarios hoje</p>
                        <p class="kpi-value"><?php echo (int) $metrics['new_today']; ?></p>
                        <p class="kpi-note">Cadastros nas ultimas 24h.</p>
                    </article>

                    <article class="kpi-card">
                        <p class="kpi-label">Novos na semana</p>
                        <p class="kpi-value"><?php echo (int) $metrics['new_week']; ?></p>
                        <p class="kpi-note">Ultimos 7 dias.</p>
                    </article>

                    <article class="kpi-card">
                        <p class="kpi-label">Novos no mes</p>
                        <p class="kpi-value"><?php echo (int) $metrics['new_month']; ?></p>
                        <p class="kpi-note">Ultimos 30 dias.</p>
                    </article>

                    <article class="kpi-card">
                        <p class="kpi-label">Usuarios que voltaram</p>
                        <p class="kpi-value"><?php echo (int) $metrics['returning_users']; ?></p>
                        <p class="kpi-note">Com mais de um login registrado.</p>
                    </article>

                    <article class="kpi-card">
                        <p class="kpi-label">So visitantes do site</p>
                        <p class="kpi-value"><?php echo (int) $metrics['visitors_only']; ?></p>
                        <p class="kpi-note">Visitaram pagina publica sem autenticar.</p>
                    </article>
                </section>

                <div style="margin-bottom:14px;">
                    <a class="btn btn-primary" href="admin_pedidos.php"
                       style="background:#0f8c80;color:#fff;border-radius:10px;padding:10px 20px;text-decoration:none;font-weight:800;font-family:inherit;display:inline-block;">
                        Ver Pedidos
                    </a>
                    <a class="btn btn-primary" href="admin_produtos.php"
                       style="background:#10214e;color:#fff;border-radius:10px;padding:10px 20px;text-decoration:none;font-weight:800;font-family:inherit;display:inline-block;margin-left:8px;">
                        Cadastrar Produtos
                    </a>
                </div>

                <form class="toolbar" method="get" action="painel_admin.php">
                    <input type="text" name="busca" placeholder="Buscar por nome..." value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                    <a class="btn btn-secondary" href="painel_admin.php">Limpar</a>
                    <a class="btn btn-secondary" href="index.php">Voltar ao inicio</a>
                    <a class="btn btn-secondary" href="logout.php">Sair</a>
                </form>

                <?php if ($mensagem !== ''): ?>
                    <div class="message-box"><?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <p class="counter">Total cadastrado: <?php echo (int) $metrics['registered_total']; ?> | Total no filtro: <?php echo $totalVisitantes; ?></p>

                <div class="table-wrap">
                    <?php if ($totalVisitantes > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Perfil</th>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($visitantes as $visitante): ?>
                                    <tr>
                                        <td><?php echo (int) $visitante['idvisitante']; ?></td>
                                        <td><?php echo htmlspecialchars($visitante['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($visitante['perfil'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <!-- DESTAQUE: acesso para editar cadastro individual. -->
                                            <a class="btn btn-secondary" href="admin_usuario_editar.php?id=<?php echo (int) $visitante['idvisitante']; ?>">Editar</a>

                                            <!-- DESTAQUE: exclusao segura com confirmacao nativa do navegador. -->
                                            <form method="post" action="admin_usuario_excluir.php" style="display:inline;" onsubmit="return confirm('Deseja realmente excluir este usuario?');">
                                                <input type="hidden" name="idvisitante" value="<?php echo (int) $visitante['idvisitante']; ?>">
                                                <button class="btn btn-secondary" type="submit">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty">Nenhum cadastro encontrado para esse filtro.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
