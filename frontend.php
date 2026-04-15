<?php
require_once 'includes/session.php';

start_secure_session();

$mensagemCadastro = '';
if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'ok') {
    $nomeCadastro = trim($_GET['nome'] ?? '');
    if ($nomeCadastro !== '') {
        $mensagemCadastro = 'Cadastro concluido com sucesso para ' . $nomeCadastro . '. Faça login para continuar.';
    } else {
        $mensagemCadastro = 'Cadastro concluido com sucesso. Faça login para continuar.';
    }
}

if (isset($_GET['mensagem'])) {
    $mensagemExtra = trim($_GET['mensagem']);
    if ($mensagemExtra !== '') {
        $mensagemCadastro = $mensagemExtra;
    }
}

// Processa o login quando o formulario for enviado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['loginNome'] ?? ($_POST['txtNome'] ?? '');
    $senha = $_POST['loginSenha'] ?? ($_POST['txtSenha'] ?? '');

    if ($nome === '' || $senha === '') {
        header('Location: acessoNegado.php?mensagem=' . urlencode('Preencha nome e senha para continuar.'));
        exit;
    } else {
        require_once 'conection.php';
        require_once 'includes/analytics.php';
        ensure_analytics_schema($conexao);

        // DESTAQUE: busca o perfil para aplicar permissao real de admin.
        $stmt = $conexao->prepare('SELECT idvisitante, nome, senha, perfil FROM visitante WHERE nome = ?');

        if ($stmt === false) {
            die('Erro ao preparar a consulta.');
        }

        $stmt->bind_param('s', $nome);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $linha = $resultado->fetch_assoc();

        $loginValido = false;
        if ($linha) {
            // Aceita senha com hash e tambem formato legado em texto puro.
            if (password_verify($senha, $linha['senha'])) {
                $loginValido = true;
            } elseif ($linha['senha'] === $senha) {
                $loginValido = true;

                // Atualiza conta legada para hash apos primeiro login valido.
                $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                if ($novoHash !== false) {
                    $stmtUpdate = $conexao->prepare('UPDATE visitante SET senha = ? WHERE nome = ?');
                    if ($stmtUpdate) {
                        $stmtUpdate->bind_param('ss', $novoHash, $linha['nome']);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                    }
                }
            }
        }

        // Se o usuario estiver valido, envia para a pagina principal.
        if ($loginValido) {
            register_user_login_metrics($conexao, (int) ($linha['idvisitante'] ?? 0));
            login_user_with_role($linha['nome'], $linha['perfil'] ?? 'usuario', (int) ($linha['idvisitante'] ?? 0));
            $redirectTarget = (($linha['perfil'] ?? 'usuario') === 'admin') ? 'painel_admin.php' : 'principal.php';
            $stmt->close();
            $conexao->close();
            header('Location: ' . $redirectTarget);
            exit;
        }

        $stmt->close();
        $conexao->close();
        header('Location: acessoNegado.php?mensagem=' . urlencode('Usuario ou senha nao existem.'));
        exit;
    }
}
?>
<!DOCTYPE html>
<!-- Define o tipo de documento HTML5 -->
<html lang="pt-br">
<!-- Informa que o conteúdo da página está em português do Brasil -->
<head>
    <!-- Cabeçalho com metadados e estilos da página -->
    <meta charset="UTF-8">
    <!-- Permite acentuação correta (UTF-8) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Faz o layout se adaptar a celular, tablet e desktop -->
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Ajuda compatibilidade com navegadores antigos -->

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <!-- Biblioteca CSS W3.CSS para utilitários de layout -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Biblioteca de ícones (opcional neste exemplo) -->

    <title>Login</title>
    <!-- Título da aba do navegador -->

    <style>
        /* Remove espaçamentos padrão e melhora controle de largura/altura */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Configuração do plano de fundo da página inteira */
        body {
            /* Usa a imagem de fundo solicitada: imgfundo (ajuste a extensão se precisar) */
            background-image: url("img/imgfundo.jpeg.png");
            /* Faz a imagem cobrir toda a tela, sem deformar */
            background-size: cover;
            /* Mantém o foco da imagem no centro */
            background-position: center;
            /* Evita repetição da imagem */
            background-repeat: no-repeat;
            /* Mantém a imagem fixa ao rolar a página */
            background-attachment: fixed;
            /* Cor de segurança caso a imagem não carregue */
            background-color: #e9eef2;
            /* Garante altura mínima de 100% da área visível */
            min-height: 100vh;
            /* Fonte base da página */
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Camada para centralizar o conteúdo na tela */
        .page-wrapper {
            /* Usa flexbox para alinhamento central */
            display: flex;
            /* Centraliza horizontalmente */
            justify-content: center;
            /* Centraliza verticalmente */
            align-items: center;
            /* Mantém altura total da viewport */
            min-height: 100vh;
            /* Espaço interno para telas pequenas */
            padding: 20px;
        }

        /* Caixa principal do login */
        .login-card {
            /* Fundo branco com transparência leve para destacar do fundo */
            background-color: rgba(36, 8, 82, 0.94);
            /* Arredondamento dos cantos */
            border-radius: 24px;
            /* Sombra para dar profundidade */
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.2);
            /* Largura máxima confortável no desktop */
            max-width: 420px;
            /* Largura fluida no mobile */
            width: 100%;
            /* Espaçamento interno */
            padding: 26px;
        }

        /* Ajuste fino do logo */
        .logo {
            /* Limita tamanho do logo para manter proporção visual */
            width: 40%;
            /* Define tamanho máximo em telas grandes */
            max-width: 130px;
            /* Evita que fique pequeno demais */
            min-width: 92px;
        }

        /* Melhora legibilidade dos labels */
        .form-label {
            /* Deixa o texto em negrito */
            color: #ffffff;
            font-weight: 700;
        }

        /* Exibe o erro de login sem alterar o restante do layout. */
        .error-box {
            margin-top: 18px;
            border-radius: 12px;
        }

        /* Personaliza o botão de envio */
        .submit-btn {
            /* Cor de fundo do botão */
            background-color: #0f8c80;
            /* Cor do texto do botão */
            color: #ffffff;
            /* Remove borda padrão */
            border: none;
            /* Arredondamento do botão */
            border-radius: 8px;
            /* Peso de fonte para destaque */
            font-weight: 600;
        }

        /* Efeito visual ao passar o mouse no botão */
        .submit-btn:hover {
            /* Escurece um pouco para feedback visual */
            background-color: #aef021;
        }

        .back-btn {
            display: block;
            text-align: center;
            text-decoration: none;
            background-color: transparent;
            color: #ffffff;
            border: 1px solid #ffffff;
            border-radius: 8px;
            font-weight: 600;
        }

        .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.14);
        }

        /* Ajustes de responsividade para telas menores */
        @media (max-width: 600px) {
            /* Reduz arredondamento e padding em celular */
            .login-card {
                border-radius: 18px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/top_nav.php'; ?>
    <!-- Envolve todo o conteúdo para centralizar na tela -->
    <div class="page-wrapper">
        <!-- Cartão visual de login -->
        <div class="login-card w3-card-4">
            <!-- Área central do logo -->
            <div class="w3-center">
                <!-- Espaço superior -->
                <br>
                <!-- Imagem de logo -->
                <img src="img/LOGO.jpeg" alt="Logo" class="logo w3-circle w3-margin-top">
            </div>

            <!-- Formulário que processa o login na própria página -->
            <form class="w3-container" action="frontend.php" method="post" autocomplete="off">
                <!-- Seção com campos de usuário e senha -->
                <div class="w3-section">
                    <?php if ($mensagemCadastro !== ''): ?>
                        <div class="w3-panel w3-pale-green w3-border w3-round-large">
                            <p><?php echo htmlspecialchars($mensagemCadastro, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endif; ?>

                    <input type="text" name="fakeUser" autocomplete="username" style="display:none;">
                    <input type="password" name="fakePassword" autocomplete="current-password" style="display:none;">

                    <!-- Rótulo do campo de usuário -->
                    <label class="form-label">USER</label>
                    <!-- Campo de texto para usuário -->
                    <input class="w3-input w3-border w3-margin-bottom" type="text" placeholder="Digite o nome" name="loginNome" autocomplete="off" required>

                    <!-- Rótulo do campo de senha -->
                    <label class="form-label">PASSWORD</label>
                    <!-- Campo de senha (oculta os caracteres digitados) -->
                    <input class="w3-input w3-border" type="password" placeholder="Digite a senha" name="loginSenha" autocomplete="new-password" required>

                    <!-- Botão para enviar o formulário -->
                    <button class="w3-button w3-block w3-section w3-padding submit-btn" type="submit">Entrar</button>

                    <!-- Botao para retornar para a primeira tela -->
                    <a class="w3-button w3-block w3-padding back-btn" href="index.php">Voltar ao inicio</a>
                </div>
            </form>
            <!-- Espaço inferior -->
            <br>
        </div>
    </div>
</body>
</html>
