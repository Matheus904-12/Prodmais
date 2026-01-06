-- =====================================================
-- PRODMAIS UMC - Schema MySQL para Locaweb
-- Database: prodmais_umc
-- Charset: utf8mb4 (suporte completo Unicode)
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. TABELA: pesquisadores
-- =====================================================
DROP TABLE IF EXISTS `pesquisadores`;
CREATE TABLE `pesquisadores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `cpf` VARCHAR(14) UNIQUE,
    `lattes_id` VARCHAR(16) UNIQUE,
    `orcid` VARCHAR(50),
    `email` VARCHAR(255),
    `ppg` VARCHAR(255),
    `departamento` VARCHAR(255),
    `link_lattes` TEXT,
    `link_openalex` TEXT,
    `foto_url` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_nome` (`nome`),
    INDEX `idx_ppg` (`ppg`),
    INDEX `idx_lattes` (`lattes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABELA: producoes
-- =====================================================
DROP TABLE IF EXISTS `producoes`;
CREATE TABLE `producoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pesquisador_id` INT NOT NULL,
    `tipo` VARCHAR(100) NOT NULL,
    `titulo` TEXT NOT NULL,
    `ano` INT NOT NULL,
    `revista` VARCHAR(255),
    `qualis` VARCHAR(10),
    `ppg` VARCHAR(255),
    `doi` VARCHAR(255),
    `issn` VARCHAR(20),
    `tags` TEXT COMMENT 'JSON array de tags',
    `autores` TEXT COMMENT 'JSON array de autores',
    `link_openalex` TEXT,
    `citacoes` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`pesquisador_id`) REFERENCES `pesquisadores`(`id`) ON DELETE CASCADE,
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_ano` (`ano`),
    INDEX `idx_qualis` (`qualis`),
    INDEX `idx_ppg` (`ppg`),
    FULLTEXT `idx_titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TABELA: projetos
-- =====================================================
DROP TABLE IF EXISTS `projetos`;
CREATE TABLE `projetos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(500) NOT NULL,
    `coordenador` VARCHAR(255),
    `ppg` VARCHAR(255),
    `status` VARCHAR(50) DEFAULT 'Ativo',
    `ano_inicio` INT,
    `ano_fim` INT,
    `descricao` TEXT,
    `membros` TEXT COMMENT 'JSON array de membros',
    `financiamento` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_coordenador` (`coordenador`),
    INDEX `idx_ppg` (`ppg`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABELA: ppgs
-- =====================================================
DROP TABLE IF EXISTS `ppgs`;
CREATE TABLE `ppgs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL UNIQUE,
    `descricao` TEXT,
    `coordenador` VARCHAR(255),
    `area` VARCHAR(255),
    `nota_capes` VARCHAR(5),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. INSERIR PPGs da UMC
-- =====================================================
INSERT INTO `ppgs` (`nome`, `descricao`, `area`, `nota_capes`) VALUES
('Biotecnologia', 'Programa de Pós-Graduação em Biotecnologia', 'Ciências Biológicas', '4'),
('Engenharia Biomédica', 'Programa de Pós-Graduação em Engenharia Biomédica', 'Engenharias', '4'),
('Políticas Públicas', 'Programa de Pós-Graduação em Políticas Públicas', 'Ciências Humanas', '3'),
('Ciências e Tecnologia da Saúde', 'Programa de Pós-Graduação em Ciências e Tecnologia da Saúde', 'Ciências da Saúde', '4');

-- =====================================================
-- 6. VIEWS ÚTEIS
-- =====================================================

-- View: Pesquisadores com contagem de produções
DROP VIEW IF EXISTS `view_pesquisadores_stats`;
CREATE VIEW `view_pesquisadores_stats` AS
SELECT 
    p.id,
    p.nome,
    p.ppg,
    p.lattes_id,
    p.email,
    COUNT(prod.id) as total_producoes,
    MAX(prod.ano) as ano_ultima_producao
FROM pesquisadores p
LEFT JOIN producoes prod ON p.id = prod.pesquisador_id
GROUP BY p.id, p.nome, p.ppg, p.lattes_id, p.email;

-- View: Produções por PPG e ano
DROP VIEW IF EXISTS `view_producoes_ppg_ano`;
CREATE VIEW `view_producoes_ppg_ano` AS
SELECT 
    ppg,
    ano,
    COUNT(*) as total,
    COUNT(DISTINCT pesquisador_id) as total_pesquisadores
FROM producoes
WHERE ppg IS NOT NULL
GROUP BY ppg, ano
ORDER BY ano DESC, total DESC;

-- View: Distribuição por Qualis
DROP VIEW IF EXISTS `view_distribuicao_qualis`;
CREATE VIEW `view_distribuicao_qualis` AS
SELECT 
    qualis,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM producoes WHERE qualis IS NOT NULL), 2) as percentual
