-- =====================================================
-- PRODMAIS UMC - Sistema de Autenticacao e Seguranca
-- Adicionar ao schema.sql existente
-- =====================================================

-- =====================================================
-- TABELA: usuarios_admin
-- Armazena usuarios administradores do sistema
-- =====================================================
DROP TABLE IF EXISTS `usuarios_admin`;
CREATE TABLE `usuarios_admin` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'BCrypt hash',
    `nome_completo` VARCHAR(255),
    `ultimo_login` TIMESTAMP NULL,
    `tentativas_login` INT DEFAULT 0,
    `bloqueado_ate` TIMESTAMP NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: tokens_recuperacao_senha
-- Tokens para recuperacao de senha
-- =====================================================
DROP TABLE IF EXISTS `tokens_recuperacao_senha`;
CREATE TABLE `tokens_recuperacao_senha` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `expira_em` TIMESTAMP NOT NULL,
    `usado` BOOLEAN DEFAULT FALSE,
    `usado_em` TIMESTAMP NULL,
    `ip_solicitacao` VARCHAR(45),
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_expira` (`expira_em`),
    INDEX `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: log_login
-- Registro de tentativas de login (auditoria)
-- =====================================================
DROP TABLE IF EXISTS `log_login`;
CREATE TABLE `log_login` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `username` VARCHAR(100),
    `sucesso` BOOLEAN NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(500),
    `motivo_falha` VARCHAR(255) NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin`(`id`) ON DELETE SET NULL,
    INDEX `idx_usuario` (`usuario_id`),
    INDEX `idx_sucesso` (`sucesso`),
    INDEX `idx_data` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERIR USUARIO PADRAO (senha: Admin@2025)
-- ALTERE A SENHA APOS O PRIMEIRO LOGIN!
-- =====================================================
INSERT INTO `usuarios_admin` (`username`, `email`, `password_hash`, `nome_completo`) VALUES
('admin', 'admin@umc.br', '$2y$10$9XKvNZZr5VrE8Y/y1OYvDOC2P0h4.vZQB5rJ7pKjE4Qm5NZrE8Y0e', 'Administrador Sistema'),
('matheus.lucindo', 'matheus.lucindo@umc.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Matheus Lucindo');

-- =====================================================
-- PROCEDIMENTO: Limpar tokens expirados (executar periodicamente)
-- =====================================================
DELIMITER //
CREATE PROCEDURE limpar_tokens_expirados()
BEGIN
    DELETE FROM tokens_recuperacao_senha 
    WHERE expira_em < NOW() 
    OR usado = TRUE;
END //
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
