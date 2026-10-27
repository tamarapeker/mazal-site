<?php
$invalid = static fn(string $field): string => isset($errors[$field]) ? 'aria-invalid="true" aria-describedby="error-' . $field . '"' : '';
$fieldError = static function (string $field) use ($errors): void {
    if (isset($errors[$field])) {
        echo '<p class="field-error" id="error-' . $field . '">' . e($errors[$field]) . '</p>';
    }
};
$sizeValues = $values['sizes'] !== [] ? $values['sizes'] : [''];
?>
<div class="page-heading heading-with-action">
  <div>
    <p class="eyebrow">Catálogo / Productos</p>
    <h1><?= $editing ? 'Editar producto' : 'Nuevo producto' ?></h1>
    <p class="muted"><?= $editing ? 'Actualizá los datos que necesites. El código del producto se mantiene.' : 'Completá los datos del producto. Los campos con * son obligatorios.' ?></p>
  </div>
  <a class="text-link" href="<?= e(url('/admin/products')) ?>">← Volver a productos</a>
</div>

<?php if ($errors !== []): ?>
  <div class="alert form-alert" role="alert" tabindex="-1" id="form-errors">
    <strong>Revisá el formulario antes de guardar.</strong>
    <?php if (isset($errors['form'])): ?><p><?= e($errors['form']) ?></p><?php endif; ?>
    <?php if ($hadImage): ?><p>Volvé a seleccionar la imagen; el navegador no conserva el archivo al recargar.</p><?php endif; ?>
  </div>
<?php endif; ?>

