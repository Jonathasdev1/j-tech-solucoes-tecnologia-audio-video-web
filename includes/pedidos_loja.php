<?php
// DESTAQUE: cria automaticamente as tabelas da loja virtual se nao existirem.
function ensure_pedidos_loja_schema(mysqli $conexao): void
{
    $sqlLoja = "CREATE TABLE IF NOT EXISTS `pedidos_loja` (
        `id`              INT(11)       NOT NULL AUTO_INCREMENT,
        `numero_pedido`   VARCHAR(20)   NOT NULL,
        `usuario_id`      INT(11)       NULL,
        `nome_cliente`    VARCHAR(150)  NOT NULL,
        `cpf_cliente`     VARCHAR(14)   NOT NULL DEFAULT '',
        `cep`             VARCHAR(9)    NOT NULL DEFAULT '',
        `logradouro`      VARCHAR(200)  NOT NULL DEFAULT '',
        `numero_end`      VARCHAR(20)   NOT NULL DEFAULT '',
        `complemento`     VARCHAR(100)  NOT NULL DEFAULT '',
        `bairro`          VARCHAR(100)  NOT NULL DEFAULT '',
        `cidade`          VARCHAR(100)  NOT NULL DEFAULT '',
        `estado`          CHAR(2)       NOT NULL DEFAULT '',
        `frete`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `total_itens`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `total_pedido`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `forma_pagamento` ENUM('pix','boleto','cartao') NOT NULL DEFAULT 'pix',
        `status_pedido`   ENUM(
                              'aguardando_pagamento',
                              'pago',
                              'separando',
                              'enviado',
                              'entregue',
                              'cancelado'
                          ) NOT NULL DEFAULT 'aguardando_pagamento',
        `codigo_rastreio` VARCHAR(60)   NOT NULL DEFAULT '',
        `observacoes`     TEXT          NULL,
        `criado_em`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `atualizado_em`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_numero_pedido` (`numero_pedido`),
        KEY `idx_usuario_id`  (`usuario_id`),
        KEY `idx_status`      (`status_pedido`),
        KEY `idx_criado_em`   (`criado_em`)
    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_unicode_ci
      COMMENT='Pedidos da loja virtual J-Tech';";

    $conexao->query($sqlLoja);

    $sqlItens = "CREATE TABLE IF NOT EXISTS `pedido_itens` (
        `id`            INT(11)       NOT NULL AUTO_INCREMENT,
        `pedido_id`     INT(11)       NOT NULL,
        `produto_nome`  VARCHAR(200)  NOT NULL,
        `produto_preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `quantidade`    INT(11)       NOT NULL DEFAULT 1,
        `cor`           VARCHAR(50)   NOT NULL DEFAULT '',
        `subtotal`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        PRIMARY KEY (`id`),
        KEY `idx_pedido_id` (`pedido_id`)
    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_unicode_ci
      COMMENT='Itens dos pedidos da loja J-Tech';";

    $conexao->query($sqlItens);
}

// DESTAQUE: retorna todos os pedidos da loja para o painel admin.
function fetch_pedidos_loja(mysqli $conexao, string $filtroStatus = '', string $busca = ''): array
{
    $where  = '1=1';
    $params = [];
    $types  = '';

    if ($filtroStatus !== '') {
        $where   .= ' AND pl.status_pedido = ?';
        $params[] = $filtroStatus;
        $types   .= 's';
    }

    if ($busca !== '') {
        $termo    = '%' . $busca . '%';
        $where   .= ' AND (pl.numero_pedido LIKE ? OR pl.nome_cliente LIKE ? OR pl.cpf_cliente LIKE ?)';
        $params[] = $termo;
        $params[] = $termo;
        $params[] = $termo;
        $types   .= 'sss';
    }

    $sql  = "SELECT pl.id, pl.numero_pedido, pl.nome_cliente, pl.cpf_cliente,
                    pl.cidade, pl.estado, pl.total_pedido, pl.forma_pagamento,
                    pl.status_pedido, pl.codigo_rastreio, pl.criado_em
             FROM pedidos_loja pl
             WHERE {$where}
             ORDER BY pl.criado_em DESC";

    $stmt = $conexao->prepare($sql);
    if ($stmt === false) {
        return [];
    }

    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $rows   = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

// DESTAQUE: retorna pedidos de um usuario especifico.
function fetch_pedidos_usuario(mysqli $conexao, int $usuarioId): array
{
    $stmt = $conexao->prepare(
        "SELECT pl.id, pl.numero_pedido, pl.total_pedido, pl.forma_pagamento,
                pl.status_pedido, pl.codigo_rastreio, pl.criado_em
         FROM pedidos_loja pl
         WHERE pl.usuario_id = ?
         ORDER BY pl.criado_em DESC"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows   = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

// DESTAQUE: retorna os itens de um pedido especifico.
function fetch_itens_pedido(mysqli $conexao, int $pedidoId): array
{
    $stmt = $conexao->prepare(
        "SELECT produto_nome, produto_preco, quantidade, cor, subtotal
         FROM pedido_itens
         WHERE pedido_id = ?
         ORDER BY id ASC"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param('i', $pedidoId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows   = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

// DESTAQUE: label legivel para status de pedido.
function label_status_pedido(string $status): string
{
    $mapa = [
        'aguardando_pagamento' => 'Aguardando Pagamento',
        'pago'                 => 'Pago',
        'separando'            => 'Separando',
        'enviado'              => 'Enviado',
        'entregue'             => 'Entregue',
        'cancelado'            => 'Cancelado',
    ];

    return $mapa[$status] ?? ucfirst($status);
}

// DESTAQUE: classe CSS de cor para badge de status.
function cor_status_pedido(string $status): string
{
    $mapa = [
        'aguardando_pagamento' => 'w3-orange',
        'pago'                 => 'w3-blue',
        'separando'            => 'w3-purple',
        'enviado'              => 'w3-teal',
        'entregue'             => 'w3-green',
        'cancelado'            => 'w3-red',
    ];

    return $mapa[$status] ?? 'w3-grey';
}
