<?php

// DESTAQUE: garante que a estrutura de produtos exista antes de consultar ou salvar dados.
function ensure_products_schema(mysqli $conexao): void
{
    $sql = "CREATE TABLE IF NOT EXISTS produtos (
        idproduto INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(160) NOT NULL,
        descricao TEXT NOT NULL,
        preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        imagem_principal_url VARCHAR(500) NOT NULL,
        galeria_json LONGTEXT NULL,
        ativo TINYINT(1) NOT NULL DEFAULT 1,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conexao->query($sql);
}

// DESTAQUE: converte a galeria salva em JSON para um array pronto para exibir nos cards.
function decode_product_gallery(?string $galleryJson, string $fallbackImage): array
{
    $images = [];

    if ($galleryJson !== null && $galleryJson !== '') {
        $decoded = json_decode($galleryJson, true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $url = trim((string) $item);
                if ($url !== '') {
                    $images[] = $url;
                }
            }
        }
    }

    if ($fallbackImage !== '') {
        array_unshift($images, $fallbackImage);
    }

    $images = array_values(array_unique($images));

    if (count($images) === 0) {
        $images[] = 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80';
    }

    return $images;
}

// DESTAQUE: retorna os produtos ativos com a galeria já normalizada para a vitrine.
function fetch_active_products(mysqli $conexao): array
{
    ensure_products_schema($conexao);

    $produtos = [];
    $sql = 'SELECT idproduto, nome, descricao, preco, imagem_principal_url, galeria_json FROM produtos WHERE ativo = 1 ORDER BY criado_em DESC';
    $resultado = $conexao->query($sql);

    if ($resultado) {
        while ($linha = $resultado->fetch_assoc()) {
            $linha['imagens'] = decode_product_gallery($linha['galeria_json'] ?? null, $linha['imagem_principal_url'] ?? '');
            $produtos[] = $linha;
        }
        $resultado->free();
    }

    return $produtos;
}