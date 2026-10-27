(() => {
  const form = document.querySelector('#product-form');
  if (!form) return;

  const list = form.querySelector('#size-list');
  const add = form.querySelector('#add-size');
  const template = document.querySelector('#size-template');
  const announcement = form.querySelector('#size-announcement');

  function updateSizes() {
    const rows = [...list.querySelectorAll('.size-row')];
    rows.forEach((row, index) => {
      const input = row.querySelector('input');
      input.id = `size-${index}`;
      const label = row.querySelector('label');
      label.htmlFor = input.id;
      label.textContent = `Medida ${index + 1}`;
      row.querySelector('button').setAttribute('aria-label', `Quitar medida ${index + 1}`);
      row.querySelector('button').hidden = false;
    });
    add.disabled = rows.length >= 50;
  }
  add.hidden = false;
  add.addEventListener('click', () => {
    if (list.querySelectorAll('.size-row').length >= 50) return;
    const row = template.content.firstElementChild.cloneNode(true);
    list.append(row);
    updateSizes();
    row.querySelector('input').focus();
    announcement.textContent = 'Se agregó un campo de medida.';
  });
  list.addEventListener('click', (event) => {
    const button = event.target.closest('.remove-size');
    if (!button) return;
    const row = button.closest('.size-row');
    if (list.querySelectorAll('.size-row').length === 1) {
      row.querySelector('input').value = '';
      row.querySelector('input').focus();
    } else {
      const next = row.nextElementSibling || row.previousElementSibling;
      row.remove();
      updateSizes();
      next.querySelector('input').focus();
    }
    announcement.textContent = 'Se quitó la medida.';
  });
  updateSizes();

  const fileInput = form.querySelector('#product-image');
  const preview = form.querySelector('#preview-image');
  const placeholder = form.querySelector('#preview-placeholder');
  const remove = form.querySelector('#remove-image');
  const removeExisting = form.querySelector('#remove-existing-image');
  const existingSrc = preview.dataset.existingSrc || '';
  const error = form.querySelector('#image-client-error');
  let previewUrl;

  function clearPreview() {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = undefined;
    if (removeExisting) removeExisting.disabled = false;
    const showExisting = existingSrc && !removeExisting?.checked;
    if (showExisting) preview.src = existingSrc;
    else preview.removeAttribute('src');
    preview.hidden = !showExisting;
    placeholder.hidden = Boolean(showExisting);
    remove.hidden = true;
    error.hidden = true;
    fileInput.setCustomValidity('');
  }
  function imageError(message) {
    error.textContent = message;
    error.hidden = false;
    fileInput.setCustomValidity(message);
    remove.hidden = false;
  }
  fileInput.addEventListener('change', () => {
    clearPreview();
    const file = fileInput.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      imageError('Elegí una imagen JPG, PNG o WebP.');
      return;
    }
    if (file.size === 0 || file.size > 5 * 1024 * 1024) {
      imageError('La imagen debe pesar entre 1 byte y 5 MB.');
      return;
    }
    previewUrl = URL.createObjectURL(file);
    if (removeExisting) {
      removeExisting.checked = false;
      removeExisting.disabled = true;
    }
    preview.src = previewUrl;
    preview.hidden = false;
    placeholder.hidden = true;
    remove.hidden = false;
  });
  preview.addEventListener('error', () => {
    if (!previewUrl) {
      preview.hidden = true;
      placeholder.hidden = false;
      return;
    }
    clearPreview();
    imageError('No pudimos leer esa imagen. Seleccioná otro archivo.');
  });
  remove.addEventListener('click', () => {
    fileInput.value = '';
    clearPreview();
    fileInput.focus();
  });
  removeExisting?.addEventListener('change', clearPreview);
  form.addEventListener('submit', () => {
    form.querySelector('button[type="submit"]').disabled = true;
  });
  window.addEventListener('pageshow', () => {
    const submit = form.querySelector('button[type="submit"]');
    if (form.querySelector('#category-id').options.length > 1) submit.disabled = false;
  });
  document.querySelector('#form-errors')?.focus();
})();
