-- ---------------------------------------------------------------------------
-- Mazal DB - Schema
-- ---------------------------------------------------------------------------
-- Usage:
--   1) Open phpMyAdmin
--   2) Import this file
-- ---------------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS mazal_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mazal_db;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  image_filename VARCHAR(255) NULL,
  display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_categories_name (name),
  UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  product_code VARCHAR(40) NOT NULL,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  description TEXT NULL,
  sizes JSON NULL COMMENT 'Array of measures: [\"10cm\", \"20cm\", \"30cm\"]',
  unit VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'unit, kg, m, pair, dozen, etc.',
  quantity_per_bulk VARCHAR(100) NULL COMMENT '50 units, 20 kilograms, etc.',
  image_filename VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_products_product_code (product_code),
  UNIQUE KEY uq_products_slug (slug),
  KEY idx_products_category_id (category_id),
  KEY idx_products_name (name),
  CONSTRAINT fk_products_category_id
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin', 'editor') NOT NULL DEFAULT 'editor',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
