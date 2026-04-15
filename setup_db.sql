-- Script de criacao do banco e tabela para o projeto J-Tech Login
-- Execute pelo terminal: mysql -u root < setup_db.sql

CREATE DATABASE IF NOT EXISTS `visitante-j-tech`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `visitante-j-tech`;

CREATE TABLE IF NOT EXISTS `visitante` (
    `idvisitante` INT(11) NOT NULL AUTO_INCREMENT,
    `nome`        VARCHAR(150) NOT NULL,
    `senha`       VARCHAR(255) NOT NULL,
    `perfil`      ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
    PRIMARY KEY (`idvisitante`),
    UNIQUE KEY `uq_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cria usuario admin padrao (senha: admin123)
-- O hash abaixo corresponde a password_hash('admin123', PASSWORD_DEFAULT)
INSERT IGNORE INTO `visitante` (`nome`, `senha`, `perfil`)
VALUES (
    'admin',
    '$2y$10$.zCiWfu95kbyXx./Q8g3o.R00biwoippO46djZEcXAzuiOI/RGJlq',
    'admin'
);
