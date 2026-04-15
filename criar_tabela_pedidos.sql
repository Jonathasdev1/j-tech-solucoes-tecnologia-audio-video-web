-- ============================================================
-- Tabela: pedidos
-- Banco:  visitante-j-tech
-- Criado: 14/04/2026
-- ============================================================

USE `visitante-j-tech`;

CREATE TABLE IF NOT EXISTS `pedidos` (
    `idpedido`          INT(11)          NOT NULL AUTO_INCREMENT,
    `numero_pedido`     VARCHAR(20)      NOT NULL,
    `idvisitante`       INT(11)          NOT NULL,
    `categoria`         ENUM(
                            'Academia',
                            'Restaurante',
                            'Cinema',
                            'Dentista',
                            'Outro'
                        )                NOT NULL DEFAULT 'Outro',
    `descricao`         TEXT             NOT NULL,
    `valor`             DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `status_pedido`     ENUM(
                            'Pendente',
                            'Aprovado',
                            'Concluido',
                            'Cancelado'
                        )                NOT NULL DEFAULT 'Pendente',
    `observacao`        TEXT             NULL,
    `criado_em`         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`idpedido`),
    UNIQUE KEY `uq_numero_pedido` (`numero_pedido`),
    KEY `idx_visitante`   (`idvisitante`),
    KEY `idx_status`      (`status_pedido`),
    KEY `idx_categoria`   (`categoria`),
    KEY `idx_criado_em`   (`criado_em`),
    CONSTRAINT `fk_pedido_visitante`
        FOREIGN KEY (`idvisitante`)
        REFERENCES `visitante` (`idvisitante`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Pedidos e compras vinculados a cada usuario cadastrado';
