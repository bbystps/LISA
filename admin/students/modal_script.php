<script>
  (function() {
    const modal = document.getElementById('studentRegModal');
    const dialog = modal.querySelector('.modal__dialog');
    const overlay = modal.querySelector('.modal__overlay');
    const btnOpen = document.getElementById('btnAddStudent');
    const btnCloseEls = modal.querySelectorAll('[data-close-modal]');

    const form = document.getElementById('studentRegForm');
    const submitBtn = document.getElementById('regSubmitBtn');
    const alertBox = document.getElementById('formAlert');

    // Inputs
    const rfId = form.querySelector('#rfid_key');
    const studentId = form.querySelector('#student_id');
    const nameEl = form.querySelector('#name');
    const emailEl = form.querySelector('#email');
    const courseEl = form.querySelector('#course');
    const pwd = form.querySelector('#password');
    const pwd2 = form.querySelector('#password_confirm');

    const titleEl = document.getElementById('reg_title');

    // Hidden for edit context (added dynamically if missing)
    let hidOriginalId = form.querySelector('input[name="original_student_id"]');
    if (!hidOriginalId) {
      hidOriginalId = document.createElement('input');
      hidOriginalId.type = 'hidden';
      hidOriginalId.name = 'original_student_id';
      form.appendChild(hidOriginalId);
    }

    let mode = 'create'; // 'create' | 'edit'
    let lastFocused = null;

    const inertTargets = Array.from(document.body.children).filter(el => el !== modal);

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

    function validateClient() {
      clearErrors();
      let ok = true;

      // If create: passwords required; if edit: passwords optional but must match if provided
      if (mode === 'edit') {
        pwd.required = false;
        pwd2.required = false;
      } else {
        pwd.required = true;
        pwd2.required = true;
      }

      if (!form.checkValidity()) {
        Array.from(form.elements).forEach(el => {
          if (!el.checkValidity() && el.id) {
            ok = false;
            setErr(el.id, el.validationMessage);
          }
        });
      }

      if (pwd.value || pwd2.value) {
        if (pwd.value.length < 8) {
          ok = false;
          setErr('password', 'Password must be at least 8 characters.');
        }
        if (pwd.value !== pwd2.value) {
          ok = false;
          setErr('password_confirm', 'Passwords do not match.');
        }
      }

      return ok;
    }

    // ---------- Mode setup ----------
    function setFormMode(nextMode, data = {}) {
      mode = nextMode === 'edit' ? 'edit' : 'create';

      // Defaults
      form.reset();
      clearErrors();

      if (mode === 'create') {
        titleEl.textContent = 'Student Registration';
        submitBtn.textContent = 'Create account';
        form.action = 'register.php';

        rfId.readOnly = false;
        studentId.readOnly = false;
        pwd.required = true;
        pwd2.required = true;
        hidOriginalId.value = '';

      } else {
        titleEl.textContent = 'Edit Student';
        submitBtn.textContent = 'Save changes';
        form.action = 'update_student.php';

        // Pre-fill
        rfId.value = data.rfid_key || '';
        studentId.value = data.student_id || '';
        nameEl.value = data.name || '';
        emailEl.value = data.email || '';
        courseEl.value = data.course || '';
        pwd.value = '';
        pwd2.value = '';

        // Lock keys (you can change to allow edits; just set readOnly=false)
        rfId.readOnly = true;
        studentId.readOnly = true;
        pwd.required = false;
        pwd2.required = false;

        // Carry original id for server WHERE clause
        hidOriginalId.value = data.student_id || '';
      }
    }

    // ---------- Focus trap & open/close ----------
    function keydownTrap(e) {
      if (!modal.classList.contains('is-open')) return;
      const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
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
      setTimeout(() => (rfId || studentId || form.querySelector('input,select,button'))?.focus(), 0);

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

    // Expose a single entry point that **chooses mode & opens**
    window.openStudentRegModal = function(opts) {
      const nextMode = (opts && opts.mode) || 'create';
      const data = (opts && opts.data) || {};
      setFormMode(nextMode, data);
      openModal();
    };
    window.closeStudentRegModal = closeModal;

    // Wire close actions
    btnOpen && btnOpen.addEventListener('click', () => window.openStudentRegModal({
      mode: 'create'
    }));
    btnCloseEls.forEach(b => b.addEventListener('click', closeModal));
    overlay && overlay.addEventListener('click', closeModal);

    // RFID normalizer
    if (rfId) {
      rfId.addEventListener('blur', () => {
        rfId.value = rfId.value.replace(/[\s-]/g, '').toUpperCase();
      });
    }

    // Submit
    $(form).on('submit', function(e) {
      e.preventDefault();
      if (!validateClient()) {
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      const fd = new FormData(form);

      // In EDIT mode, if password fields empty → remove them so server won't change password
      if (mode === 'edit' && !pwd.value && !pwd2.value) {
        fd.delete('password');
        fd.delete('password_confirm');
      }

      submitBtn.disabled = true;
      alertBox.textContent = '';
      alertBox.classList.remove('show');

      $.ajax({
        type: 'POST',
        url: form.action,
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
            const errs = (res && res.errors) ? res.errors : {
              form: 'Please fix the errors and try again.'
            };
            // paint errors
            Object.entries(errs).forEach(([k, v]) => setErr(k, v));
            if (errs.form) {
              alertBox.textContent = errs.form;
              alertBox.classList.add('show');
            }
            return;
          }

          // Success
          if (window.studentsTable) {
            // For both create & edit, simplest is to reload current page
            window.studentsTable.ajax.reload(null, false);
          }

          form.reset();
          clearErrors();
          setTimeout(() => closeModal(), 300);
        },
        error: function(xhr) {
          const msg = (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.form) ?
            xhr.responseJSON.errors.form :
            ((xhr.responseText || '').toString().trim().substring(0, 400) || 'Network/Server error. Please try again.');
          alertBox.textContent = msg;
          alertBox.classList.add('show');
        },
        complete: function() {
          submitBtn.disabled = false;
        }
      });
    });
  })();
</script>