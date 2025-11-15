<script>
  let lastOpener = null; // remember the element that opened the modal

  // Hook the table button to pass "this" as opener
  // (If you already have this handler, just add the third arg.)
  $(document).on('click', '.table-btn.borrow', function() {
    const sid = this.getAttribute('data-sid');
    const title = this.getAttribute('data-title') || '';
    openBorrowModal(sid, title, this);
  });

  function openBorrowModal(isbn, title, openerEl) {
    const modal = document.getElementById('borrowModal');
    if (!modal) return;

    lastOpener = openerEl && document.body.contains(openerEl) ? openerEl : null;

    // populate fields
    document.getElementById('borrowBookDisplay').value = title || '';
    document.getElementById('borrow_isbn').value = isbn || '';

    const borrowEl = document.getElementById('borrow_date');
    const returnEl = document.getElementById('return_date');

    const today = new Date();

    // Allow choosing today or any future date (no max!)
    borrowEl.min = toInputDate(today);

    // Default borrow = today if empty
    if (!borrowEl.value) borrowEl.value = toInputDate(today);

    // Set initial bounds for return based on CURRENT borrow value
    setReturnBoundsFromBorrow(borrowEl, returnEl);

    // If return is empty or out of range, default to borrow+7
    const b = strToDate(borrowEl.value);
    const bPlus7 = addDays(b, 7);
    if (!returnEl.value) returnEl.value = toInputDate(bPlus7);
    clampReturnIfNeeded(borrowEl, returnEl);

    // show modal
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    document.getElementById('borrowCloseBtn').focus();
  }

  // --- bind once: when borrow date changes, update return bounds ---
  document.addEventListener('DOMContentLoaded', () => {
    const borrowEl = document.getElementById('borrow_date');
    const returnEl = document.getElementById('return_date');
    if (borrowEl && returnEl) {
      borrowEl.addEventListener('change', () => {
        // update min/max for return, then clamp value
        setReturnBoundsFromBorrow(borrowEl, returnEl);
        clampReturnIfNeeded(borrowEl, returnEl);
      });
    }
  });

  // --- helpers ---
  function setReturnBoundsFromBorrow(borrowEl, returnEl) {
    const b = strToDate(borrowEl.value || toInputDate(new Date()));
    const min = b;
    const max = addDays(b, 7);
    returnEl.min = toInputDate(min);
    returnEl.max = toInputDate(max);
  }

  function clampReturnIfNeeded(borrowEl, returnEl) {
    const b = strToDate(borrowEl.value);
    const max = addDays(b, 7);
    let r = returnEl.value ? strToDate(returnEl.value) : null;

    // if empty, set to b+7
    if (!r) {
      returnEl.value = toInputDate(max);
      return;
    }

    // clamp to [b, b+7]
    if (r < b) r = b;
    if (r > max) r = max;
    returnEl.value = toInputDate(r);
  }

  // yyyy-mm-dd helpers (avoid TZ issues)
  function toInputDate(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  function strToDate(s) { // "yyyy-mm-dd" -> Date (local)
    const [y, m, d] = s.split('-').map(Number);
    return new Date(y, m - 1, d);
  }

  function addDays(d, days) {
    const x = new Date(d.getFullYear(), d.getMonth(), d.getDate());
    x.setDate(x.getDate() + days);
    return x;
  }

  function closeBorrowModal() {
    const modal = document.getElementById('borrowModal');
    if (!modal) return;

    // 1) hide first
    modal.classList.remove('is-open'); // your CSS hides when .is-open is gone
    modal.setAttribute('aria-hidden', 'true');

    // 2) try to restore focus to opener if it still exists
    try {
      if (lastOpener && document.body.contains(lastOpener)) {
        lastOpener.focus();
      } else {
        // safe fallback: focus body (make sure body is focusable)
        document.body.setAttribute('tabindex', '-1');
        document.body.focus();
        // optional: remove tabindex after focusing
        setTimeout(() => document.body.removeAttribute('tabindex'), 0);
      }
    } catch (e) {
      // swallow any focus errors—modal is already hidden
    }
  }

  // Wire up close controls (X, Cancel, overlay)
  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('borrowModal');
    document.getElementById('borrowCloseBtn')?.addEventListener('click', closeBorrowModal);
    modal?.querySelector('[data-action="cancel"]')?.addEventListener('click', closeBorrowModal);
    modal?.querySelector('.modal__overlay')?.addEventListener('click', closeBorrowModal);

    // Esc to close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal?.classList.contains('is-open')) {
        closeBorrowModal();
      }
    });
  });
</script>

<script>
  function confirmBorrowModal() {
    console.log("Confirm Borrow");
    const form = $('#borrowForm')[0];
    const data = new FormData(form);

    // Get the selected sendInterval value from the form
    let borrowBookDisplay = data.get("borrowBookDisplay");
    let borrow_isbn = data.get("borrow_isbn");
    let borrow_date = data.get("borrow_date");
    let return_date = data.get("return_date");
    console.log("Selected Book:", borrowBookDisplay);
    console.log("Selected ISBN:", borrow_isbn);
    console.log("Selected Bdate:", borrow_date);
    console.log("Selected Rdate:", return_date);

    // minimal client-side check for 7-day window
    const bStr = $('#borrow_date').val();
    const rStr = $('#return_date').val();
    if (!bStr || !rStr) {
      $('#borrowInlineErr').text('Please complete all required fields.');
      return;
    }
    const b = new Date(bStr + 'T00:00:00');
    const r = new Date(rStr + 'T00:00:00');
    const days = Math.round((r - b) / (1000 * 60 * 60 * 24));
    if (days < 0 || days > 7) {
      $('#borrowInlineErr').text('Return date must be within 0–7 days from borrow date.');
      return;
    }

    $.ajax({
      type: "POST",
      enctype: 'multipart/form-data',
      url: "borrow_book.php",
      data: data,
      processData: false,
      contentType: false,
      cache: false,
      success: function(resp) {
        try {
          const res = typeof resp === 'string' ? JSON.parse(resp) : resp;
          if (res.status === 'success') {
            closeBorrowModal();
            if (window.booksTable) window.booksTable.ajax.reload(null, false);
            $("#promptSuccess").text(res.message || "Borrow recorded successfully!");
            if (typeof openSuccessModal === 'function') openSuccessModal();

            var message = new Messaging.Message("borrowed");
            message.destinationName = "LISA/User";
            message.qos = 0;
            client.send(message);

          } else {
            $('#borrowInlineErr').text(res.message || 'Failed to borrow book.');
          }
        } catch (e) {
          console.error("Parse error:", e);
          $('#borrowInlineErr').text('Server response error.');
        }
      },
      error: function(xhr) {
        console.error(xhr.responseText);
        $("#promptError").text("Failed to submit borrow request!");
        if (typeof openErrorModal === 'function') openErrorModal();
      }
    });
  }

  // Bind once
  $(document).on('submit', '#borrowForm', function(e) {
    e.preventDefault();
    confirmBorrowModal();
  });
</script>