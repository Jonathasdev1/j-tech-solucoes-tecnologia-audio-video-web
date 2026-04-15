<?php
$formAction = $formAction ?? 'cadastroaction.php';
$pageHeading = $pageHeading ?? 'Cadastro de visitante';
$pageDescription = $pageDescription ?? 'Este primeiro passo separa a tela em blocos reutilizaveis para facilitar manutencao e crescimento do projeto.';
$submitLabel = $submitLabel ?? 'Continuar';
$status = $status ?? '';
$statusMessage = $statusMessage ?? '';
$oldNome = $oldNome ?? '';
?>
<header class="panel-header">
    <h1><?php echo htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p><?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?></p>
</header>

<div class="panel-body">
    <?php if ($statusMessage !== ''): ?>
        <?php
        $boxClass = $status === 'ok' ? 'w3-pale-green w3-border-green' : 'w3-pale-red w3-border-red';
        ?>
        <div class="w3-panel w3-border <?php echo $boxClass; ?> w3-round-large">
            <p><?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php endif; ?>

    <p class="helper-text">Preencha os campos abaixo para criar seu usuario no banco de dados.</p>

    <form action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" autocomplete="off">
        <div class="form-grid">
            <div class="form-field full">
                <label for="cadNome">Nome</label>
                <input id="cadNome" name="cadNome" type="text" placeholder="Digite o nome do usuario" value="<?php echo htmlspecialchars($oldNome, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off" required>
            </div>

            <div class="form-field">
                <label for="cadSenha">Senha</label>
                <input id="cadSenha" name="cadSenha" type="password" placeholder="Crie uma senha" autocomplete="new-password" required>
            </div>

            <div class="form-field">
                <label for="cadConfirmarSenha">Confirmar senha</label>
                <input id="cadConfirmarSenha" name="cadConfirmarSenha" type="password" placeholder="Repita a senha" autocomplete="new-password" required>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn-primary" type="submit"><?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?></button>
        </div>
    </form>
</div>