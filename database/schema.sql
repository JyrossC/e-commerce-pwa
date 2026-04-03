-- ============================================================
-- NovaMart E-Commerce PWA — Database Schema
-- Author: Jaiross Steven
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `novamart_db`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `novamart_db`;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ─── Users ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(100)     NOT NULL,
  `email`        VARCHAR(180)     NOT NULL,
  `password`     VARCHAR(255)     NOT NULL COMMENT 'bcrypt hash',
  `role`         ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `phone`        VARCHAR(30)      DEFAULT NULL,
  `address`      TEXT             DEFAULT NULL,
  `avatar`       VARCHAR(255)     DEFAULT NULL,
  `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Categories ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(100)     NOT NULL,
  `slug`         VARCHAR(120)     NOT NULL,
  `description`  TEXT             DEFAULT NULL,
  `image`        VARCHAR(255)     DEFAULT NULL,
  `sort_order`   SMALLINT         NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Products ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `category_id`  INT UNSIGNED     NOT NULL,
  `name`         VARCHAR(200)     NOT NULL,
  `slug`         VARCHAR(220)     NOT NULL,
  `description`  TEXT             DEFAULT NULL,
  `price`        DECIMAL(10,2)    NOT NULL DEFAULT '0.00',
  `sale_price`   DECIMAL(10,2)    DEFAULT NULL COMMENT 'NULL = no sale',
  `stock`        INT UNSIGNED     NOT NULL DEFAULT 0,
  `sku`          VARCHAR(80)      DEFAULT NULL,
  `image`        VARCHAR(255)     DEFAULT NULL,
  `is_featured`  TINYINT(1)       NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_slug` (`slug`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_featured` (`is_featured`),
  KEY `idx_products_active` (`is_active`),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Cart ────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cart` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `user_id`      INT UNSIGNED     NOT NULL,
  `product_id`   INT UNSIGNED     NOT NULL,
  `quantity`     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_user_product` (`user_id`, `product_id`),
  KEY `idx_cart_user` (`user_id`),
  CONSTRAINT `fk_cart_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cart_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Orders ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED  NOT NULL,
  `order_number`     VARCHAR(20)   NOT NULL,
  `subtotal`         DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `shipping_cost`    DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `tax`              DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `total`            DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `status`           ENUM('pending','processing','shipped','delivered','cancelled','refunded')
                     NOT NULL DEFAULT 'pending',
  `payment_method`   VARCHAR(50)   NOT NULL DEFAULT 'cod',
  `payment_status`   ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `shipping_name`    VARCHAR(255)  NOT NULL,
  `shipping_email`   VARCHAR(180)  NOT NULL,
  `shipping_phone`   VARCHAR(255)  DEFAULT NULL,
  `shipping_address` TEXT          NOT NULL,
  `shipping_city`    VARCHAR(80)   NOT NULL,
  `shipping_country` VARCHAR(80)   NOT NULL DEFAULT 'Kenya',
  `notes`            TEXT          DEFAULT NULL,
  `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_number` (`order_number`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_created` (`created_at`),
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Order Items ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `order_id`      INT UNSIGNED  NOT NULL,
  `product_id`    INT UNSIGNED  DEFAULT NULL COMMENT 'NULL if product deleted',
  `product_name`  VARCHAR(200)  NOT NULL COMMENT 'Snapshot at order time',
  `product_image` VARCHAR(255)  DEFAULT NULL,
  `quantity`      SMALLINT UNSIGNED NOT NULL,
  `price`         DECIMAL(10,2) NOT NULL COMMENT 'Unit price at order time',
  `subtotal`      DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_oi_order` (`order_id`),
  KEY `idx_oi_product` (`product_id`),
  CONSTRAINT `fk_oi_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_oi_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Reviews ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED   NOT NULL,
  `product_id`  INT UNSIGNED   NOT NULL,
  `rating`      TINYINT        NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `title`       VARCHAR(150)   DEFAULT NULL,
  `comment`     TEXT           DEFAULT NULL,
  `is_approved` TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review_user_product` (`user_id`, `product_id`),
  KEY `idx_reviews_product` (`product_id`),
  CONSTRAINT `fk_reviews_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reviews_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Wishlist ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wishlist_user_product` (`user_id`, `product_id`),
  CONSTRAINT `fk_wishlist_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wishlist_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
