<?php
require_once 'includes/session.php';
start_secure_session();
require_once 'conection.php';
require_once 'includes/products.php';
include 'cabecalho.php';

// DESTAQUE: visitante pode visualizar produtos, mas compra so para sessao autenticada.
$usuarioLogado = isset($_SESSION['usuario']) && trim((string) $_SESSION['usuario']) !== '';
$cartStorageKey = 'jtech_cart_' . ($usuarioLogado ? (string) ($_SESSION['usuario_id'] ?? ($_SESSION['usuario'] ?? session_id())) : session_id());

// DESTAQUE: a vitrine agora busca o catalogo cadastrado pelo administrador.
$produtos = fetch_active_products($conexao);
$conexao->close();

// DESTAQUE: fallback com 10 produtos de audio/video para garantir vitrine inicial completa.
$produtosFallback = [
    ['nome' => 'Fone Bluetooth ANC Pro', 'preco' => 249.90, 'descricao' => 'Fone sem fio com cancelamento de ruido e autonomia de 28h.', 'imagens' => ['https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'Caixa de Som Portatil Bass+', 'preco' => 199.90, 'descricao' => 'Som potente com graves reforcados e resistencia a respingos.', 'imagens' => ['https://images.unsplash.com/photo-1589003077984-894e133dabab?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'Soundbar Home Cinema 2.1', 'preco' => 689.90, 'descricao' => 'Soundbar com subwoofer para filmes e series com audio imersivo.', 'imagens' => ['https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'Microfone Condensador USB', 'preco' => 319.90, 'descricao' => 'Microfone para gravacao, lives e chamadas com voz nitida.', 'imagens' => ['https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'Webcam Full HD Auto Focus', 'preco' => 179.90, 'descricao' => 'Imagem em 1080p com foco automatico para reunioes e streaming.', 'imagens' => ['https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'Monitor 27" IPS', 'preco' => 1149.90, 'descricao' => 'Tela ampla com alta definicao para video, jogos e produtividade.', 'imagens' => ['https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'Projetor Smart LED', 'preco' => 1699.90, 'descricao' => 'Projetor com conexao sem fio para assistir em tela grande.', 'imagens' => ['https://images.unsplash.com/photo-1517705008128-361805f42e86?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'TV Box 4K Streaming', 'preco' => 329.90, 'descricao' => 'Transforma qualquer TV em smart com suporte a 4K.', 'imagens' => ['https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'Headset Gamer Surround', 'preco' => 279.90, 'descricao' => 'Headset com audio espacial e microfone removivel.', 'imagens' => ['https://images.unsplash.com/photo-1612444530582-fc66183b16f7?auto=format&fit=crop&w=640&q=80']],
    ['nome' => 'Placa de Captura HDMI', 'preco' => 239.90, 'descricao' => 'Captura video em alta qualidade para lives e gravacoes.', 'imagens' => ['https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=640&q=80']],
];

if (count($produtos) < 10) {
    $faltantes = 10 - count($produtos);
    $produtos = array_merge($produtos, array_slice($produtosFallback, 0, $faltantes));
}

// DESTAQUE: padroniza a descricao do botao "Detalhes" para um texto curto e direto.
function jtech_resumo_produto(string $texto, int $limite = 90): string
{
    $texto = trim(preg_replace('/\s+/', ' ', $texto));
    if ($texto === '') {
        return 'Produto de tecnologia com otimo custo-beneficio.';
    }

    if (strlen($texto) <= $limite) {
        return $texto;
    }

    return rtrim(substr($texto, 0, $limite - 3)) . '...';
}
?>
<style>
    .produtos-page {
        min-height: 100vh;
        background: #eef3f8;
        padding: 24px 16px 36px;
    }

    .produtos-shell {
        max-width: 1220px;
        margin: 0 auto;
    }

    .produtos-topo {
        background: linear-gradient(120deg, #0f8c80, #10214e);
        color: #fff;
        border-radius: 18px;
        padding: 26px 22px;
        margin-bottom: 20px;
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.16);
    }

    .produtos-topo h1 {
        margin: 0;
        font-size: clamp(1.7rem, 3.2vw, 2.4rem);
        font-weight: 800;
    }

    .produtos-topo p {
        margin: 8px 0 0;
        font-size: 1rem;
        opacity: 0.95;
    }

    .acoes-rapidas {
        margin-top: 14px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-acao {
        display: inline-block;
        text-decoration: none;
        background: #aef021;
        color: #10214e;
        font-weight: 700;
        border-radius: 10px;
        padding: 10px 16px;
    }

    .btn-acao.sec {
        background: transparent;
        color: #fff;
        border: 1px solid #fff;
    }

    .grid-produtos {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
    }

    .produto-card {
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #dfe8f2;
    }

    .img-main {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }

    .thumb-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
        padding: 8px 8px 0;
    }

    .thumb-row img {
        width: 100%;
        height: 54px;
        object-fit: cover;
        border-radius: 8px;
    }

    .produto-body {
        padding: 10px 10px 12px;
    }

    .produto-badge {
        display: inline-block;
        margin-bottom: 8px;
        padding: 2px 8px;
        border-radius: 4px;
        background: #ff7a1a;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .produto-titulo {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 700;
        color: #10214e;
        min-height: 44px;
    }

    .produto-preco {
        margin: 7px 0 6px;
        color: #cf3f1f;
        font-size: 1.05rem;
        font-weight: 800;
    }

    .produto-desc {
        margin: 0;
        font-size: 0.83rem;
        color: #3d4c5a;
        line-height: 1.35;
        min-height: 0;
    }

    .produto-btn {
        margin-top: 10px;
    }

    .produto-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .produto-btn-buy {
        margin-top: 0;
    }

    .produto-btn-login {
        margin-top: 0;
        background: #10214e;
        color: #fff;
    }

    .produto-btn-login:hover {
        background: #0c1a3d;
        color: #fff;
    }

    .status-visitante {
        margin-top: 12px;
        background: rgba(255, 255, 255, 0.17);
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: #fff;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 0.9rem;
    }

    .produto-desc {
        margin-top: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .produto-desc.is-hidden {
        display: none;
    }

    .produto-btn-more {
        margin-top: 0;
        background: #fff;
        color: #10214e;
        border: 1px solid #10214e;
    }

    .produto-btn-more:hover {
        background: #10214e;
        color: #fff;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(2, 12, 24, 0.72);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        z-index: 2000;
    }

    .modal-backdrop.is-open {
        display: flex;
    }

    .modal-card {
        width: 100%;
        max-width: 620px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 22px 42px rgba(0, 0, 0, 0.28);
        overflow: hidden;
    }

    .modal-head {
        padding: 14px 18px;
        background: #10214e;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .modal-head h3 {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 800;
    }

    .modal-close {
        border: 1px solid rgba(255, 255, 255, 0.6);
        background: transparent;
        color: #fff;
        border-radius: 8px;
        padding: 6px 10px;
        cursor: pointer;
        font-weight: 700;
    }

    .modal-body {
        padding: 16px 18px 20px;
        color: #2f4154;
        line-height: 1.55;
        font-size: 0.95rem;
        max-height: 62vh;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .produto-config {
        margin-top: 8px;
        display: grid;
        grid-template-columns: 1fr 88px;
        gap: 8px;
        align-items: end;
    }

    .produto-config label {
        font-size: 0.75rem;
        color: #5b6f82;
        font-weight: 700;
        display: block;
        margin-bottom: 3px;
    }

    .produto-config select,
    .produto-config input {
        width: 100%;
        border: 1px solid #d4dee8;
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 0.82rem;
        color: #183250;
        background: #fff;
    }

    .produto-btn-add {
        margin-top: 0;
        background: #10214e;
        color: #fff;
    }

    .produto-btn-add:hover {
        background: #0c1a3d;
        color: #fff;
    }

    .cart-fab {
        position: fixed;
        right: 16px;
        bottom: 16px;
        z-index: 1900;
        border: none;
        background: #10214e;
        color: #fff;
        border-radius: 999px;
        padding: 11px 16px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.22);
    }

    .cart-badge-count {
        display: inline-block;
        margin-left: 6px;
        min-width: 22px;
        text-align: center;
        border-radius: 999px;
        background: #aef021;
        color: #10214e;
        padding: 2px 7px;
        font-size: 0.8rem;
    }

    .cart-panel {
        position: fixed;
        top: 82px;
        right: 14px;
        width: min(360px, calc(100vw - 28px));
        max-height: calc(100vh - 100px);
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.22);
        z-index: 1950;
        border: 1px solid #d6e1ec;
        overflow: hidden;
        display: none;
        flex-direction: column;
    }

    .cart-panel.is-open {
        display: flex;
    }

    .cart-head {
        background: #10214e;
        color: #fff;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cart-head h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
    }

    .cart-close {
        border: 1px solid rgba(255, 255, 255, 0.65);
        background: transparent;
        color: #fff;
        border-radius: 7px;
        padding: 4px 8px;
        font-weight: 700;
        cursor: pointer;
    }

    .cart-list {
        padding: 10px 12px;
        overflow: auto;
        flex: 1;
        display: grid;
        gap: 8px;
    }

    .cart-item {
        border: 1px solid #e1e8ef;
        border-radius: 10px;
        padding: 9px;
        display: grid;
        grid-template-columns: 50px 1fr auto;
        gap: 8px;
        align-items: center;
    }

    .cart-item img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }

    .cart-item h4 {
        margin: 0;
        font-size: 0.86rem;
        color: #10214e;
    }

    .cart-item p {
        margin: 2px 0 0;
        font-size: 0.78rem;
        color: #5f7387;
    }

    .cart-remove {
        border: none;
        background: #fde8e8;
        color: #b62230;
        border-radius: 8px;
        padding: 6px 7px;
        font-size: 0.74rem;
        font-weight: 700;
        cursor: pointer;
    }

    .cart-foot {
        border-top: 1px solid #e1e8ef;
        padding: 10px 12px 12px;
        background: #fbfcfe;
    }

    .cart-total {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        color: #10214e;
        font-weight: 800;
    }

    .cart-actions {
        display: flex;
        gap: 8px;
    }

    .cart-empty {
        color: #6b7f92;
        font-size: 0.88rem;
        text-align: center;
        padding: 10px;
    }

    .empty-state {
        padding: 28px;
        border-radius: 16px;
        background: #fff;
        text-align: center;
        color: #415465;
    }

    @media (max-width: 1150px) {
        .grid-produtos {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 920px) {
        .grid-produtos {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .grid-produtos {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 460px) {
        .grid-produtos {
            grid-template-columns: 1fr;
        }

        .img-main {
            height: 180px;
        }
    }
</style>

<main class="produtos-page">
    <section class="produtos-shell">
        <header class="produtos-topo">
            <h1>Catalogo de Produtos</h1>
            <p>Confira os produtos disponiveis. Cada card possui preco, descricao e varias imagens.</p>
            <?php if (!$usuarioLogado): ?>
                <div class="status-visitante">
                    Voce esta no modo visitante: pode visualizar os produtos, mas a compra exige login.
                </div>
            <?php endif; ?>
            <div class="acoes-rapidas">
                <a class="btn-acao" href="servicos.index.php">Ver Servicos</a>
                <a class="btn-acao sec" href="index.php">Voltar ao Inicio</a>
            </div>
        </header>

        <section class="grid-produtos" aria-label="Lista de produtos audio e video">
            <?php foreach ($produtos as $index => $produto): ?>
                <?php $descId = 'desc-prod-' . (int) $index; ?>
                <?php $descricaoDetalhes = jtech_resumo_produto((string) ($produto['descricao'] ?? '')); ?>
                <?php $descricaoCompleta = trim((string) ($produto['descricao'] ?? '')); ?>
                <article class="w3-card-4 produto-card">
                    <img class="img-main" src="<?php echo htmlspecialchars($produto['imagens'][0], ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem principal do produto">
                    <div class="thumb-row">
                        <?php foreach ($produto['imagens'] as $img): ?>
                            <img src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem adicional do produto">
                        <?php endforeach; ?>
                    </div>
                    <div class="produto-body">
                        <span class="produto-badge">MAIS VENDIDO</span>
                        <h2 class="produto-titulo"><?php echo htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="produto-preco">R$ <?php echo number_format((float) $produto['preco'], 2, ',', '.'); ?></p>
                        <div class="produto-config">
                            <div>
                                <label for="cor-<?php echo (int) $index; ?>">Cor</label>
                                <select id="cor-<?php echo (int) $index; ?>" class="js-prod-cor">
                                    <option value="Preto">Preto</option>
                                    <option value="Branco">Branco</option>
                                    <option value="Azul">Azul</option>
                                    <option value="Vermelho">Vermelho</option>
                                </select>
                            </div>
                            <div>
                                <label for="qtd-<?php echo (int) $index; ?>">Qtd</label>
                                <input id="qtd-<?php echo (int) $index; ?>" class="js-prod-qtd" type="number" min="1" max="99" value="1">
                            </div>
                        </div>
                        <div class="produto-actions">
                            <button
                                class="w3-button w3-round-large w3-small w3-teal produto-btn js-toggle-desc"
                                type="button"
                                data-target="<?php echo $descId; ?>"
                                aria-expanded="false"
                            >
                                Detalhes
                            </button>

                            <?php if ($usuarioLogado): ?>
                                <button
                                    class="w3-button w3-round-large w3-small produto-btn produto-btn-add js-add-cart"
                                    type="button"
                                    data-id="prod-<?php echo (int) $index; ?>"
                                    data-name="<?php echo htmlspecialchars((string) ($produto['nome'] ?? 'Produto'), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-price="<?php echo number_format((float) $produto['preco'], 2, '.', ''); ?>"
                                    data-image="<?php echo htmlspecialchars((string) ($produto['imagens'][0] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Adicionar ao carrinho
                                </button>
                            <?php else: ?>
                                <a class="w3-button w3-round-large w3-small produto-btn produto-btn-login" href="frontend.php?mensagem=<?php echo urlencode('Faca login para concluir a compra.'); ?>">
                                    Login para comprar
                                </a>
                            <?php endif; ?>

                            <button
                                class="w3-button w3-round-large w3-small produto-btn produto-btn-more js-open-modal"
                                type="button"
                                data-title="<?php echo htmlspecialchars((string) ($produto['nome'] ?? 'Produto'), ENT_QUOTES, 'UTF-8'); ?>"
                                data-full-desc="<?php echo htmlspecialchars($descricaoCompleta !== '' ? $descricaoCompleta : $descricaoDetalhes, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                Ver mais
                            </button>
                        </div>
                        <p id="<?php echo $descId; ?>" class="produto-desc is-hidden"><?php echo htmlspecialchars($descricaoDetalhes, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
</main>

<div id="desc-modal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="desc-modal-title">
    <div class="modal-card">
        <div class="modal-head">
            <h3 id="desc-modal-title">Descricao do produto</h3>
            <button type="button" class="modal-close js-close-modal">Fechar</button>
        </div>
        <div id="desc-modal-body" class="modal-body"></div>
    </div>
</div>

<button id="cart-fab" class="cart-fab" type="button" aria-expanded="false" aria-controls="cart-panel">
    Carrinho <span id="cart-badge" class="cart-badge-count">0</span>
</button>

<aside id="cart-panel" class="cart-panel" aria-hidden="true">
    <div class="cart-head">
        <h3>Carrinho</h3>
        <button id="cart-close" class="cart-close" type="button">Fechar</button>
    </div>
    <div id="cart-list" class="cart-list"></div>
    <div class="cart-foot">
        <div class="cart-total">
            <span>Total</span>
            <strong id="cart-total">R$ 0,00</strong>
        </div>
        <div class="cart-actions">
            <button id="cart-clear" class="w3-button w3-small w3-round-large w3-border" type="button">Limpar</button>
            <button id="cart-checkout" class="w3-button w3-small w3-round-large w3-lime" type="button">Finalizar</button>
        </div>
    </div>
</aside>

<script>
window.JTECH_CART_CONFIG = {
    storageKey: '<?php echo htmlspecialchars($cartStorageKey, ENT_QUOTES, 'UTF-8'); ?>',
    userLogged: <?php echo $usuarioLogado ? 'true' : 'false'; ?>
};
</script>
<script src="assets/js/produto-carrinho.js" defer></script>

<?php include 'footer.php'; ?>
