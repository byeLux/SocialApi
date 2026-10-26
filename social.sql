CREATE DATABASE `social2`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `social2`;


-- ============================================
-- TABELLA USER
-- ============================================

CREATE TABLE `user` (
    `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(25) NOT NULL,
    `password` VARCHAR(60) NOT NULL,
    `role` ENUM('user', 'mod', 'admin') NOT NULL DEFAULT 'user',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_username` (`username`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- TABELLA POST
-- ============================================

CREATE TABLE `post` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` TINYINT UNSIGNED NOT NULL,
    `content` VARCHAR(300) NOT NULL,
    `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `delete_date` DATETIME NULL DEFAULT NULL,

    PRIMARY KEY (`id`),

    KEY `idx_post_user_id` (`user_id`),

    CONSTRAINT `fk_post_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)
        ON DELETE RESTRICT
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- TABELLA AUTH_TOKEN
-- ============================================

CREATE TABLE `auth_token` (
    `token_hash` CHAR(64) NOT NULL,
    `user_id` TINYINT UNSIGNED NOT NULL,
    `expires_at` BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (`token_hash`),

    KEY `idx_auth_token_user_id` (`user_id`),

    CONSTRAINT `fk_auth_token_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;