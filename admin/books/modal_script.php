<script>
  (function() {
    const modal = document.getElementById('bookRegModal');
    const dialog = modal.querySelector('.modal__dialog');
    const overlay = modal.querySelector('.modal__overlay');
    const btnOpen = document.getElementById('btnAddBook');
    const btnCloseEls = modal.querySelectorAll('[data-close-modal]');

    const form = document.getElementById('studentRegForm'); // kept to match your HTML
    const submitBtn = document.getElementById('regSubmitBtn');
    const alertBox = document.getElementById('formAlert');

    // Inputs (BOOK FIELDS ONLY)
    const bookIdEl = form.querySelector('#book_id');
    const titleEl = form.querySelector('#title');
    const authorEl = form.querySelector('#author');
    const categoryEl = form.querySelector('#category');

    let lastFocused = null;

    // Inert everything except the modal when open
    const inertTargets = Array.from(document.body.children).filter(el => el !== modal);

    // ---------------- Utilities ----------------
    function setErr(id, msg) {
      const p = form.querySelector(`.err-msg[data-err-for="${id}"]`);
      if (p) p.textContent = msg || '';
    }

    function clearErrors() {
      form.querySelectorAll('.err-msg').forEach(p => (p.textContent = ''));
      if (alertBox) {
        alertBox.textContent = '';
        alertBox.classList.remove('show');
      }
    }

    function paintServerErrors(errors) {
      if (!errors) return;
      let focused = false;

      Object.entries(errors).forEach(([k, v]) => {
        setErr(k, v);
        if (!focused) {
          const el = form.querySelector('#' + k);
          if (el) {
            el.focus();
            focused = true;
          }
        }
      });

      if (errors.form && alertBox) {
        alertBox.textContent = errors.form;
        alertBox.classList.add('show');
      }
    }

    function validateClient() {
      clearErrors();
      let ok = true;

      // Built-in constraint validation
      if (!form.checkValidity()) {
        Array.from(form.elements).forEach(el => {
          if (!el.checkValidity() && el.id) {
            ok = false;
            setErr(el.id, el.validationMessage);
          }
        });
      }

      // Extra guardrails (optional, mirrors server rules loosely)
      if (bookIdEl && bookIdEl.value.trim().length < 3) {
        ok = false;
        setErr('book_id', 'Please provide at least 3 characters.');
      }
      if (titleEl && titleEl.value.trim().length === 0) {
        ok = false;
        setErr('title', 'Title is required.');
      }
      if (authorEl && authorEl.value.trim().length === 0) {
        ok = false;
        setErr('author', 'Author is required.');
      }
      if (categoryEl && !categoryEl.value) {
        ok = false;
        setErr('category', 'Please select a category.');
      }

      return ok;
    }

    // ---------------- Focus trap ----------------
    function keydownTrap(e) {
      if (!modal.classList.contains('is-open')) return;
      const focusable = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );
      if (!focusable.length) return;

      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (e.key === 'Tab') {
        if (e.shiftKey && document.activeElement === first) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      } else if (e.key === 'Escape') {
        closeModal();
      }
    }

    function trapFocus(enable) {
      if (enable) document.addEventListener('keydown', keydownTrap);
      else document.removeEventListener('keydown', keydownTrap);
    }

    // ---------------- Open / Close ----------------
    function openModal() {
      lastFocused = document.activeElement;

      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');

      inertTargets.forEach(el => {
        el.inert = true;
        el.setAttribute('aria-hidden', 'true');
      });

      document.body.style.overflow = 'hidden';

      dialog.focus({
        preventScroll: true
      });
      const firstField =
        form.querySelector('#book_id') ||
        form.querySelector('input,select,button');
      setTimeout(() => firstField && firstField.focus(), 0);

      trapFocus(true);
    }

    function closeModal() {
      trapFocus(false);

      inertTargets.forEach(el => {
        el.inert = false;
        el.removeAttribute('aria-hidden');
      });

      modal.setAttribute('aria-hidden', 'true');
      modal.classList.remove('is-open');
      document.body.style.overflow = '';

      form.reset();
      clearErrors();

      if (lastFocused) lastFocused.focus();
    }

    // Expose for external calls
    window.closeBookRegModal = closeModal;

    // ---------------- Wire events ----------------
    if (btnOpen) btnOpen.addEventListener('click', openModal);
    btnCloseEls.forEach(b => b.addEventListener('click', closeModal));
    if (overlay) overlay.addEventListener('click', closeModal);

    // ---------------- jQuery AJAX submit ----------------
    $(form).on('submit', function(e) {
      e.preventDefault();

      if (!validateClient()) {
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      const fd = new FormData(form);
      submitBtn.disabled = true;
      if (alertBox) {
        alertBox.textContent = '';
        alertBox.classList.remove('show');
      }

      $.ajax({
        type: 'POST',
        url: form.action || 'register.php', // /admin/books/register.php
        data: fd,
        processData: false,
        contentType: false,
        cache: false,
        dataType: 'json',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(res) {
          if (!res || res.ok !== true) {
            paintServerErrors((res && res.errors) ? res.errors : {
              form: 'Please fix the errors and try again.'
            });
            return;
          }

          // Success UX
          form.reset();
          clearErrors();
          if (alertBox) {
            alertBox.textContent = 'Book added successfully!';
            alertBox.classList.add('show');
          }

          // Refresh DataTable (if present)
          if ($.fn.DataTable && $('#booksTable').length) {
            $('#booksTable').DataTable().ajax.reload(null, false); // keep paging
          }

          setTimeout(() => closeModal(), 500);
        },
        error: function(xhr) {
          if (xhr.responseJSON && xhr.responseJSON.errors) {
            paintServerErrors(xhr.responseJSON.errors);
            return;
          }
          const txt = (xhr.responseText || '').toString().trim();
          paintServerErrors({
            form: txt ? txt.substring(0, 400) : 'Network/Server error. Please try again.'
          });
        },
        complete: function() {
          submitBtn.disabled = false;
        }
      });
    });
  })();
</script>