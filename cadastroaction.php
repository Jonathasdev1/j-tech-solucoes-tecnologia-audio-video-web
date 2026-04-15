<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('Acesso invalido ao cadastro.'));
	exit;
}

$nome = trim($_POST['cadNome'] ?? ($_POST['txtNome'] ?? ''));
$senha = $_POST['cadSenha'] ?? ($_POST['txtSenha'] ?? '');
$confirmarSenha = $_POST['cadConfirmarSenha'] ?? ($_POST['txtConfirmarSenha'] ?? '');

if ($nome === '' || $senha === '' || $confirmarSenha === '') {
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('Preencha todos os campos.') . '&nome=' . urlencode($nome));
	exit;
}

if (strlen($senha) < 4) {
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('A senha deve ter no minimo 4 caracteres.') . '&nome=' . urlencode($nome));
	exit;
}

if ($senha !== $confirmarSenha) {
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('As senhas nao conferem.') . '&nome=' . urlencode($nome));
	exit;
}

require_once 'conection.php';

// Verifica se ja existe usuario com o mesmo nome.
$stmtBusca = $conexao->prepare('SELECT idvisitante FROM visitante WHERE nome = ? LIMIT 1');
if ($stmtBusca === false) {
	$conexao->close();
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('Erro interno ao validar cadastro.'));
	exit;
}

$stmtBusca->bind_param('s', $nome);
$stmtBusca->execute();
$resultadoBusca = $stmtBusca->get_result();

if ($resultadoBusca->fetch_assoc()) {
	$stmtBusca->close();
	$conexao->close();
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('Nome de usuario ja cadastrado. Tente outro.') . '&nome=' . urlencode($nome));
	exit;
}

$stmtBusca->close();

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
if ($senhaHash === false) {
	$conexao->close();
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('Erro interno ao proteger a senha.'));
	exit;
}

// DESTAQUE: novos cadastros entram como usuario; admin e controlado por perfil.
$perfil = 'usuario';

// Salva o novo visitante para permitir login futuro.
$stmtInsert = $conexao->prepare('INSERT INTO visitante (nome, senha, perfil) VALUES (?, ?, ?)');
if ($stmtInsert === false) {
	$conexao->close();
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('Erro interno ao salvar cadastro.'));
	exit;
}

$stmtInsert->bind_param('sss', $nome, $senhaHash, $perfil);
$ok = $stmtInsert->execute();
$stmtInsert->close();
$conexao->close();

if (!$ok) {
	header('Location: cad_vis_user.php?status=erro&mensagem=' . urlencode('Nao foi possivel concluir o cadastro.'));
	exit;
}

header('Location: frontend.php?cadastro=ok&nome=' . urlencode($nome));
exit;
?>
