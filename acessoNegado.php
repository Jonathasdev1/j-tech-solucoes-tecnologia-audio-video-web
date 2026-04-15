<?php
http_response_code(403);

$pageTitle = 'Acesso negado';
$mensagem = trim($_GET['mensagem'] ?? 'Usuario ou senha nao existem.');

include 'includes/page_start.php';
?>

<header class="panel-header">
	<h1>Acesso negado</h1>
	<p>As credenciais informadas nao permitiram o acesso ao sistema.</p>
</header>

<div class="panel-body">
	<div class="status-box">
		<?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
	</div>

	<div class="form-actions">
		<a class="btn-secondary" href="index.php">Voltar ao inicio</a>
	</div>
</div>

<?php
include 'includes/page_end.php';
?>
