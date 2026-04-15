<?php
require_once 'includes/session.php';
require_admin_user();
require_once 'conection.php';
require_once 'includes/products.php';

ensure_products_schema($conexao);

$mensagem = trim($_GET['mensagem'] ?? '');
$erro = '';
$produtoEdicao = null;

// DESTAQUE: quando o admin clicar em editar, carrega os dados para preencher o formulario.
$editarId = (int) ($_GET['editar'] ?? 0);
if ($editarId > 0) {
    $stmtEdit = $conexao->prepare('SELECT idproduto, nome, descricao, preco, imagem_principal_url, galeria_json FROM produtos WHERE idproduto = ? LIMIT 1');
    if ($stmtEdit) {
        $stmtEdit->bind_param('i', $editarId);
        $stmtEdit->execute();
        $resEdit = $stmtEdit->get_result();
        $produtoEdicao = $resEdit ? $resEdit->fetch_assoc() : null;
        $stmtEdit->close();

        if ($produtoEdicao) {
            $galeriaArray = json_decode((string) ($produtoEdicao['galeria_json'] ?? '[]'), true);
            if (!is_array($galeriaArray)) {
                $galeriaArray = [];
            }
            $produtoEdicao['galeria_texto'] = implode(PHP_EOL, $galeriaArray);
        }
    }
}

