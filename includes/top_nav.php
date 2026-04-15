<?php
require_once __DIR__ . '/session.php';
start_secure_session();

$usuarioNav = $_SESSION['usuario'] ?? '';
$perfilNav = $_SESSION['perfil'] ?? 'usuario';
?>
<style>
    .jtech-top-nav {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: rgba(7,18,46,0.92);
        border-bottom: 1px solid rgba(255,255,255,0.15);
        backdrop-filter: blur(4px);
    }

    .jtech-top-nav-spacer {
        height: 52px;
    }

    @media (max-width: 700px) {
        .jtech-top-nav-spacer {
            height: 96px;
        }
    }
</style>
<!-- DESTAQUE: barra de navegacao reutilizavel para padronizar as telas. -->
<div class="w3-bar jtech-top-nav">
    <a class="w3-bar-item w3-button" style="color:#fff;" href="index.php">Inicio</a>
    <a class="w3-bar-item w3-button" style="color:#fff;" href="visitante.php">Visitante</a>
    <a class="w3-bar-item w3-button" style="color:#fff;" href="frontend.php">Login</a>
    <a class="w3-bar-item w3-button" style="color:#fff;" href="cad_vis_user.php">Cadastrar-se</a>

    <?php if ($usuarioNav !== ''): ?>
        <a class="w3-bar-item w3-button" style="color:#fff;" href="produto.index.php">Produtos</a>
        <a class="w3-bar-item w3-button" style="color:#fff;" href="servicos.index.php">Servicos</a>
        <a class="w3-bar-item w3-button" style="color:#fff;" href="meus_pedidos.php">Meus Pedidos</a>
        <?php if ($perfilNav === 'admin'): ?>
            <a class="w3-bar-item w3-button" style="color:#aef021;" href="painel_admin.php">Painel Admin</a>
            <a class="w3-bar-item w3-button" style="color:#aef021;" href="admin_pedidos_loja.php">Pedidos Loja</a>
            <a class="w3-bar-item w3-button" style="color:#aef021;" href="admin_produtos.php">Cadastro Produtos</a>
        <?php endif; ?>
        <a class="w3-bar-item w3-button w3-right" style="color:#fff;" href="logout.php">Sair</a>
    <?php endif; ?>
</div>
<div class="jtech-top-nav-spacer" aria-hidden="true"></div>