<form method="post" action="<?= e(url($formAction)) ?>" enctype="multipart/form-data" id="product-form">
  <?= csrf_field() ?>
  <div class="product-form-grid">
    <div class="form-stack">
      <section class="card form-section" aria-labelledby="general-heading">
        <h2 id="general-heading">Datos del producto</h2>
        <div class="form-fields">
          <div class="field-grid">
            <div>
              <label for="product-code">Código *</label>
              <input id="product-code" name="product_code" value="<?= e($values['product_code']) ?>" <?= $editing ? 'readonly aria-readonly="true"' : 'required maxlength="40" pattern="[A-Za-z0-9][A-Za-z0-9_-]{0,39}" placeholder="Ej.: JARMA"' ?> <?= $invalid('product_code') ?>>
              <p class="field-help"><?= $editing ? 'El código no se puede modificar.' : 'Un código único. Usá letras, números o guiones.' ?></p>
              <?php $fieldError('product_code'); ?>
            </div>
            <div>
              <label for="category-id">Categoría *</label>
              <select id="category-id" name="category_id" required <?= $invalid('category_id') ?>>
                <option value="">Seleccionar categoría</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= (int)$category['id'] ?>" <?= $values['category_id'] === (string)$category['id'] ? 'selected' : '' ?>><?= e((string)$category['name']) ?><?= (int)$category['is_active'] === 1 ? '' : ' (inactiva)' ?></option>
                <?php endforeach; ?>
              </select>
              <?php if ($categories === []): ?><p class="field-help">Necesitás una categoría cargada para crear productos.</p><?php endif; ?>
              <?php $fieldError('category_id'); ?>
            </div>
          </div>
          <div>
            <label for="product-name">Nombre *</label>
            <input id="product-name" name="name" value="<?= e($values['name']) ?>" required maxlength="180" placeholder="Ej.: Manguera de jardín" <?= $invalid('name') ?>>
            <?php $fieldError('name'); ?>
          </div>
          <div>
            <label for="product-description">Descripción <span class="optional">(opcional)</span></label>
            <textarea id="product-description" name="description" rows="5" maxlength="5000" placeholder="Materiales, características y otros detalles del producto." <?= $invalid('description') ?>><?= e($values['description']) ?></textarea>
            <?php $fieldError('description'); ?>
          </div>
        </div>
      </section>

      <section class="card form-section" aria-labelledby="sales-heading">
        <h2 id="sales-heading">Venta y presentación</h2>
        <div class="field-grid form-fields">
          <div>
            <label for="product-unit">Venta mínima <span class="optional">(opcional)</span></label>
            <input id="product-unit" name="unit" value="<?= e($values['unit']) ?>" maxlength="50" placeholder="Ej.: 1 unidad, 1 par, 25 m" <?= $invalid('unit') ?>>
            <p class="field-help">Por ahora se muestra solo en el backoffice.</p>
            <?php $fieldError('unit'); ?>
          </div>
          <div>
            <label for="product-bulk">Cantidad por bulto <span class="optional">(opcional)</span></label>
            <input id="product-bulk" name="quantity_per_bulk" value="<?= e($values['quantity_per_bulk']) ?>" maxlength="100" placeholder="Ej.: 50 unidades" <?= $invalid('quantity_per_bulk') ?>>
            <?php $fieldError('quantity_per_bulk'); ?>
          </div>
        </div>
      </section>

      <section class="card form-section" aria-labelledby="sizes-heading">
        <h2 id="sizes-heading">Medidas <span class="optional">(opcional)</span></h2>
        <p class="field-help section-help" id="sizes-help">Agregá una medida por campo, por ejemplo 10 cm, 20 cm o 1/2 pulgada. Si no aplica, dejalo vacío.</p>
        <div id="size-list" class="form-fields" aria-describedby="sizes-help">
          <?php foreach ($sizeValues as $index => $size): ?>
            <div class="size-row">
              <div>
                <label for="size-<?= $index ?>">Medida <?= $index + 1 ?></label>
                <input id="size-<?= $index ?>" name="sizes[]" value="<?= e($size) ?>" maxlength="80" placeholder="Ej.: 10 cm" <?= isset($errors['sizes']) ? 'aria-invalid="true" aria-describedby="error-sizes"' : '' ?>>
              </div>
              <button class="button button-secondary remove-size" type="button" aria-label="Quitar medida <?= $index + 1 ?>" hidden>Quitar</button>
            </div>
          <?php endforeach; ?>
        </div>
        <?php $fieldError('sizes'); ?>
        <button class="button button-secondary add-size" type="button" id="add-size" hidden>+ Agregar medida</button>
        <p class="sr-only" aria-live="polite" id="size-announcement"></p>
        <noscript><p class="field-help">Activá JavaScript para agregar más de una medida.</p></noscript>
      </section>
    </div>

    <div class="form-stack">
      <section class="card form-section" aria-labelledby="image-heading">
        <h2 id="image-heading">Imagen <span class="optional">(opcional)</span></h2>
        <div class="image-preview" id="image-preview">
          <img id="preview-image" alt="Vista previa de la imagen del producto" data-existing-src="<?= e($existingImage ?? '') ?>" <?= $existingImage !== null && $values['remove_image'] !== '1' ? 'src="' . e($existingImage) . '"' : 'hidden' ?>>
          <p class="muted" id="preview-placeholder" <?= $existingImage !== null && $values['remove_image'] !== '1' ? 'hidden' : '' ?>>La vista previa aparecerá acá.</p>
        </div>
        <label for="product-image"><?= $existingImage !== null ? 'Reemplazar imagen' : 'Seleccionar imagen' ?></label>
        <input type="file" id="product-image" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="image-help<?= isset($errors['image']) ? ' error-image' : '' ?>" <?= isset($errors['image']) ? 'aria-invalid="true"' : '' ?>>
        <p class="field-help" id="image-help">JPG, PNG o WebP. Hasta 5 MB.<?= $existingImage !== null ? ' Si no elegís otra, se conserva la imagen actual.' : '' ?></p>
        <?php $fieldError('image'); ?>
        <p class="field-error" id="image-client-error" role="alert" hidden></p>
        <button class="text-button" id="remove-image" type="button" hidden>Quitar imagen seleccionada</button>
        <?php if ($existingImage !== null): ?>
          <label class="checkbox-field" for="remove-existing-image">
            <input type="checkbox" id="remove-existing-image" name="remove_image" value="1" <?= $values['remove_image'] === '1' ? 'checked' : '' ?>>
            Quitar la imagen actual al guardar
          </label>
        <?php endif; ?>
      </section>

      <section class="card form-section" aria-labelledby="status-heading">
        <h2 id="status-heading">Publicación</h2>
        <div class="form-fields">
          <label for="product-active">Estado *</label>
          <select id="product-active" name="is_active" <?= $editing ? 'disabled' : 'required' ?> <?= $invalid('is_active') ?>>
            <option value="1" <?= $values['is_active'] === '1' ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= $values['is_active'] === '0' ? 'selected' : '' ?>>Inactivo</option>
          </select>
          <p class="field-help"><?= $editing ? 'El estado se cambia desde el botón Activar o Desactivar de la tabla de productos.' : 'Un producto activo aparece en el catálogo público si su categoría también está activa. Elegí Inactivo para guardarlo sin publicarlo.' ?></p>
          <?php $fieldError('is_active'); ?>
        </div>
      </section>
    </div>
  </div>

  <div class="form-actions">
    <a class="button button-secondary" href="<?= e(url('/admin/products')) ?>">Cancelar</a>
    <button class="button" type="submit" <?= $categories === [] ? 'disabled' : '' ?>><?= $editing ? 'Guardar cambios' : 'Crear producto' ?></button>
  </div>
</form>

<template id="size-template">
  <div class="size-row">
    <div><label></label><input name="sizes[]" maxlength="80" placeholder="Ej.: 10 cm"></div>
    <button class="button button-secondary remove-size" type="button">Quitar</button>
  </div>
</template>
<script src="<?= e(asset('js/admin-product-form.js')) ?>" defer></script>