// DESTAQUE: salva novos produtos apenas quando a requisicao vier do formulario do admin.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = trim($_POST['acao'] ?? 'cadastrar');
    $idproduto = (int) ($_POST['idproduto'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = (float) str_replace(',', '.', trim($_POST['preco'] ?? '0'));
    $imagemPrincipal = trim($_POST['imagem_principal_url'] ?? '');
    $galeriaTexto = trim($_POST['galeria_urls'] ?? '');

    // DESTAQUE: exclusao logica para manter historico sem mostrar na vitrine.
    if ($acao === 'excluir') {
        if ($idproduto <= 0) {
            $erro = 'ID do produto invalido para exclusao.';
        } else {
            $stmtDelete = $conexao->prepare('UPDATE produtos SET ativo = 0 WHERE idproduto = ?');
            if ($stmtDelete) {
                $stmtDelete->bind_param('i', $idproduto);
                $okDelete = $stmtDelete->execute();
                $stmtDelete->close();

                if ($okDelete) {
                    $conexao->close();
                    header('Location: admin_produtos.php?mensagem=' . urlencode('Produto excluido com sucesso.'));
                    exit;
                }
                $erro = 'Nao foi possivel excluir o produto.';
            } else {
                $erro = 'Erro ao preparar exclusao do produto.';
            }
        }
    }

    if ($acao !== 'excluir' && ($nome === '' || $descricao === '' || $imagemPrincipal === '')) {
        $erro = 'Preencha nome, descricao e imagem principal.';
    } elseif ($acao !== 'excluir' && $preco < 0) {
        $erro = 'Informe um preco valido.';
    } elseif ($erro === '') {
        $galeria = [];
        foreach (preg_split('/\r\n|\r|\n/', $galeriaTexto) as $linha) {
            $url = trim($linha);
            if ($url !== '') {
                $galeria[] = $url;
            }
        }

        $galeriaJson = json_encode($galeria, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($acao === 'editar' && $idproduto > 0) {
            $stmt = $conexao->prepare('UPDATE produtos SET nome = ?, descricao = ?, preco = ?, imagem_principal_url = ?, galeria_json = ?, ativo = 1 WHERE idproduto = ?');
            if ($stmt) {
                $stmt->bind_param('ssdssi', $nome, $descricao, $preco, $imagemPrincipal, $galeriaJson, $idproduto);
                $ok = $stmt->execute();
                $stmt->close();

                if ($ok) {
                    $conexao->close();
                    header('Location: admin_produtos.php?mensagem=' . urlencode('Produto atualizado com sucesso.'));
                    exit;
                }

                $erro = 'Nao foi possivel atualizar o produto.';
            } else {
                $erro = 'Erro ao preparar a edicao do produto.';
            }
        } else {
            $stmt = $conexao->prepare('INSERT INTO produtos (nome, descricao, preco, imagem_principal_url, galeria_json, ativo) VALUES (?, ?, ?, ?, ?, 1)');

            if ($stmt) {
                $stmt->bind_param('ssdss', $nome, $descricao, $preco, $imagemPrincipal, $galeriaJson);
                $ok = $stmt->execute();
                $stmt->close();

                if ($ok) {
                    $conexao->close();
                    header('Location: admin_produtos.php?mensagem=' . urlencode('Produto cadastrado com sucesso.'));
                    exit;
                }

                $erro = 'Nao foi possivel salvar o produto.';
            } else {
                $erro = 'Erro ao preparar o cadastro do produto.';
            }
        }
    }
}

$produtos = fetch_active_products($conexao);
$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Produtos</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            background: linear-gradient(135deg, #081933, #0f8c80);
            min-height: 100vh;
        }

        .admin-produtos-wrap {
            max-width: 1240px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 430px 1fr;
            gap: 18px;
        }

        .admin-panel {
            border-radius: 20px;
            overflow: hidden;
            background: rgba(255,255,255,0.96);
        }

        .admin-head {
            background: linear-gradient(120deg, #10214e, #0f8c80);
            color: #fff;
            padding: 22px;
        }

        .admin-head h1,
        .admin-head h2 {
            margin: 0;
            font-weight: 800;
        }

        .admin-head p {
            margin: 8px 0 0;
            opacity: 0.94;
        }

        .admin-body {
            padding: 18px;
        }

        .field-label {
            font-weight: 700;
            color: #10214e;
            margin-bottom: 6px;
        }

        .thumb-mini {
            width: 70px;
            height: 56px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #d9e2ee;
        }

        .catalog-list {
            display: grid;
            gap: 12px;
        }

        .catalog-item {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 12px;
            align-items: start;
            padding: 12px;
            border: 1px solid #dfe7f1;
            border-radius: 14px;
            background: #fff;
        }

        .catalog-item img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
        }

        .catalog-item h3 {
            margin: 0 0 6px;
            color: #10214e;
            font-size: 1.05rem;
        }

        .catalog-item p {
            margin: 0;
            color: #405264;
        }

        .catalog-price {
            margin-top: 8px;
            color: #cf3f1f;
            font-weight: 800;
        }

        .catalog-actions {
            margin-top: 10px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        @media (max-width: 960px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/top_nav.php'; ?>

    <main class="admin-produtos-wrap">
        <div class="admin-grid">
            <section class="admin-panel w3-card-4">
                <div class="admin-head">
                    <h1><?php echo $produtoEdicao ? 'Editar Produto' : 'Cadastro de Produtos'; ?></h1>
                    <p>Somente o administrador pode cadastrar itens no catalogo.</p>
                </div>

                <div class="admin-body">
                    <?php if ($mensagem !== ''): ?>
                        <div class="w3-panel w3-pale-green w3-round-large w3-border">
                            <p><?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($erro !== ''): ?>
                        <div class="w3-panel w3-pale-red w3-round-large w3-border">
                            <p><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="admin_produtos.php">
                        <input type="hidden" name="acao" value="<?php echo $produtoEdicao ? 'editar' : 'cadastrar'; ?>">
                        <input type="hidden" name="idproduto" value="<?php echo (int) ($produtoEdicao['idproduto'] ?? 0); ?>">

                        <p class="field-label">Nome do Produto</p>
                        <input class="w3-input w3-border w3-round-large w3-margin-bottom" type="text" name="nome" value="<?php echo htmlspecialchars($produtoEdicao['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

                        <p class="field-label">Preco</p>
                        <input class="w3-input w3-border w3-round-large w3-margin-bottom" type="number" step="0.01" min="0" name="preco" value="<?php echo htmlspecialchars(isset($produtoEdicao['preco']) ? (string) $produtoEdicao['preco'] : '', ENT_QUOTES, 'UTF-8'); ?>" required>

                        <p class="field-label">Descricao</p>
                        <textarea class="w3-input w3-border w3-round-large w3-margin-bottom" name="descricao" rows="4" required><?php echo htmlspecialchars($produtoEdicao['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                        <p class="field-label">Imagem Principal da Web</p>
                        <input class="w3-input w3-border w3-round-large w3-margin-bottom" type="url" name="imagem_principal_url" placeholder="https://..." value="<?php echo htmlspecialchars($produtoEdicao['imagem_principal_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

                        <p class="field-label">URLs da Galeria</p>
                        <textarea class="w3-input w3-border w3-round-large w3-margin-bottom" name="galeria_urls" rows="5" placeholder="Uma URL por linha"><?php echo htmlspecialchars($produtoEdicao['galeria_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                        <!-- DESTAQUE: deixa claro para o admin como armazenar varias imagens por produto. -->
                        <p class="w3-small w3-text-grey">Informe uma imagem por linha para montar a galeria do card.</p>

                        <div class="w3-margin-top">
                            <button class="w3-button w3-round-large w3-teal" type="submit"><?php echo $produtoEdicao ? 'Salvar Alteracoes' : 'Cadastrar Produto'; ?></button>
                            <?php if ($produtoEdicao): ?>
                                <a class="w3-button w3-round-large w3-border" href="admin_produtos.php">Cancelar Edicao</a>
                            <?php endif; ?>
                            <a class="w3-button w3-round-large w3-border" href="painel_admin.php">Voltar ao Painel</a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="admin-panel w3-card-4">
                <div class="admin-head">
                    <h2>Produtos Cadastrados</h2>
                    <p>Resumo rapido do que ja esta publicado na vitrine.</p>
                </div>

                <div class="admin-body">
                    <div class="catalog-list">
                        <?php if (count($produtos) === 0): ?>
                            <div class="w3-panel w3-pale-yellow w3-round-large w3-border">
                                <p>Nenhum produto cadastrado ainda.</p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($produtos as $produto): ?>
                            <article class="catalog-item">
                                <img src="<?php echo htmlspecialchars($produto['imagens'][0], ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem do produto">
                                <div>
                                    <h3><?php echo htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p><?php echo htmlspecialchars($produto['descricao'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="catalog-price">R$ <?php echo number_format((float) $produto['preco'], 2, ',', '.'); ?></p>
                                    <div class="catalog-actions">
                                        <a class="w3-button w3-small w3-round-large w3-blue" href="admin_produtos.php?editar=<?php echo (int) $produto['idproduto']; ?>">Editar</a>
                                        <form method="post" action="admin_produtos.php" style="display:inline;" onsubmit="return confirm('Deseja excluir este produto?');">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="idproduto" value="<?php echo (int) $produto['idproduto']; ?>">
                                            <button class="w3-button w3-small w3-round-large w3-red" type="submit">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>