<?php
require_once 'includes/session.php';

require_authenticated_user();

include 'cabecalho.php';
?>

<style>
	/* DESTAQUE: pagina principal remodelada para servir como hub com dois acessos centrais. */
	.hub-page {
		min-height: 100vh;
		padding: 26px 18px 40px;
		background:
			radial-gradient(circle at top left, rgba(174, 240, 33, 0.16), transparent 24%),
			linear-gradient(135deg, #0c1e45, #0f8c80 55%, #16325d);
	}

	.hub-shell {
		max-width: 1180px;
		margin: 0 auto;
	}

	.hub-hero {
		padding: 28px 28px 18px;
		color: #fff;
	}

	.hub-hero h1 {
		margin: 0;
		font-size: clamp(2rem, 5vw, 3.5rem);
		font-weight: 900;
	}

	.hub-hero p {
		margin: 10px 0 0;
		font-size: clamp(1rem, 2vw, 1.18rem);
		max-width: 720px;
		line-height: 1.6;
	}

	.hub-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 22px;
	}

	.hub-card {
		background: rgba(255,255,255,0.12);
		backdrop-filter: blur(8px);
		border: 1px solid rgba(255,255,255,0.16);
		border-radius: 26px;
		overflow: hidden;
		box-shadow: 0 18px 42px rgba(0,0,0,0.2);
	}

	.hub-card img {
		width: 100%;
		height: 260px;
		object-fit: cover;
		display: block;
	}

	.hub-card-body {
		padding: 22px;
		color: #fff;
	}

	.hub-badge {
		display: inline-block;
		padding: 7px 14px;
		border-radius: 999px;
		font-size: 0.78rem;
		font-weight: 800;
		letter-spacing: 0.8px;
		text-transform: uppercase;
		margin-bottom: 12px;
	}

	.hub-badge.produtos {
		background: #aef021;
		color: #10214e;
	}

	.hub-badge.servicos {
		background: #ff6a4d;
		color: #fff;
	}

	.hub-card-body h2 {
		margin: 0;
		font-size: 2rem;
		font-weight: 800;
	}

	.hub-card-body p {
		margin: 12px 0 20px;
		font-size: 1rem;
		line-height: 1.65;
		color: rgba(255,255,255,0.92);
	}

	.hub-actions {
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
	}

	.hub-btn {
		display: inline-block;
		padding: 12px 18px;
		border-radius: 14px;
		font-weight: 800;
		text-decoration: none;
	}

	.hub-btn.primary {
		background: #ffffff;
		color: #10214e;
	}

	.hub-btn.secondary {
		background: transparent;
		border: 1px solid rgba(255,255,255,0.55);
		color: #fff;
	}

	@media (max-width: 860px) {
		.hub-grid {
			grid-template-columns: 1fr;
		}

		.hub-card img {
			height: 220px;
		}
	}
</style>

<?php
// Recupera o nome salvo na sessao durante o login para personalizar a abertura.
$usuario = $_SESSION['usuario'] ?? 'Visitante';
$perfil = $_SESSION['perfil'] ?? 'usuario';
?>

<main class="hub-page">
	<section class="hub-shell">
		<header class="hub-hero">
			<h1>Ola, <?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?></h1>
			<p>Escolha uma das duas areas principais da plataforma. A pagina foi simplificada para concentrar sua navegacao em Produtos e Servicos.</p>
		</header>

		<section class="hub-grid" aria-label="Acessos principais do usuario">
			<article class="hub-card">
				<img src="https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&w=1200&q=80" alt="Vitrine de produtos tecnologicos">
				<div class="hub-card-body">
					<span class="hub-badge produtos">Produtos</span>
					<h2>Catalogo de Produtos</h2>
					<p>Veja os produtos publicados pelo administrador, com preco, descricao e varias imagens por item.</p>
					<div class="hub-actions">
						<a class="hub-btn primary" href="produto.index.php">Abrir Produtos</a>
						<a class="hub-btn secondary" href="index.php">Voltar ao Inicio</a>
					</div>
				</div>
			</article>

			<article class="hub-card">
				<img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80" alt="Equipe oferecendo servicos digitais">
				<div class="hub-card-body">
					<span class="hub-badge servicos">Servicos</span>
					<h2>Area de Servicos</h2>
					<p>Acesse os servicos oferecidos pela plataforma para suporte, instalacao, consultoria e atendimento especializado.</p>
					<div class="hub-actions">
						<a class="hub-btn primary" href="servicos.index.php">Abrir Servicos</a>
						<?php if ($perfil === 'admin'): ?>
							<a class="hub-btn secondary" href="painel_admin.php">Painel Admin</a>
						<?php else: ?>
							<a class="hub-btn secondary" href="logout.php">Sair</a>
						<?php endif; ?>
					</div>
				</div>
			</article>
		</section>
	</section>
</main>

<?php
include 'footer.php';
?>