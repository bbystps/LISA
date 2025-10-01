<script>
  /* Requires: jQuery, your CSS, and DataTables (optional) already loaded */
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
    const agree = form.querySelector('#agree'); // optional

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

      // built-in validity
      if (!form.checkValidity()) {
        Array.from(form.elements).forEach(el => {
          if (!el.checkValidity() && el.id) {
            ok = false;
            setErr(el.id, el.validationMessage);
          }
        });
      }

      // password match
      if (pwd && pwd2 && pwd.value !== pwd2.value) {
        ok = false;
        setErr('password_confirm', 'Passwords do not match.');
      }

      // terms (only if checkbox exists)
      if (agree && !agree.checked) {
        ok = false;
        setErr('agree', 'You must agree to continue.');
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
      if (enable) {
        document.addEventListener('keydown', keydownTrap);
      } else {
        document.removeEventListener('keydown', keydownTrap);
      }
    }

    // ---------------- Open / Close ----------------
    function openModal() {
      lastFocused = document.activeElement;

      // Show and announce to AT
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');

      // Lock background for focus and AT
      inertTargets.forEach(el => {
        el.inert = true;
        el.setAttribute('aria-hidden', 'true');
      });

      // Prevent body scroll
      document.body.style.overflow = 'hidden';

      // Focus dialog (SR announcement), then first field
      dialog.focus({
        preventScroll: true
      });
      const firstField =
        form.querySelector('#rfid_key') ||
        form.querySelector('#student_id') ||
        form.querySelector('input,select,button');
      setTimeout(() => firstField && firstField.focus(), 0);

      trapFocus(true);
    }

    function closeModal() {
      trapFocus(false);

      // Restore background
      inertTargets.forEach(el => {
        el.inert = false;
        el.removeAttribute('aria-hidden');
      });

      // Hide from AT and visual
      modal.setAttribute('aria-hidden', 'true');
      modal.classList.remove('is-open');

      // Restore scroll
      document.body.style.overflow = '';

      // Reset form + errors
      form.reset();
      clearErrors();

      // Return focus
      if (lastFocused) lastFocused.focus();
    }

    // Make closable elsewhere if needed
    window.closeStudentRegModal = closeModal;

    // ---------------- Wire events ----------------
    if (btnOpen) btnOpen.addEventListener('click', openModal);
    btnCloseEls.forEach(b => b.addEventListener('click', closeModal));
    if (overlay) overlay.addEventListener('click', closeModal);

    if (pwd2) {
      pwd2.addEventListener('input', () => {
        if (pwd.value && pwd2.value && pwd.value !== pwd2.value) setErr('password_confirm', 'Passwords do not match.');
        else setErr('password_confirm', '');
      });
    }
    if (agree) agree.addEventListener('change', () => setErr('agree', ''));

    // RFID normalizer (optional)
    if (rfId) {
      rfId.addEventListener('blur', () => {
        rfId.value = rfId.value.replace(/[\s-]/g, '').toUpperCase();
      });
    }

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
        url: form.action || 'register.php',
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
            alertBox.textContent = 'Registered successfully!';
            alertBox.classList.add('show');
          }

          // Optionally add to DataTable
          // if ($.fn.DataTable && $('#studentsTable').length && res.student) {
          //   $('#studentsTable').DataTable().row.add([
          //     res.student.rfid_key,
          //     res.student.student_id,
          //     res.student.name,
          //     res.student.email,
          //     res.student.course,
          //     '0/3',
          //     '<td class="actions"><button class="icon-btn edit" title="Edit">Edit</button><button class="icon-btn del" title="Delete">Delete</button></td>'
          //   ]).draw(false);
          // }

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