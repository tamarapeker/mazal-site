-- ---------------------------------------------------------------------------
-- Add category image filename column and backfill default values.
-- Naming convention: NOMBRECATEGORIA.png (example: FERRETERIA.png)
-- ---------------------------------------------------------------------------

SET @current_db = DATABASE();

SET @column_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @current_db
    AND TABLE_NAME = 'categories'
    AND COLUMN_NAME = 'image_filename'
);

SET @ddl = IF(
  @column_exists = 0,
  'ALTER TABLE categories ADD COLUMN image_filename VARCHAR(255) NULL AFTER slug',
  'SELECT ''Column categories.image_filename already exists'' AS message'
);

PREPARE ddl_stmt FROM @ddl;
EXECUTE ddl_stmt;
DEALLOCATE PREPARE ddl_stmt;

UPDATE categories
SET image_filename = CONCAT(UPPER(REPLACE(slug, '-', '')), '.png')
WHERE image_filename IS NULL OR image_filename = '';
