<?php
require_once 'includes/session.php';

start_secure_session();

// Recebe os dados enviados pelo formulario.
$nome = $_POST['loginNome'] ?? ($_POST['txtNome'] ?? '');
$senha = $_POST['loginSenha'] ?? ($_POST['txtSenha'] ?? '');
$mensagemErro = '';

if ($nome === '' || $senha === '') {
    header('Location: acessoNegado.php?mensagem=' . urlencode('Preencha nome e senha para continuar.'));
    exit;
} else {
    // Abre a conexao com o banco usando o arquivo separado.
    require_once 'conection.php';
    require_once 'includes/analytics.php';
    ensure_analytics_schema($conexao);

    // DESTAQUE: busca o perfil para aplicar permissao real de admin.
    $stmt = $conexao->prepare('SELECT idvisitante, nome, senha, perfil FROM visitante WHERE nome = ?');

    if ($stmt === false) {
        die('Erro ao preparar a consulta.');
    }

    // Envia o nome digitado para a consulta preparada.
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

    // Se o usuario existir e a senha estiver correta, abre a pagina principal.
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
