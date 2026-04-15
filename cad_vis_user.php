<?php
$pageTitle = 'Cadastro de visitante';
$pageHeading = 'Cadastro de visitante';
$pageDescription = 'Crie seu acesso para salvar os dados e entrar novamente quando precisar.';
$formAction = 'cadastroaction.php';
$submitLabel = 'Cadastrar';
$status = $_GET['status'] ?? '';
$statusMessage = $_GET['mensagem'] ?? '';
$oldNome = $_GET['nome'] ?? '';

include 'includes/page_start.php';
include 'includes/forms/cad_vis_user_form.php';
include 'includes/page_end.php';
?>