document.addEventListener('DOMContentLoaded', function () {
    const vars = window.gnn_vars || {};
    const i18n = vars.i18n || {};

    /* ─── Toast Helper ─── */
    function gnnToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `gnn-toast gnn-toast-${type}`;
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s ease';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /* ─── Button Loading State Helper ─── */
    function setBtnLoading(form, isLoading) {
        const btn = form.querySelector('button[type="submit"]');
        if (!btn) return;
        const btnText = btn.querySelector('.btn-text');
        const btnLoader = btn.querySelector('.btn-loader');
        btn.disabled = isLoading;
        if (btnText) btnText.style.display = isLoading ? 'none' : '';
        if (btnLoader) btnLoader.style.display = isLoading ? 'inline-flex' : 'none';
    }

    /* ─── URL Shortening Form ─── */
    const shortnerForm = document.getElementById('gnn-shortner-form');
    if (shortnerForm) {
        shortnerForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(shortnerForm);
            formData.append('action', 'gnn_shortner');
            formData.append('nonce', vars.nonce || '');

            setBtnLoading(shortnerForm, true);

            try {
                const res = await fetch(vars.ajax_url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                const response = await res.json();
                setBtnLoading(shortnerForm, false);

                if (response && response.success && response.data && response.data.short_url) {
                    const resultEl = document.getElementById('gnn-result');
                    if (resultEl) {
                        resultEl.textContent = '';
                        const codeEl = document.createElement('code');
                        codeEl.textContent = response.data.short_url;
                        codeEl.setAttribute('tabindex', '0');
                        codeEl.setAttribute('role', 'button');
                        codeEl.setAttribute('aria-label', i18n.copy_label || 'Copy short URL to clipboard');
                        resultEl.appendChild(codeEl);
                    }
                } else {
                    gnnToast((response && response.data) || i18n.error_generic || 'An error occurred. Please try again.', 'error');
                }
            } catch {
                setBtnLoading(shortnerForm, false);
                gnnToast(i18n.error_generic || 'An error occurred. Please try again.', 'error');
            }
        });
    }

    /* ─── Password Verification Form ─── */
    const passwordForm = document.getElementById('gnn-password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(passwordForm);
            formData.append('action', 'gnn_verify_password');
            formData.append('nonce', vars.nonce || '');

            setBtnLoading(passwordForm, true);

            try {
                const res = await fetch(vars.ajax_url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                const response = await res.json();

                if (response && response.success) {
                    window.location.reload();
                } else {
                    setBtnLoading(passwordForm, false);
                    gnnToast((response && response.data) || i18n.error_generic || 'An error occurred. Please try again.', 'error');
                    if (typeof window.grecaptcha !== 'undefined') {
                        window.grecaptcha.reset();
                    }
                }
            } catch {
                setBtnLoading(passwordForm, false);
                gnnToast(i18n.error_generic || 'An error occurred. Please try again.', 'error');
                if (typeof window.grecaptcha !== 'undefined') {
                    window.grecaptcha.reset();
                }
            }
        });
    }

    /* ─── Copy to Clipboard (Click & Keyboard) ─── */
    function copyResultCode(codeEl) {
        const url = codeEl.textContent;
        if (!url || !navigator.clipboard) return;
        navigator.clipboard.writeText(url).then(() => {
            gnnToast(`${i18n.copied || 'Copied to clipboard:'} ${url}`);
        });
    }

    document.addEventListener('click', function (e) {
        const codeEl = e.target.closest('#gnn-result code');
        if (codeEl) {
            copyResultCode(codeEl);
        }
    });

    document.addEventListener('keydown', function (e) {
        const codeEl = e.target.closest('#gnn-result code');
        if (codeEl && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            copyResultCode(codeEl);
        }
    });

    /* ─── Accessible Modal Helpers ─── */
    let activeModal = null;
    let lastFocusedEl = null;

    function getFocusableElements(container) {
        return Array.from(
            container.querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')
        );
    }

    function openModal(modal, focusTarget) {
        if (!modal) return;
        lastFocusedEl = document.activeElement;
        activeModal = modal;
        modal.classList.add('gnn-active');
        const target = focusTarget || getFocusableElements(modal)[0];
        if (target) {
            setTimeout(() => target.focus(), 20);
        }
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('gnn-active');
        if (activeModal === modal) {
            activeModal = null;
        }
        if (lastFocusedEl && typeof lastFocusedEl.focus === 'function') {
            lastFocusedEl.focus();
            lastFocusedEl = null;
        }
    }

    /* ─── Delete URL ─── */
    let deleteId = null;
    const deleteModal = document.getElementById('gnn-delete-modal');
    const deleteYesBtn = document.getElementById('gnn-delete-yes');
    const deleteNoBtn = document.getElementById('gnn-delete-no');

    document.addEventListener('click', function (e) {
        const delBtn = e.target.closest('.gnn-delete-url');
        if (delBtn) {
            e.preventDefault();
            deleteId = delBtn.dataset.id || null;
            openModal(deleteModal, deleteNoBtn);
        }
    });

    if (deleteYesBtn) {
        deleteYesBtn.addEventListener('click', async function () {
            if (!deleteId) return;
            const body = new URLSearchParams({
                action: 'gnn_delete_url',
                nonce: vars.nonce || '',
                id: deleteId
            });

            try {
                const res = await fetch(vars.ajax_url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: body.toString()
                });
                const response = await res.json();

                if (response && response.success) {
                    const row = document.querySelector(`tr[data-id="${CSS.escape(String(deleteId))}"]`);
                    if (row) {
                        row.style.transition = 'opacity 0.2s ease';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 200);
                    }
                    closeModal(deleteModal);
                    deleteId = null;
                    gnnToast(i18n.deleted || 'URL deleted successfully.');
                } else {
                    gnnToast((response && response.data) || i18n.delete_failed || 'Failed to delete.', 'error');
                }
            } catch {
                gnnToast(i18n.request_failed || 'Request failed. Please try again.', 'error');
            }
        });
    }

    if (deleteNoBtn) {
        deleteNoBtn.addEventListener('click', function () {
            closeModal(deleteModal);
            deleteId = null;
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === deleteModal) {
                closeModal(deleteModal);
                deleteId = null;
            }
        });
    }

    /* ─── Edit URL ─── */
    const editModal = document.getElementById('gnn-edit-modal');
    const editForm = document.getElementById('gnn-edit-url-form');
    const editCancelBtn = document.getElementById('gnn-edit-cancel');
    const editCloseBtn = document.getElementById('gnn-edit-close');
    const editShortInput = document.getElementById('gnn-edit-short-url');
    const editLongInput = document.getElementById('gnn-edit-long-url');

    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.gnn-edit-url');
        if (!editBtn || !editModal || !editForm) return;
        e.preventDefault();

        const id = editBtn.dataset.id;
        const row = document.querySelector(`tr[data-id="${CSS.escape(String(id))}"]`);
        if (!row) return;

        const slug = row.dataset.slug || '';
        const longUrlCell = row.querySelector('.gnn-long-url');
        const longUrl = longUrlCell ? (longUrlCell.getAttribute('title') || '') : '';

        const idInput = editForm.querySelector('input[name="id"]');
        if (idInput) idInput.value = id;
        if (editShortInput) editShortInput.value = slug;
        if (editLongInput) editLongInput.value = longUrl;

        openModal(editModal, editShortInput);
    });

    if (editCancelBtn) {
        editCancelBtn.addEventListener('click', () => closeModal(editModal));
    }
    if (editCloseBtn) {
        editCloseBtn.addEventListener('click', () => closeModal(editModal));
    }
    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) {
                closeModal(editModal);
            }
        });
    }

    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const idVal = editForm.querySelector('input[name="id"]')?.value || '';
            const shortVal = editShortInput ? editShortInput.value : '';
            const longVal = editLongInput ? editLongInput.value : '';

            const body = new URLSearchParams({
                action: 'gnn_edit_url',
                nonce: vars.nonce || '',
                id: idVal,
                short_url: shortVal,
                long_url: longVal
            });

            try {
                const res = await fetch(vars.ajax_url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: body.toString()
                });
                const response = await res.json();

                if (response && response.success && response.data) {
                    const row = document.querySelector(`tr[data-id="${CSS.escape(String(idVal))}"]`);
                    if (row) {
                        if (response.data.slug) {
                            row.dataset.slug = response.data.slug;
                        }
                        const linkEl = row.querySelector('.gnn-short-link');
                        if (linkEl) {
                            linkEl.setAttribute('href', response.data.short_url);
                            linkEl.textContent = response.data.short_url;
                        }
                        const longCell = row.querySelector('.gnn-long-url');
                        if (longCell) {
                            longCell.setAttribute('title', response.data.long_url);
                            longCell.textContent = response.data.long_url;
                        }
                    }
                    closeModal(editModal);
                    gnnToast(i18n.updated || 'URL updated successfully.');
                } else {
                    gnnToast((response && response.data) || i18n.update_failed || 'Failed to update.', 'error');
                }
            } catch {
                gnnToast(i18n.request_failed || 'Request failed. Please try again.', 'error');
            }
        });
    }

    /* ─── Modal Keyboard Accessibility (Escape & Focus Trap) ─── */
    document.addEventListener('keydown', function (e) {
        if (!activeModal) return;

        if (e.key === 'Escape') {
            if (activeModal === deleteModal) {
                deleteId = null;
            }
            closeModal(activeModal);
            return;
        }

        if (e.key === 'Tab') {
            const focusables = getFocusableElements(activeModal);
            if (focusables.length === 0) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });
});
