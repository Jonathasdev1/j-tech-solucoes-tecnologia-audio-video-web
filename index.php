<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>J-Tech | Bem-vindo</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ─── Fundo global ─── */
        body {
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-image: url("img/imgfundo.jpeg.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: #0d1b2a;
        }

        /* Camada escura sobre o fundo para melhorar legibilidade */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(160deg, rgba(16, 33, 78, 0.90) 0%, rgba(15, 140, 128, 0.72) 100%);
            z-index: 0;
        }

        /* ─── Navbar ─── */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            height: 68px;
            background: rgba(10, 20, 50, 0.88);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar-logo img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0f8c80;
        }

        .navbar-logo span {
            font-size: 1.2rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 1px;
        }

        .navbar-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-nav {
            padding: 9px 22px;
            border-radius: 50px;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        /* Visitante — contorno */
        .btn-visitante {
            background: transparent;
            border-color: rgba(255, 255, 255, 0.55);
            color: #ffffff;
        }

        .btn-visitante:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: #ffffff;
        }

        /* Login — teal sólido */
        .btn-login {
            background: #0f8c80;
            border-color: #0f8c80;
            color: #ffffff;
        }

        .btn-login:hover {
            background: #0c7067;
            border-color: #0c7067;
        }

        /* Cadastrar-se — verde lima destaque */
        .btn-cadastrar {
            background: #aef021;
            border-color: #aef021;
            color: #10214e;
        }

        .btn-cadastrar:hover {
            background: #9bd61c;
            border-color: #9bd61c;
        }

        /* ─── Conteúdo principal ─── */
        .main-content {
            position: relative;
            z-index: 1;
            padding: 120px 24px 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 56px;
        }

        /* ─── Hero text ─── */
        .hero {
            text-align: center;
            color: #ffffff;
        }

        .hero h1 {
            font-size: clamp(2.4rem, 6vw, 4.2rem);
            font-weight: 900;
            line-height: 1.1;
            text-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        }

        .hero h1 span {
            color: #aef021;
        }

        .hero p {
            margin-top: 18px;
            font-size: clamp(1rem, 2.5vw, 1.35rem);
            opacity: 0.88;
            max-width: 560px;
            line-height: 1.7;
        }

        /* ─── Grade de cards ─── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 28px;
            width: 100%;
            max-width: 820px;
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
            opacity: 0.82;
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

        /* ─── Responsivo ─── */
        @media (max-width: 860px) {
            .cards-grid {
                grid-template-columns: 1fr;
                max-width: 440px;
            }

            .navbar {
                padding: 0 16px;
            }

            .btn-nav {
                padding: 8px 14px;
                font-size: 0.84rem;
            }

            .navbar-logo span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .navbar-buttons {
                gap: 6px;
            }
        }
    </style>
</head>
<body>

    <!-- ─── Navbar ─── -->
    <nav class="navbar">
        <a class="navbar-logo" href="index.php">
            <img src="img/LOGO.jpeg" alt="Logo J-Tech">
            <span>J-TECH</span>
        </a>

        <div class="navbar-buttons">
            <a class="btn-nav btn-visitante" href="visitante.php">Visitante</a>
            <a class="btn-nav btn-login"     href="frontend.php">Login</a>
            <a class="btn-nav btn-cadastrar" href="cad_vis_user.php">Cadastrar-se</a>
        </div>
    </nav>

    <!-- ─── Conteúdo ─── -->
    <div class="main-content">

        <!-- Hero -->
        <div class="hero">
            <h1>Bem-vindo à <span>J-Tech</span></h1>
            <p>Acesse sua conta ou cadastre-se para explorar nosso sistema. Escolha entre Produtos e Servicos.</p>
        </div>

        <!-- Cards -->
        <div class="cards-grid">

            <!-- Card Produtos -->
            <div class="card">
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
            </div>

            <!-- Card Servicos -->
            <div class="card">
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
            </div>

        </div>
    </div>

</body>
</html>
