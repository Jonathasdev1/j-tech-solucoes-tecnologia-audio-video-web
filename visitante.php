<?php
require_once 'includes/session.php';
require_once 'conection.php';
require_once 'includes/analytics.php';

start_secure_session();
ensure_analytics_schema($conexao);
register_site_visit($conexao, 'visitante.php', false, 0);
$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>J-Tech | Apresentacao Visitante</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        :root {
            --azul: #10214e;
            --teal: #0f8c80;
            --lima: #aef021;
            --claro: #f4f8fb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(150deg, rgba(16, 33, 78, 0.92), rgba(15, 140, 128, 0.82)), url("img/imgfundo.jpeg.png") center/cover no-repeat fixed;
            color: #ffffff;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(8, 18, 45, 0.86);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        }

        .topbar-inner {
            max-width: 1120px;
            margin: 0 auto;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #ffffff;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .brand img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--lima);
            object-fit: cover;
        }

        .nav-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn {
            text-decoration: none;
            border-radius: 999px;
            padding: 9px 16px;
            font-size: 0.9rem;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .btn-outline {
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.7);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .btn-solid {
            background: var(--teal);
            color: #ffffff;
        }

        .btn-solid:hover {
            background: #0c7067;
        }

        .btn-lime {
            background: var(--lima);
            color: var(--azul);
        }

        .btn-lime:hover {
            background: #99d71d;
        }

        .page {
            max-width: 1120px;
            margin: 0 auto;
            padding: 26px 18px 54px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 20px;
            align-items: stretch;
            margin-bottom: 24px;
        }

        .hero-copy,
        .hero-image {
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(7, 14, 38, 0.42);
        }

        .hero-copy {
            padding: 30px 28px;
        }

        .hero-copy h1 {
            font-size: clamp(1.9rem, 4.3vw, 3.2rem);
            line-height: 1.1;
            margin-bottom: 14px;
        }

        .hero-copy h1 span {
            color: var(--lima);
        }

        .hero-copy p {
            font-size: clamp(1rem, 2.1vw, 1.2rem);
            line-height: 1.65;
            opacity: 0.95;
            max-width: 62ch;
        }

        .hero-cta {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            min-height: 280px;
            object-fit: cover;
        }

        .info-strip {
            margin: 22px 0 26px;
            background: rgba(255, 255, 255, 0.92);
            color: #163047;
            border-radius: 20px;
            padding: 18px 20px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.16);
        }

        .info-strip strong {
            color: #0a4769;
        }

        /* DESTAQUE: mesmos cards da pagina inicial para manter consistencia visual. */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 28px;
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
        }

        .card {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(6px);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.38);
        }

        .card-img-wrap {
            position: relative;
            width: 100%;
            height: 210px;
            overflow: hidden;
        }

        .card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }

        .card:hover .card-img-wrap img {
            transform: scale(1.06);
        }

        .card-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .badge-produtos  { background: #aef021; color: #10214e; }
        .badge-servicos  { background: #0f8c80; color: #ffffff; }

        .card-body {
            padding: 22px 24px 26px;
            color: #ffffff;
        }

        .card-body h2 {
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .card-body p {
            font-size: 0.93rem;
            line-height: 1.65;
            opacity: 0.9;
        }

        .card-action {
            display: inline-block;
            margin-top: 18px;
            padding: 9px 20px;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .action-produtos    { background: #aef021; color: #10214e; }
        .action-produtos:hover { background: #9bd61c; }
        .action-servicos      { background: #0f8c80; color: #ffffff; }
        .action-servicos:hover { background: #0c7067; }

        .visual-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .visual-card {
            border-radius: 18px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .visual-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
        }

        .visual-caption {
            padding: 14px 16px 16px;
        }

        .visual-caption h3 {
            font-size: 1.3rem;
            margin-bottom: 6px;
        }

        .visual-caption p {
            font-size: 0.95rem;
            line-height: 1.55;
            opacity: 0.92;
        }

        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .visual-grid {
                grid-template-columns: 1fr;
            }

            .cards-grid {
                grid-template-columns: 1fr;
                max-width: 440px;
            }
        }

        @media (max-width: 540px) {
            .brand span {
                display: none;
            }

            .btn {
                padding: 8px 12px;
                font-size: 0.82rem;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="index.php">
                <img src="img/LOGO.jpeg" alt="Logo J-Tech">
                <span>J-TECH</span>
            </a>

            <nav class="nav-actions">
                <a class="btn btn-outline" href="index.php">Inicio</a>
                <a class="btn btn-solid" href="frontend.php">Login</a>
                <a class="btn btn-lime" href="cad_vis_user.php">Cadastrar-se</a>
            </nav>
        </div>
    </header>

    <main class="page">
        <section class="hero">
            <article class="hero-copy">
                <h1>Bem-vindo ao universo <span>Visitante J-Tech</span></h1>
                <p>
                    Na J-Tech voce tem mais de <strong>150 estabelecimentos</strong> prontos para oferecer
                    <strong>descontos de ate 30%</strong> nas categorias academia, restaurante, cinema,
                    dentista e muito mais.
                </p>
                <div class="hero-cta">
                    <a class="btn btn-lime" href="cad_vis_user.php">Quero meu cadastro</a>
                    <a class="btn btn-outline" href="frontend.php">Ja tenho login</a>
                </div>
            </article>

            <aside class="hero-image">
                <img src="img/visitante/hero.jpg" alt="Pessoas aproveitando beneficios em estabelecimentos">
            </aside>
        </section>

        <section class="info-strip">
            <p>
                Na J-Tech, seu beneficio entra em <strong>Modo Turbo</strong>: uma unica conta para liberar
                vantagens reais em produtos e servicos, com mais praticidade, experiencia inteligente e
                economia no ritmo da sua vida.
            </p>
        </section>

        <section class="cards-grid" aria-label="Cards de acesso rapido">
            <article class="card">
                <div class="card-img-wrap">
                    <img
                        src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=640&q=80"
                        alt="Produtos">
                    <span class="card-badge badge-produtos">Produtos</span>
                </div>
                <div class="card-body">
                    <h2>Produtos</h2>
                    <p>Conheca nosso catalogo com itens de tecnologia, preco atualizado e varias imagens por produto.</p>
                    <a class="card-action action-produtos" href="produto.index.php">Acessar</a>
                </div>
            </article>

            <article class="card">
                <div class="card-img-wrap">
                    <img
                        src="https://images.unsplash.com/photo-1521791136064-7986c2920216?w=640&q=80"
                        alt="Servicos">
                    <span class="card-badge badge-servicos">Servicos</span>
                </div>
                <div class="card-body">
                    <h2>Servicos</h2>
                    <p>Acesse a area de servicos para suporte, instalacao, consultoria e atendimento especializado.</p>
                    <a class="card-action action-servicos" href="servicos.index.php">Acessar</a>
                </div>
            </article>
        </section>
    </main>
</body>
</html>
