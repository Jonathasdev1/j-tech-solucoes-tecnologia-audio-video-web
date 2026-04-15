<?php
require_once 'includes/session.php';

// DESTAQUE: pagina de edicao permitida apenas para perfil admin.
require_admin_user();

require_once 'conection.php';

$idVisitante = (int) ($_GET['id'] ?? $_POST['idvisitante'] ?? 0);
if ($idVisitante <= 0) {
    $conexao->close();
    header('Location: painel_admin.php?mensagem=' . urlencode('Usuario invalido para edicao.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoNome = trim($_POST['nome'] ?? '');
    $novoPerfil = trim($_POST['perfil'] ?? 'usuario');

    if ($novoNome === '') {
        $conexao->close();
        header('Location: admin_usuario_editar.php?id=' . $idVisitante . '&mensagem=' . urlencode('O nome nao pode ficar vazio.'));
        exit;
    }

    if ($novoPerfil !== 'admin' && $novoPerfil !== 'usuario') {
        $novoPerfil = 'usuario';
    }

    // DESTAQUE: impede remover perfil admin do proprio usuario logado por acidente.
    if ((int) ($_SESSION['usuario_id'] ?? 0) === $idVisitante && $novoPerfil !== 'admin') {
        $conexao->close();
        header('Location: admin_usuario_editar.php?id=' . $idVisitante . '&mensagem=' . urlencode('Voce nao pode remover seu proprio perfil admin.'));
        exit;
    }

    $stmtUpdate = $conexao->prepare('UPDATE visitante SET nome = ?, perfil = ? WHERE idvisitante = ?');
    if ($stmtUpdate === false) {
        $conexao->close();
        header('Location: painel_admin.php?mensagem=' . urlencode('Erro ao atualizar cadastro.'));
        exit;
    }

    $stmtUpdate->bind_param('ssi', $novoNome, $novoPerfil, $idVisitante);
    $ok = $stmtUpdate->execute();
    $stmtUpdate->close();
    $conexao->close();

    if (!$ok) {
        header('Location: admin_usuario_editar.php?id=' . $idVisitante . '&mensagem=' . urlencode('Nao foi possivel atualizar este cadastro.'));
        exit;
    }

    header('Location: painel_admin.php?mensagem=' . urlencode('Cadastro atualizado com sucesso.'));
    exit;
}

$stmt = $conexao->prepare('SELECT idvisitante, nome, perfil FROM visitante WHERE idvisitante = ? LIMIT 1');
if ($stmt === false) {
    $conexao->close();
    header('Location: painel_admin.php?mensagem=' . urlencode('Erro ao abrir cadastro para edicao.'));
    exit;
}

$stmt->bind_param('i', $idVisitante);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();
$conexao->close();

if (!$usuario) {
    header('Location: painel_admin.php?mensagem=' . urlencode('Usuario nao encontrado.'));
    exit;
}

$pageTitle = 'Editar usuario';
$mensagem = trim($_GET['mensagem'] ?? '');

include 'includes/page_start.php';
?>

<header class="panel-header">
    <h1>Editar usuario</h1>
    <p>Atualize nome e perfil do cadastro selecionado.</p>
</header>

<div class="panel-body">
    <?php if ($mensagem !== ''): ?>
        <div class="w3-panel w3-pale-red w3-border w3-round-large">
            <p><?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="admin_usuario_editar.php" autocomplete="off">
        <input type="hidden" name="idvisitante" value="<?php echo (int) $usuario['idvisitante']; ?>">

        <div class="form-grid">
            <div class="form-field full">
                <label for="nome">Nome</label>
                <input id="nome" name="nome" type="text" value="<?php echo htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="form-field full">
                <label for="perfil">Perfil</label>
                <select id="perfil" name="perfil" class="w3-select w3-border w3-round-large" required>
                    <option value="usuario" <?php echo $usuario['perfil'] === 'usuario' ? 'selected' : ''; ?>>usuario</option>
                    <option value="admin" <?php echo $usuario['perfil'] === 'admin' ? 'selected' : ''; ?>>admin</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn-secondary" href="painel_admin.php">Cancelar</a>
            <button class="btn-primary" type="submit">Salvar alteracoes</button>
        </div>
    </form>
</div>

<?php
include 'includes/page_end.php';
?>
