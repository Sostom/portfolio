-- Schema pour le portfolio vidéo Tokou Zime Maximilien
-- Importer via: mysql -u root portfolio_db < data/schema.sql

CREATE DATABASE IF NOT EXISTS `portfolio_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio_db`;

-- Table administrateur
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table projets
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `category` ENUM('montage', 'cadrage', 'motion', 'reportage', 'autre') DEFAULT 'montage',
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `media_type` ENUM('upload', 'youtube', 'vimeo') DEFAULT 'upload',
    `media_url` VARCHAR(500) DEFAULT NULL,
    `media_file` VARCHAR(255) DEFAULT NULL,
    `featured` TINYINT(1) DEFAULT 0,
    `order_num` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed: admin (mot de passe: admin2025!)
INSERT INTO `users` (`username`, `password_hash`)
VALUES ('admin', '$2y$10$lK6wiKU1ped7j1s11oJ6H.t.Gdlmf2vrNDylOYd4ySxlJFFkKqmv2')
ON DUPLICATE KEY UPDATE `username` = `username`;

-- Seed: projets démo (placeholder - à remplacer par l'admin)
INSERT INTO `projects` (`slug`, `title`, `description`, `category`, `media_type`, `media_url`, `featured`, `order_num`)
VALUES
('exemple-bande-annonce', 'Bande annonce événementiel', 'Montage et cadrage d\'une bande annonce pour un événement culturel à Cotonou.', 'montage', 'youtube', 'dQw4w9WgXcQ', 1, 1),
('reportage-coronavirus', 'Reportage sanitaire', 'Couverture d\'un reportage sur les mesures sanitaires. Cadrage et montage.', 'reportage', 'upload', NULL, 0, 2);
