-- ============================================================
-- J-TECH - Schema de producao
-- Execute este script no banco MySQL remoto ja criado.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `visitante` (
    `idvisitante` INT(11) NOT NULL AUTO_INCREMENT,
    `nome`        VARCHAR(150) NOT NULL,
    `senha`       VARCHAR(255) NOT NULL,
    `perfil`      ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
    PRIMARY KEY (`idvisitante`),
    UNIQUE KEY `uq_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin padrao: usuario=admin / senha=admin123 (altere apos primeiro login)
INSERT IGNORE INTO `visitante` (`nome`, `senha`, `perfil`)
VALUES (
    'admin',
    '$2y$10$.zCiWfu95kbyXx./Q8g3o.R00biwoippO46djZEcXAzuiOI/RGJlq',
    'admin'
);

CREATE TABLE IF NOT EXISTS `pedidos` (
    `idpedido`          INT(11)          NOT NULL AUTO_INCREMENT,
    `numero_pedido`     VARCHAR(20)      NOT NULL,
    `idvisitante`       INT(11)          NOT NULL,
    `categoria`         ENUM('Academia','Restaurante','Cinema','Dentista','Outro')
                                         NOT NULL DEFAULT 'Outro',
    `descricao`         TEXT             NOT NULL,
    `valor`             DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `status_pedido`     ENUM('Pendente','Aprovado','Concluido','Cancelado')
                                         NOT NULL DEFAULT 'Pendente',
    `observacao`        TEXT             NULL,
    `criado_em`         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`idpedido`),
    UNIQUE KEY `uq_numero_pedido` (`numero_pedido`),
    KEY `idx_visitante` (`idvisitante`),
    KEY `idx_status` (`status_pedido`),
    KEY `idx_categoria` (`categoria`),
    CONSTRAINT `fk_pedido_visitante`
        FOREIGN KEY (`idvisitante`)
        REFERENCES `visitante` (`idvisitante`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `produtos` (
    `idproduto` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(160) NOT NULL,
    `descricao` TEXT NOT NULL,
    `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `imagem_principal_url` VARCHAR(500) NOT NULL,
    `galeria_json` LONGTEXT NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedidos_loja` (
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
    `status_pedido`   ENUM('aguardando_pagamento','pago','separando','enviado','entregue','cancelado')
                                       NOT NULL DEFAULT 'aguardando_pagamento',
    `codigo_rastreio` VARCHAR(60)   NOT NULL DEFAULT '',
    `observacoes`     TEXT          NULL,
    `criado_em`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pedidos_loja_numero` (`numero_pedido`),
    KEY `idx_pedidos_loja_usuario` (`usuario_id`),
    KEY `idx_pedidos_loja_status` (`status_pedido`),
    KEY `idx_pedidos_loja_criado` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedido_itens` (
    `id`            INT(11)       NOT NULL AUTO_INCREMENT,
    `pedido_id`     INT(11)       NOT NULL,
    `produto_nome`  VARCHAR(200)  NOT NULL,
    `produto_preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `quantidade`    INT(11)       NOT NULL DEFAULT 1,
    `cor`           VARCHAR(50)   NOT NULL DEFAULT '',
    `subtotal`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    KEY `idx_pedido_itens_pedido` (`pedido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
