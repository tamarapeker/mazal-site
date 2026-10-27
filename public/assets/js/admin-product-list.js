(() => {
  const dialog = document.querySelector('#product-status-dialog');
  let pendingForm;
  document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-product-status-form]');
    if (!form) return;
    if (!dialog?.showModal) return;
    event.preventDefault();
    pendingForm = form;
    const activating = form.dataset.statusAction === 'activate';
    const label = activating ? 'Activar producto' : 'Desactivar producto';
    dialog.querySelector('#product-status-title').textContent = label;
    dialog.querySelector('#product-status-label').textContent = `${form.dataset.productCode} — ${form.dataset.productName}`;
    dialog.querySelector('#product-status-description').textContent = activating
      ? 'Se mostrará en el catálogo público cuando su categoría esté activa. Sus datos e imagen se conservan.'
      : 'Dejará de mostrarse en el catálogo público. Sus datos e imagen se conservan y podés reactivarlo desde esta tabla.';
    const confirm = dialog.querySelector('#confirm-status-change');
    confirm.textContent = label;
    confirm.classList.toggle('button-activate', activating);
    confirm.classList.toggle('button-danger', !activating);
    dialog.showModal();
  });
  dialog?.querySelector('#cancel-status-change').addEventListener('click', () => dialog.close());
  dialog?.addEventListener('close', () => { pendingForm = undefined; });
  dialog?.querySelector('#confirm-status-change').addEventListener('click', () => {
    const form = pendingForm;
    if (!form) return;
    dialog.close();
    form.querySelector('button[type="submit"]').disabled = true;
    HTMLFormElement.prototype.submit.call(form);
  });
  window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-product-status-button]').forEach((button) => { button.disabled = false; });
  });
})();
