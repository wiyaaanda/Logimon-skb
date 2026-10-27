document.addEventListener('DOMContentLoaded', function () {
  const toastContainer = document.getElementById('toast-container');

  document.querySelectorAll('.auth-form, form[action$="/logout"]').forEach(function (form) {
    form.addEventListener('submit', function () {
      if (form.dataset.loading === 'true') return;

      form.dataset.loading = 'true';

      if (document.querySelector('.auth-loading-overlay')) return;

      const overlay = document.createElement('div');
      overlay.className = 'auth-loading-overlay';
      overlay.setAttribute('role', 'status');
      overlay.setAttribute('aria-live', 'polite');
      overlay.innerHTML = '<div class="auth-loading-panel"><span class="auth-loading-spinner" aria-hidden="true"></span><strong>Memproses...</strong><small>Mohon tunggu sebentar</small></div>';
      document.body.appendChild(overlay);
    });
  });

  const driverFab = document.getElementById('driverFab');
  const driverFabToggle = document.getElementById('driverFabToggle');
  const driverFabMenu = document.getElementById('driverFabMenu');

  if (driverFab && driverFabToggle && driverFabMenu) {
    driverFabToggle.addEventListener('click', function () {
      const isOpen = driverFab.classList.toggle('is-open');
      driverFabMenu.hidden = !isOpen;
      driverFabToggle.setAttribute('aria-expanded', String(isOpen));
      driverFabToggle.setAttribute('title', isOpen ? 'Tutup menu aksi' : 'Buka menu aksi');
      driverFabToggle.setAttribute('aria-label', isOpen ? 'Tutup menu aksi' : 'Buka menu aksi');
    });
  }

  const showToast = (type, message) => {
    if (!toastContainer || !message) return;

    const toast = document.createElement('div');

    toast.className = 'toast toast--' + type;
    toast.innerHTML = `
  document.addEventListener('click', function (event) {
    if (event.target.closest('.action-menu')) return;
    document.querySelectorAll('.action-menu.is-open').forEach(function (menu) {
      menu.classList.remove('is-open');
      menu.querySelector('.action-menu__trigger')?.setAttribute('aria-expanded', 'false');
      const items = menu.querySelector('.action-menu__items');
      if (items) {
        items.classList.remove('opens-up');
        items.style.top = '';
        items.style.left = '';
      }
    });
  });
      <div class="toast__body">${message}</div>
      <button type="button" class="toast__close" aria-label="Tutup notifikasi">×</button>
    `;

    const closeBtn = toast.querySelector('.toast__close');
    closeBtn.addEventListener('click', function () {
      toast.remove();
    });

    toastContainer.appendChild(toast);
    setTimeout(function () {
      toast.remove();
    }, 3500);
  };

  const showSuccessModal = function (message) {
    if (!window.Swal) {
      showToast('success', message);
      return;
    }

    Swal.fire({
      title: 'Berhasil',
      text: message,
      icon: false,
      animation: false,
      showConfirmButton: false,
      showCancelButton: false,
      timer: 2000,
      timerProgressBar: false,
      buttonsStyling: false,
      customClass: {
        popup: 'swal2-modern-confirm swal2-success-confirm'
      },
      allowOutsideClick: true,
      width: '420px'
    });
  };

  const confirmAction = function (options) {
    const settings = Object.assign({
      title: 'Konfirmasi',
      text: 'Apakah Anda yakin?',
      confirmButtonText: 'Iya',
      cancelButtonText: 'Batal',
      icon: 'warning',
      dangerMode: true,
      onConfirm: null,
      onCancel: null
    }, options || {});

    if (!window.Swal) {
      if (settings.onConfirm) settings.onConfirm();
      return;
    }

    Swal.fire({
      title: settings.title,
      text: settings.text,
      icon: false,
      animation: false,
      showCancelButton: true,
      confirmButtonText: settings.confirmButtonText,
      cancelButtonText: settings.cancelButtonText,
      reverseButtons: true,
      buttonsStyling: false,
      customClass: {
        popup: 'swal2-modern-confirm',
        confirmButton: 'swal2-confirm',
        cancelButton: 'swal2-cancel'
      },
      backdrop: true,
      allowOutsideClick: true,
      width: '420px'
    }).then(function (result) {
      if (result.isConfirmed) {
        if (settings.onConfirm) settings.onConfirm();
      } else if (settings.onCancel) {
        settings.onCancel();
      }
    });
  };

  window.confirmAction = confirmAction;

  const flushFlashMessages = () => {
    if (!window.__flashMessages || !Array.isArray(window.__flashMessages)) return;
    if (window.__flashMessagesShown) return;

    window.__flashMessages.forEach(function (item) {
      const type = item.type || 'success';
      const message = item.message || '';
      if (!message) return;

      if (type === 'success') {
        showSuccessModal(message);
      } else {
        showToast(type, message);
      }
    });

    window.__flashMessagesShown = true;
    delete window.__flashMessages;
  };

  flushFlashMessages();
  window.addEventListener('load', flushFlashMessages, { once: true });

  let loadPage;
  const submitDeleteForm = async function (form) {
    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
      });

      if (!response.ok) throw new Error('Delete request failed');
      await loadPage(window.location.href, false);
    } catch (error) {
      showToast('error', 'Data gagal dihapus. Silakan coba lagi.');
    }
  };

  const deleteForms = document.querySelectorAll('form[data-confirm-delete], form[data-confirm]');
  deleteForms.forEach(function (form) {
    form.dataset.confirmDeleteBound = 'true';
    form.addEventListener('submit', function (event) {
      if (form.dataset.confirmHandled === 'true') return;
      event.preventDefault();

      confirmAction({
        title: 'Hapus data?',
        text: 'Data yang dihapus tidak dapat dikembalikan.',
        confirmButtonText: 'Iya',
        cancelButtonText: 'Batal',
        icon: 'warning',
        onConfirm: function () {
          form.dataset.confirmHandled = 'true';
          submitDeleteForm(form);
        }
      });
    });
  });

  document.querySelectorAll('form').forEach(function (form) {
    const methodInput = form.querySelector('input[name="_method"][value="DELETE"]');
    if (!methodInput) return;

    if (form.dataset.confirmDeleteBound === 'true') return;
    form.dataset.confirmDeleteBound = 'true';

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      confirmAction({
        title: 'Perhatian',
        text: 'Data yang dihapus tidak dapat dikembalikan. Lanjutkan?',
        confirmButtonText: 'Iya',
        cancelButtonText: 'Batal',
        icon: 'warning',
        onConfirm: function () {
          submitDeleteForm(form);
        }
      });
    });
  });

  document.querySelectorAll('[data-confirm]').forEach(function (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();

      const title = button.dataset.confirmTitle || 'Konfirmasi';
      const text = button.dataset.confirm || 'Apakah Anda yakin?';
      const action = button.dataset.confirmAction || 'Iya';
      const form = button.closest('form');

      confirmAction({
        title: title,
        text: text,
        confirmButtonText: action,
        cancelButtonText: 'Batal',
        icon: 'warning',
        onConfirm: function () {
          if (form) {
            form.submit();
          } else if (button.type === 'submit') {
            button.form?.submit();
          }
        }
      });
    });
  });

  const successButtons = document.querySelectorAll('[data-toast-success]');
  successButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const text = button.dataset.toastSuccess || 'Data berhasil diproses.';
      showToast('success', text);
    });
  });

  const escapeImportHtml = function (value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  };

  const importErrorMessage = function (payload) {
    const errors = Array.isArray(payload?.errors) ? payload.errors : [];
    const message = payload?.message || 'Import gagal diproses.';
    const errorList = errors.length
      ? '<ul class="import-error-list">' + errors.map(function (error) {
        return '<li>' + escapeImportHtml(error) + '</li>';
      }).join('') + '</ul>'
      : '';

    return '<div class="import-error-message"><strong>' + escapeImportHtml(message) + '</strong>' + errorList + '</div>';
  };

  const bindImportForms = function (container) {
    container.querySelectorAll('.js-import-form:not([data-import-ready])').forEach(function (form) {
      const fileInput = form.querySelector('.js-import-file');
      const trigger = form.querySelector('.import-trigger');
      if (!fileInput || !trigger) return;

      form.dataset.importReady = 'true';
      const actionGroup = form.closest('.master-toolbar')?.querySelector('.master-actions') || form.closest('.header-actions');
      if (actionGroup && !actionGroup.contains(form)) actionGroup.appendChild(form);
      trigger.addEventListener('click', function () {
        fileInput.click();
      });

      fileInput.addEventListener('change', async function () {
        const file = fileInput.files?.[0];
        if (!file) return;

        trigger.disabled = true;
        form.classList.add('is-import-previewing');
        const formData = new FormData();
        formData.append('file', file);

        try {
          const response = await fetch(form.dataset.previewUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
          });
          const payload = await response.json();
          if (!response.ok || !payload.valid) throw payload;

          const columns = payload.columns || [];
          const rows = payload.rows || [];
          const warningHtml = payload.warnings?.length
            ? '<div class="import-warning-message"><strong>Beberapa data akan dilewati:</strong><ul class="import-error-list">' + payload.warnings.map(escapeImportHtml).map(function (warning) {
              return '<li>' + warning + '</li>';
            }).join('') + '</ul></div>'
            : '';
          const tableHead = columns.map(function (column) {
            return '<th>' + escapeImportHtml(column) + '</th>';
          }).join('');
          const tableBody = rows.map(function (row) {
            return '<tr>' + columns.map(function (column) {
              return '<td>' + escapeImportHtml(row[column]) + '</td>';
            }).join('') + '</tr>';
          }).join('');
          const previewHtml = `
            <div class="import-preview">
              <p class="import-preview__summary">${escapeImportHtml(file.name)} · ${payload.total} baris ditemukan</p>
              ${warningHtml}
              <div class="import-preview__table-wrap">
                <table class="import-preview__table"><thead><tr>${tableHead}</tr></thead><tbody>${tableBody}</tbody></table>
              </div>
              ${payload.total > rows.length ? '<p class="import-preview__note">Menampilkan maksimal 100 baris pertama.</p>' : ''}
            </div>
          `;

          const result = await Swal.fire({
            title: 'Preview data import',
            html: previewHtml,
            width: 'min(1100px, 94vw)',
            showCancelButton: true,
            confirmButtonText: 'Import data',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
              popup: 'swal2-import-preview',
              confirmButton: 'swal2-confirm',
              cancelButton: 'swal2-cancel'
            },
            showLoaderOnConfirm: true,
            allowOutsideClick: function () { return !Swal.isLoading(); },
            preConfirm: async function () {
              Swal.update({
                html: '<div class="import-progress"><strong>Memproses import...</strong><div class="import-progress__track"><span></span></div><small>Mohon tunggu, data sedang divalidasi dan disimpan.</small></div>',
                showCancelButton: false,
                showConfirmButton: false
              });
              Swal.showLoading();
              try {
                const importResponse = await fetch(form.dataset.importUrl, {
                  method: 'POST',
                  body: new FormData(form),
                  headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const importPayload = await importResponse.json();
                if (!importResponse.ok) throw importPayload;
                return importPayload;
              } catch (error) {
                Swal.hideLoading();
                Swal.update({
                  showCancelButton: true,
                  showConfirmButton: true,
                  confirmButtonText: 'Coba lagi',
                  cancelButtonText: 'Tutup'
                });
                Swal.showValidationMessage(importErrorMessage(error));
                return false;
              }
            }
          });

          if (result.isConfirmed) {
            await Swal.fire({
              title: 'Import berhasil',
              text: result.value?.message || 'Data berhasil diimport.',
              icon: 'success',
              showConfirmButton: false,
              timer: 1600
            });
            window.location.reload();
          }
        } catch (error) {
          await Swal.fire({
            title: 'Data tidak valid',
            html: importErrorMessage(error),
            icon: 'error',
            confirmButtonText: 'Tutup',
            buttonsStyling: false,
            customClass: {
              popup: 'swal2-import-error',
              confirmButton: 'swal2-confirm'
            }
          });
        } finally {
          trigger.disabled = false;
          form.classList.remove('is-import-previewing');
        }
      });
    });
  };

  bindImportForms(document);

  const enhanceBulkTables = function (container) {
    container.querySelectorAll('table:not([data-bulk-ready])').forEach(function (table) {
      if (table.hasAttribute('data-no-bulk-selection')) return;

      const headerRow = table.querySelector('thead tr');
      const bodyRows = Array.from(table.querySelectorAll('tbody tr')).filter(function (row) {
        return row.querySelector('td') && !row.querySelector('td[colspan]');
      });

      if (!headerRow || bodyRows.length === 0) return;

      const headers = Array.from(headerRow.querySelectorAll('th'));
      const lastColumnIndex = headers.length - 1;
      const lastColumnContainsActions = bodyRows.every(function (row) {
        const lastCell = row.querySelectorAll('td')[lastColumnIndex];
        return lastCell && lastCell.querySelector('a, button, form');
      });
      const actionColumn = headers.length && (
        headers[lastColumnIndex].textContent.trim().toLowerCase() === 'aksi' || lastColumnContainsActions
      ) ? lastColumnIndex : -1;

      if (actionColumn !== -1) {
        bodyRows.forEach(function (row) {
          const actionCell = row.querySelectorAll('td')[actionColumn];
          if (!actionCell || actionCell.dataset.actionMenuReady === 'true') return;

          const menu = document.createElement('div');
          const trigger = document.createElement('button');
          const items = document.createElement('div');
          menu.className = 'action-menu';
          actionCell.dataset.actionMenuReady = 'true';
          trigger.type = 'button';
          trigger.className = 'action-menu__trigger';
          trigger.setAttribute('aria-label', 'Buka menu aksi');
          trigger.setAttribute('aria-expanded', 'false');
          trigger.innerHTML = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>';
          items.className = 'action-menu__items';

          while (actionCell.firstChild) items.appendChild(actionCell.firstChild);
          items.querySelectorAll('a, button').forEach(function (control) {
            const rawLabel = control.getAttribute('aria-label') || control.getAttribute('title') || control.closest('form')?.getAttribute('title');
            const label = rawLabel?.match(/^edit\b/i) ? 'Edit'
              : rawLabel?.match(/^reset password\b/i) ? 'Reset password'
              : rawLabel?.match(/^hapus\b/i) ? 'Hapus'
              : rawLabel?.match(/detail/i) ? 'Detail'
              : rawLabel;
            if (!label || control.querySelector('.action-menu__label')) return;

            const labelElement = document.createElement('span');
            labelElement.className = 'action-menu__label';
            labelElement.textContent = label;
            control.appendChild(labelElement);
          });
          menu.appendChild(trigger);
          menu.appendChild(items);
          actionCell.appendChild(menu);
          actionCell.classList.add('action-cell');

          trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const isOpen = menu.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', String(isOpen));

            if (isOpen) {
              const triggerRect = trigger.getBoundingClientRect();
              const menuRect = items.getBoundingClientRect();
              const gap = 6;
              const edge = 8;
              const opensUp = triggerRect.bottom + menuRect.height + gap > window.innerHeight;
              const top = opensUp
                ? triggerRect.top - menuRect.height - gap
                : triggerRect.bottom + gap;
              const left = Math.min(
                Math.max(edge, triggerRect.right - menuRect.width),
                window.innerWidth - menuRect.width - edge
              );

              items.classList.toggle('opens-up', opensUp);
              items.style.top = Math.max(edge, top) + 'px';
              items.style.left = left + 'px';
            } else {
              items.classList.remove('opens-up');
              items.style.top = '';
              items.style.left = '';
            }
          });
        });
      }

      const deleteRows = bodyRows.filter(function (row) {
        return row.querySelector('form input[name="_method"][value="DELETE"]');
      });
      const wrapper = table.closest('.table-responsive') || table.parentElement;
      if (!wrapper) return;

      table.dataset.bulkReady = 'true';
      table.classList.add('has-bulk-selection');

      const selectionHeader = document.createElement('th');
      selectionHeader.className = 'bulk-selection-cell';
      selectionHeader.innerHTML = '<input type="checkbox" class="bulk-select-all" aria-label="Pilih semua baris">';
      headerRow.prepend(selectionHeader);

      bodyRows.forEach(function (row) {
        const cell = document.createElement('td');
        const deleteForm = row.querySelector('form input[name="_method"][value="DELETE"]')?.form;
        cell.className = 'bulk-selection-cell';
        cell.innerHTML = '<input type="checkbox" class="bulk-row-select" aria-label="Pilih baris">';
        if (deleteForm) cell.firstElementChild.dataset.deleteAction = deleteForm.action;
        row.prepend(cell);
      });

      const toolbar = document.createElement('div');
      toolbar.className = 'bulk-actions';
      toolbar.hidden = true;
      toolbar.innerHTML = `
        <span class="bulk-selection-count" aria-live="polite">0 dipilih</span>
        <select class="bulk-export-select" aria-label="Export data terpilih">
          <option value="">Export...</option>
          <option value="excel">Excel</option>
          <option value="csv">CSV</option>
        </select>
        ${deleteRows.length ? '<button type="button" class="btn btn-outline-danger bulk-delete-button">Hapus</button>' : ''}
      `;
      wrapper.parentElement.insertBefore(toolbar, wrapper);

      const selectAll = toolbar.previousElementSibling?.querySelector('.bulk-select-all') || table.querySelector('.bulk-select-all');
      const tableSignature = Array.from(headerRow.querySelectorAll('th')).slice(1).map(function (header) {
        return header.textContent.trim();
      }).join('|');
      window.__bulkSelectionStore = window.__bulkSelectionStore || new Map();
      const selectionUrl = new URL(window.location.href);
      selectionUrl.searchParams.delete('page');
      const selectionKey = selectionUrl.pathname + '?' + selectionUrl.searchParams.toString() + '|' + tableSignature;
      const selection = window.__bulkSelectionStore.get(selectionKey) || { records: new Map(), allPagesSelected: false, totalCount: 0 };
      window.__bulkSelectionStore.set(selectionKey, selection);
      const recordFromRow = function (row) {
        const cells = Array.from(row.querySelectorAll('td'));
        const dataCells = cells[0]?.querySelector('.bulk-row-select') ? cells.slice(1) : cells;
        const deleteForm = row.querySelector('form input[name="_method"][value="DELETE"]')?.form;
        return {
          key: deleteForm?.action || dataCells.map(function (cell) { return cell.textContent.trim(); }).join('|'),
          cells: dataCells.map(function (cell) { return cell.textContent.replace(/\s+/g, ' ').trim(); }),
          deleteAction: deleteForm?.action || '',
          token: deleteForm?.querySelector('input[name="_token"]')?.value || ''
        };
      };
      bodyRows.forEach(function (row) {
        const record = recordFromRow(row);
        row.querySelector('.bulk-row-select').checked = selection.allPagesSelected || selection.records.has(record.key);
      });
      const getSelectedRecords = function () {
        if (selection.allPagesSelected && selection.records.size === 0) {
          return bodyRows.map(recordFromRow);
        }
        return Array.from(selection.records.values());
      };
      const getSelectedCount = function () {
        return selection.allPagesSelected ? selection.totalCount : selection.records.size;
      };
      const getPaginationUrls = function () {
        const content = document.querySelector('.content-area') || document;
        return Array.from(content.querySelectorAll('nav a, .pagination a, a[href*="page="]'))
          .map(function (link) { return new URL(link.href, window.location.origin); })
          .filter(function (url, index, urls) {
            return url.searchParams.has('page') && urls.findIndex(function (candidate) {
              return candidate.href === url.href;
            }) === index;
          });
      };
      const collectAllPageRecords = async function () {
        const records = bodyRows.map(recordFromRow);
        const currentUrl = new URL(window.location.href);
        const urls = getPaginationUrls().filter(function (url) {
          return url.href !== currentUrl.href;
        });
        const pages = await Promise.all(urls.map(async function (url) {
          const response = await fetch(url.href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
          if (!response.ok) throw new Error('Unable to load paginated rows');
          const documentPage = new DOMParser().parseFromString(await response.text(), 'text/html');
          const matchingTable = Array.from(documentPage.querySelectorAll('table')).find(function (candidate) {
            return Array.from(candidate.querySelectorAll('thead th')).map(function (header) {
              return header.textContent.trim();
            }).join('|') === tableSignature;
          });
          if (!matchingTable) return [];
          return Array.from(matchingTable.querySelectorAll('tbody tr')).filter(function (row) {
            return row.querySelector('td') && !row.querySelector('td[colspan]');
          }).map(recordFromRow);
        }));
        return records.concat(...pages);
      };
      const updateToolbar = function () {
        const selectedCount = getSelectedCount();
        toolbar.hidden = selectedCount === 0;
        toolbar.querySelector('.bulk-selection-count').textContent = selectedCount + ' dipilih';
        if (selectAll) {
          selectAll.checked = selection.allPagesSelected || (selectedCount > 0 && selectedCount === bodyRows.length);
          selectAll.indeterminate = !selection.allPagesSelected && selectedCount > 0 && selectedCount < bodyRows.length;
        }
      };

      table.addEventListener('change', async function (event) {
        if (event.target.matches('.bulk-select-all')) {
          if (!event.target.checked) {
            selection.records.clear();
            selection.allPagesSelected = false;
            selection.totalCount = 0;
          } else {
            event.target.disabled = true;
            try {
              const paginationUrls = getPaginationUrls();
              if (paginationUrls.length === 0) {
                selection.records.clear();
                selection.totalCount = bodyRows.length;
              } else {
                const records = await collectAllPageRecords();
                records.forEach(function (record) { selection.records.set(record.key, record); });
                selection.totalCount = records.length;
              }
              selection.allPagesSelected = true;
            } finally {
              event.target.disabled = false;
            }
          }
          bodyRows.forEach(function (row) {
            const checkbox = row.querySelector('.bulk-row-select');
            if (checkbox) checkbox.checked = event.target.checked;
          });
        } else if (event.target.matches('.bulk-row-select')) {
          if (selection.allPagesSelected) {
            selection.records = new Map(bodyRows.map(function (row) {
              return [recordFromRow(row).key, recordFromRow(row)];
            }));
            selection.allPagesSelected = false;
            selection.totalCount = 0;
          }
          const record = recordFromRow(event.target.closest('tr'));
          if (event.target.checked) selection.records.set(record.key, record);
          else {
            selection.records.delete(record.key);
            selection.allPagesSelected = false;
          }
        }
        if (event.target.matches('.bulk-select-all, .bulk-row-select')) updateToolbar();
      });

      const escapeCsv = function (value) {
        const text = String(value || '').replace(/\s+/g, ' ').trim();
        return '"' + text.replace(/"/g, '""') + '"';
      };
      const exportData = function (records, format) {
        const headers = Array.from(headerRow.querySelectorAll('th')).slice(1).map(function (cell) {
          return cell.textContent.trim();
        });
        const lastDataCellIsAction = records.every(function (record) { return record.deleteAction || record.cells.at(-1)?.length === 0; });
        const actionIndex = headers.length && (headers[headers.length - 1].toLowerCase() === 'aksi' || lastDataCellIsAction)
          ? headers.length - 1
          : -1;
        const outputHeaders = actionIndex === -1 ? headers : headers.slice(0, actionIndex);
        const outputRows = records.map(function (record) {
          return (actionIndex === -1 ? record.cells : record.cells.slice(0, actionIndex));
        });
        const lines = [outputHeaders.map(escapeCsv).join(',')].concat(outputRows.map(function (values) {
          return values.map(escapeCsv).join(',');
        }));

        const fileName = (document.title || 'export').replace(/[^a-z0-9]+/gi, '-').toLowerCase();
        let content = '\uFEFF' + lines.join('\r\n');
        let mimeType = 'text/csv;charset=utf-8;';
        let extension = 'csv';

        if (format === 'excel') {
          const escapeHtml = function (value) {
            return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
          };
          content = '<table><thead><tr>' + outputHeaders.map(function (value) {
            return '<th>' + escapeHtml(value) + '</th>';
          }).join('') + '</tr></thead><tbody>' + outputRows.map(function (values) {
            return '<tr>' + values.map(function (value) {
              return '<td>' + escapeHtml(value) + '</td>';
            }).join('') + '</tr>';
          }).join('') + '</tbody></table>';
          mimeType = 'application/vnd.ms-excel;charset=utf-8;';
          extension = 'xls';
        }

        const blob = new Blob([content], { type: mimeType });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = fileName + '.' + extension;
        link.click();
        URL.revokeObjectURL(link.href);
      };

      toolbar.querySelector('.bulk-export-select').addEventListener('change', function (event) {
        const selectedRecords = getSelectedRecords();
        if (event.target.value && selectedRecords.length) exportData(selectedRecords, event.target.value);
        event.target.value = '';
      });

      const deleteButton = toolbar.querySelector('.bulk-delete-button');
      if (deleteButton) {
        deleteButton.addEventListener('click', function () {
          const selectedRecords = getSelectedRecords().filter(function (record) {
            return record.deleteAction;
          });
          if (!selectedRecords.length) return;

          confirmAction({
            title: 'Hapus data terpilih?',
            text: selectedRecords.length + ' data akan dihapus dan tidak dapat dikembalikan.',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            onConfirm: async function () {
              deleteButton.disabled = true;
              const jadwalRecords = selectedRecords.filter(function (record) {
                return /^\/jadwal\/\d+$/.test(new URL(record.deleteAction, window.location.origin).pathname);
              });
              const isJadwalBulkDelete = jadwalRecords.length === selectedRecords.length;
              Swal.fire({
                title: 'Menghapus data',
                html: '<div class="bulk-delete-progress"><strong class="bulk-delete-progress__label">Menyiapkan proses...</strong><div class="import-progress__track"><span class="bulk-delete-progress__bar"></span></div><small class="bulk-delete-progress__detail">0 dari ' + selectedRecords.length + ' data</small></div>',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: { popup: 'swal2-bulk-progress' },
                didOpen: async function () {
                  const progressLabel = Swal.getPopup().querySelector('.bulk-delete-progress__label');
                  const progressDetail = Swal.getPopup().querySelector('.bulk-delete-progress__detail');
                  const progressBar = Swal.getPopup().querySelector('.bulk-delete-progress__bar');

                  try {
                    if (isJadwalBulkDelete) {
                      const ids = jadwalRecords.map(function (record) {
                        return new URL(record.deleteAction, window.location.origin).pathname.split('/').pop();
                      });
                      const token = jadwalRecords[0].token || document.querySelector('input[name="_token"]')?.value || '';
                      const response = await fetch('/jadwal/bulk-delete', {
                        method: 'POST',
                        headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'Accept': 'application/json',
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ ids: ids })
                      });
                      if (!response.ok) throw new Error('Bulk delete request failed');
                      progressDetail.textContent = jadwalRecords.length + ' dari ' + selectedRecords.length + ' data';
                    } else {
                    for (const [index, record] of selectedRecords.entries()) {
                      const visibleForm = bodyRows.map(function (row) {
                        return row.querySelector('form input[name="_method"][value="DELETE"]')?.form;
                      }).find(function (form) {
                        return form?.action === record.deleteAction;
                      });
                      const formData = visibleForm ? new FormData(visibleForm) : new FormData();
                      if (!visibleForm) {
                        formData.append('_token', record.token || document.querySelector('input[name="_token"]')?.value || '');
                        formData.append('_method', 'DELETE');
                      }
                      const response = await fetch(record.deleteAction, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                      });
                      if (!response.ok) throw new Error('Delete request failed');

                      const completed = index + 1;
                      const percentage = Math.round((completed / selectedRecords.length) * 100);
                      progressLabel.textContent = 'Menghapus data... ' + percentage + '%';
                      progressDetail.textContent = completed + ' dari ' + selectedRecords.length + ' data';
                      progressBar.style.width = percentage + '%';
                    }
                    }

                    progressLabel.textContent = 'Data berhasil dihapus';
                    progressBar.style.width = '100%';
                    await new Promise(function (resolve) { setTimeout(resolve, 500); });
                    Swal.close();
                    window.location.reload();
                  } catch (error) {
                    Swal.close();
                    await Swal.fire({
                      title: 'Hapus gagal',
                      text: 'Sebagian data tidak berhasil dihapus. Silakan coba lagi.',
                      icon: 'error',
                      confirmButtonText: 'Tutup',
                      buttonsStyling: false,
                      customClass: { confirmButton: 'swal2-confirm' }
                    });
                  } finally {
                    deleteButton.disabled = false;
                  }
                }
              });
            }
          });
        });
      }
    });
  };

  enhanceBulkTables(document);

  const updateNavigationState = function (url) {
    const currentPath = new URL(url, window.location.origin).pathname;

    document.querySelectorAll('.sidebar-nav a.nav-item, .mobile-nav a').forEach(function (link) {
      const linkPath = new URL(link.href, window.location.origin).pathname;
      link.classList.toggle('active', linkPath === currentPath);
    });
  };

  const runPageScripts = function (container) {
    container.querySelectorAll('script').forEach(function (script) {
      if (script.src) return;

      const executable = document.createElement('script');
      executable.textContent = script.textContent;
      script.replaceWith(executable);
    });
  };

  loadPage = async function (url, addHistory) {
    document.body.classList.add('is-page-loading');

    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html'
        }
      });

      if (!response.ok) throw new Error('Page request failed');

      const html = await response.text();
      const page = new DOMParser().parseFromString(html, 'text/html');
      const nextContent = page.querySelector('.content-area');
      const currentContent = document.querySelector('.content-area');

      if (!nextContent || !currentContent) {
        window.location.href = url;
        return;
      }

      currentContent.innerHTML = nextContent.innerHTML;
      document.title = page.title || document.title;

      const nextHeading = page.querySelector('.page-title h1');
      const currentHeading = document.querySelector('.page-title h1');
      if (nextHeading && currentHeading) currentHeading.textContent = nextHeading.textContent;

      if (addHistory) window.history.pushState({}, '', url);
      updateNavigationState(url);
      runPageScripts(currentContent);
      bindImportForms(currentContent);
      enhanceBulkTables(currentContent);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
      window.location.href = url;
    } finally {
      document.body.classList.remove('is-page-loading');
    }
  };

  document.addEventListener('submit', function (event) {
    const form = event.target.closest('.rekap-filter-form, .status-log-filter-panel');
    if (form) {
      event.preventDefault();
      const params = new URLSearchParams(new FormData(form));
      const url = new URL(form.action || window.location.href, window.location.origin);
      url.search = params.toString();
      loadPage(url.href, true);
      return;
    }

    const deleteForm = event.target.closest('form');
    const methodInput = deleteForm?.querySelector('input[name="_method"][value="DELETE"]');
    if (!deleteForm || !methodInput || deleteForm.dataset.confirmDeleteBound === 'true') return;

    deleteForm.dataset.confirmDeleteBound = 'true';
    event.preventDefault();
    confirmAction({
      title: 'Perhatian',
      text: 'Data yang dihapus tidak dapat dikembalikan. Lanjutkan?',
      confirmButtonText: 'Iya',
      cancelButtonText: 'Batal',
      icon: 'warning',
      onConfirm: function () {
        submitDeleteForm(deleteForm);
      }
    });
  });

  document.addEventListener('click', function (event) {
    const link = event.target.closest('.sidebar-nav a.nav-item, .mobile-nav a');
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const url = new URL(link.href, window.location.origin);
    if (url.origin !== window.location.origin || link.target === '_blank' || url.pathname === window.location.pathname && url.search === window.location.search) return;

    event.preventDefault();
    loadPage(url.href, true);
  });

  window.addEventListener('popstate', function () {
    loadPage(window.location.href, false);
  });
});
