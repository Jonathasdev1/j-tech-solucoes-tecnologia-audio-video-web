<?php
require_once 'includes/session.php';
start_secure_session();
include 'cabecalho.php';

$usuarioLogado = isset($_SESSION['usuario']) && trim((string) $_SESSION['usuario']) !== '';

$servicos = [
    [
        'icone'    => 'fa-desktop',
        'nome'     => 'Desenvolvimento de Sites',
        'preco'    => 'A partir de R$ 1.500,00',
        'prazo'    => '7 a 15 dias uteis',
        'descricao'=> 'Sites institucionais, portfolios, landing pages e blogs totalmente responsivos, otimizados para SEO e rapidos no carregamento.',
        'tags'     => ['HTML5 + CSS3', 'Design Responsivo', 'SEO Basico', 'Formulario de Contato'],
    ],
    [
        'icone'    => 'fa-shopping-cart',
        'nome'     => 'Loja Virtual / E-commerce',
        'preco'    => 'A partir de R$ 3.500,00',
        'prazo'    => '15 a 30 dias uteis',
        'descricao'=> 'Lojas online completas com catalogo de produtos, carrinho, checkout, integracao de pagamento (PIX, Cartao, Boleto) e painel administrativo.',
        'tags'     => ['Catalogo + Carrinho', 'Gateway de Pagamento', 'Painel Admin', 'Dropshipping'],
    ],
    [
        'icone'    => 'fa-table',
        'nome'     => 'Automacao de Planilhas',
        'preco'    => 'A partir de R$ 500,00',
        'prazo'    => '3 a 7 dias uteis',
        'descricao'=> 'Automatizacao de processos em Excel (VBA / Macros) e Google Sheets (Apps Script): relatorios automaticos, dashboards e integracao com APIs.',
        'tags'     => ['Excel VBA / Macros', 'Google Apps Script', 'Relatorios PDF', 'Dashboard'],
    ],
    [
        'icone'    => 'fa-cogs',
        'nome'     => 'Sistema de Gestao Web',
        'preco'    => 'A partir de R$ 5.000,00',
        'prazo'    => '30 a 60 dias uteis',
        'descricao'=> 'Sistemas personalizados para gestao de clientes, estoque, financeiro e processos internos. ERP, CRM e dashboards sob medida para o seu negocio.',
        'tags'     => ['ERP / CRM', 'Controle de Estoque', 'Multi-usuario', 'Relatorios'],
    ],
    [
        'icone'    => 'fa-plug',
        'nome'     => 'APIs e Integracoes',
        'preco'    => 'A partir de R$ 2.000,00',
        'prazo'    => '5 a 20 dias uteis',
        'descricao'=> 'Desenvolvimento de APIs RESTful e integracao com sistemas externos, gateways de pagamento, marketplaces (Mercado Livre, Shopee) e ERPs.',
        'tags'     => ['REST API', 'Webhooks', 'Gateways Pagamento', 'Marketplaces'],
    ],
    [
        'icone'    => 'fa-wrench',
        'nome'     => 'Suporte e Manutencao',
        'preco'    => 'A partir de R$ 150,00 / mes',
        'prazo'    => 'Contrato mensal',
        'descricao'=> 'Monitoramento continuo, atualizacoes de seguranca, correcao de bugs e pequenas melhorias para manter seu sistema funcionando sem interrupcoes.',
        'tags'     => ['Monitoramento', 'Backup Diario', 'Atualizacoes', 'Suporte Prioritario'],
    ],
];
?>
<style>
    .servicos-page {
        min-height: 100vh;
        background: #eef3f8;
        padding: 24px 16px 36px;
    }

    .servicos-shell {
        max-width: 1100px;
        margin: 0 auto;
    }

    .servicos-topo {
        background: linear-gradient(120deg, #10214e, #0f8c80);
        color: #fff;
        border-radius: 18px;
        padding: 24px 22px;
        margin-bottom: 20px;
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.16);
    }

    .servicos-topo h1 {
        margin: 0;
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        font-weight: 800;
    }

    .servicos-topo p {
        margin: 8px 0 0;
        opacity: 0.95;
    }

    .servicos-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .servico-card {
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .servico-card {
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        display: flex;
        flex-direction: column;
        transition: transform 0.18s, box-shadow 0.18s;
    }

    .servico-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 32px rgba(15,140,128,0.18);
    }

    .servico-icone-wrap {
        background: linear-gradient(135deg, #0f8c80, #10214e);
        padding: 18px 14px 14px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .servico-icone-wrap i {
        font-size: 1.8rem;
        opacity: 0.9;
    }

    .servico-icone-wrap h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .servico-body {
        padding: 14px 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .servico-preco {
        margin: 0 0 4px;
        color: #0f8c80;
        font-size: 1.05rem;
        font-weight: 800;
    }

    .servico-prazo {
        margin: 0 0 10px;
        color: #7a8896;
        font-size: 0.8rem;
    }

    .servico-prazo i { color: #aef021; margin-right: 4px; }

    .servico-desc {
        margin: 0 0 12px;
        color: #364657;
        line-height: 1.45;
        font-size: 0.88rem;
        flex: 1;
    }

    .servico-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
    }

    .servico-tag {
        background: #e8f7f5;
        color: #0f8c80;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 99px;
        border: 1px solid #b2e4df;
    }

    .servico-footer {
        padding: 0 16px 16px;
    }

    .btn-orcamento {
        display: block;
        text-align: center;
        background: #0f8c80;
        color: #fff;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 9px 14px;
        border-radius: 8px;
        text-decoration: none;
        transition: background 0.15s;
    }

    .btn-orcamento:hover {
        background: #0a6b63;
        color: #fff;
        text-decoration: none;
    }

    .nav-acoes {
        margin-top: 16px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .nav-acoes a {
        text-decoration: none;
    }

    @media (max-width: 900px) {
        .servicos-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 580px) {
        .servicos-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="servicos-page">
    <section class="servicos-shell">
        <header class="servicos-topo">
            <h1><i class="fa fa-code" style="margin-right:10px;opacity:0.85;"></i>Servicos de Desenvolvimento</h1>
            <p>Sites, lojas virtuais, automacao de planilhas e sistemas sob medida para o seu negocio.</p>
            <div class="nav-acoes">
                <a class="w3-button w3-round-large w3-lime" href="produto.index.php">
                    <i class="fa fa-shopping-bag" style="margin-right:6px;"></i>Ver Produtos
                </a>
                <a class="w3-button w3-round-large w3-border w3-border-white" style="color:#fff;" href="index.php">
                    <i class="fa fa-home" style="margin-right:6px;"></i>Voltar ao Inicio
                </a>
            </div>
        </header>

        <section class="servicos-grid" aria-label="Lista de servicos">
            <?php foreach ($servicos as $s): ?>
                <article class="w3-card-4 servico-card">
                    <div class="servico-icone-wrap">
                        <i class="fa <?php echo htmlspecialchars($s['icone'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                        <h2><?php echo htmlspecialchars($s['nome'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    </div>
                    <div class="servico-body">
                        <p class="servico-preco"><?php echo htmlspecialchars($s['preco'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="servico-prazo"><i class="fa fa-clock-o"></i><?php echo htmlspecialchars($s['prazo'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="servico-desc"><?php echo htmlspecialchars($s['descricao'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="servico-tags">
                            <?php foreach ($s['tags'] as $tag): ?>
                                <span class="servico-tag"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="servico-footer">
                        <a class="btn-orcamento"
                           href="https://wa.me/5500000000000?text=<?php echo urlencode('Ola! Tenho interesse no servico: ' . $s['nome']); ?>"
                           target="_blank" rel="noopener noreferrer">
                            <i class="fa fa-whatsapp" style="margin-right:6px;"></i>Solicitar Orcamento
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
</main>

<?php include 'footer.php'; ?>