FROM producoes
WHERE qualis IS NOT NULL
GROUP BY qualis
ORDER BY 
    FIELD(qualis, 'A1', 'A2', 'A3', 'A4', 'B1', 'B2', 'B3', 'B4', 'C');

-- =====================================================
-- 7. STORED PROCEDURES ÚTEIS
-- =====================================================

DELIMITER $$

-- Procedure: Buscar produções com filtros
DROP PROCEDURE IF EXISTS `sp_buscar_producoes`$$
CREATE PROCEDURE `sp_buscar_producoes`(
    IN p_busca VARCHAR(255),
    IN p_tipo VARCHAR(100),
    IN p_qualis VARCHAR(10),
    IN p_ppg VARCHAR(255),
    IN p_ano_inicio INT,
    IN p_ano_fim INT,
    IN p_limite INT
)
BEGIN
    SELECT 
        prod.*,
        pesq.nome as nome_pesquisador,
        pesq.lattes_id
    FROM producoes prod
    INNER JOIN pesquisadores pesq ON prod.pesquisador_id = pesq.id
    WHERE 
        (p_busca IS NULL OR prod.titulo LIKE CONCAT('%', p_busca, '%'))
        AND (p_tipo IS NULL OR prod.tipo = p_tipo)
        AND (p_qualis IS NULL OR prod.qualis = p_qualis)
        AND (p_ppg IS NULL OR prod.ppg = p_ppg)
        AND (p_ano_inicio IS NULL OR prod.ano >= p_ano_inicio)
        AND (p_ano_fim IS NULL OR prod.ano <= p_ano_fim)
    ORDER BY prod.ano DESC, prod.titulo ASC
    LIMIT p_limite;
END$$

-- Procedure: Estatísticas gerais
DROP PROCEDURE IF EXISTS `sp_estatisticas_gerais`$$
CREATE PROCEDURE `sp_estatisticas_gerais`()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM pesquisadores) as total_pesquisadores,
        (SELECT COUNT(*) FROM producoes) as total_producoes,
        (SELECT COUNT(*) FROM projetos) as total_projetos,
        (SELECT COUNT(*) FROM ppgs) as total_ppgs,
        (SELECT COUNT(DISTINCT ano) FROM producoes) as anos_com_producoes,
        (SELECT MAX(ano) FROM producoes) as ano_mais_recente;
END$$

DELIMITER ;

-- =====================================================
-- 8. TRIGGERS
-- =====================================================

DELIMITER $$

-- Trigger: Atualizar updated_at automaticamente
DROP TRIGGER IF EXISTS `trg_producoes_before_update`$$
CREATE TRIGGER `trg_producoes_before_update`
BEFORE UPDATE ON `producoes`
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

DROP TRIGGER IF EXISTS `trg_pesquisadores_before_update`$$
CREATE TRIGGER `trg_pesquisadores_before_update`
BEFORE UPDATE ON `pesquisadores`
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

DELIMITER ;

-- =====================================================
-- 9. USUÁRIO DE APLICAÇÃO (opcional)
-- =====================================================
-- Descomente se quiser criar um usuário específico:
-- CREATE USER IF NOT EXISTS 'prodmais_app'@'%' IDENTIFIED BY 'SenhaSegura123!';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON prodmais_umc.* TO 'prodmais_app'@'%';
-- FLUSH PRIVILEGES;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- FIM DO SCHEMA
-- =====================================================
SELECT 'Schema criado com sucesso!' as status;
