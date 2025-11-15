<script>
  (function() {
    // --- HTML escaper ---
    function escapeHtml(s) {
      if (s == null) return '';
      return String(s)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    // --- Row builder ---
    function itemTemplate(it) {
      const isBorrowed = it.status === 'Borrowed';
      const isReserved = it.status === 'Reserved';

      const statusBadge = isBorrowed ?
        '<span class="badge badge-borrowed">Borrowed</span>' :
        isReserved ?
        '<span class="badge badge-reserved">Reserved</span>' :
        `<span class="badge">${escapeHtml(it.status || 'Active')}</span>`;

      let rightBtn = '';
      if (isBorrowed) {
        rightBtn = `<button class="btn outline table-btn" data-action="return" data-tx="${escapeHtml(it.tx_id)}">Return</button>`;
      } else if (isReserved) {
        rightBtn = `<button class="btn outline table-btn" data-action="cancel" data-tx="${escapeHtml(it.tx_id)}">Cancel</button>`;
      } else {
        rightBtn = '<button class="btn outline table-btn" disabled>—</button>';
      }

      let dates = '';
      if (isBorrowed) {
        dates = ` · Borrowed: ${escapeHtml(it.borrow_date ?? '')} · Due: <b>${escapeHtml(it.return_date ?? '')}</b>`;
      } else if (isReserved) {
        dates = ` · Reserved: ${escapeHtml(it.borrow_date ?? '')} · Pickup within: <b>${escapeHtml(it.return_date ?? '')}</b>`;
      } else if (it.transaction_date) {
        dates = ` · Updated: ${escapeHtml(it.transaction_date)}`;
      }

      return `
        <div
          class="item"
          data-status="${escapeHtml(it.status)}"
          data-book-id="${escapeHtml(it.book_id)}"
          data-tx="${escapeHtml(it.tx_id)}"
          data-title="${escapeHtml(it.title)}"
          data-author="${escapeHtml(it.author)}"
          data-category="${escapeHtml(it.category)}"
          data-borrow-date="${escapeHtml(it.borrow_date ?? '')}"
          data-return-date="${escapeHtml(it.return_date ?? '')}"
        >
          <div>
            <strong>${escapeHtml(it.title)}</strong><br>
            <span class="meta">
              Book ID: ${escapeHtml(it.book_id)} · Author: ${escapeHtml(it.author)} · Category: ${escapeHtml(it.category)}
            </span><br>
            <span class="meta">Status: ${statusBadge}${dates}</span>
          </div>
          <div style="display:flex; gap:8px; align-items:center;">
            ${rightBtn}
          </div>
        </div>
      `;
    }


    function ensureCounterEl() {
      let el = document.querySelector('.borrow-transact');
      if (!el) {
        let right = document.querySelector('.toolbar .right');
        if (!right) {
          right = document.createElement('div');
          right.className = 'right';
          const toolbar = document.querySelector('.toolbar');
          if (toolbar) toolbar.appendChild(right);
        }
        el = document.createElement('div');
        el.className = 'borrow-transact';
        el.textContent = 'Active items: 0/3';
        right.appendChild(el);
      }
      return el;
    }

    function setLoadingState() {
      const box = document.getElementById('my-books');
      if (box) {
        box.innerHTML = `
        <div class="item">
          <div>
            <strong>Loading...</strong><br>
            <span class="meta">Please wait while we fetch your active books.</span>
          </div>
        </div>
      `;
      }
    }

    function render(data) {
      const box = document.getElementById('my-books');
      if (!box) return;

      if (!data.items || data.items.length === 0) {
        box.innerHTML = `<div class="empty">You have no active items. You can have up to ${data.limit} active at a time.</div>`;
      } else {
        box.innerHTML = data.items.map(itemTemplate).join('');
      }

      // Slots-left (ALL ACTIVE)
      const slots = document.getElementById('slots-left');
      if (slots) {
        const slotsLeft = Math.max(0, (data.slots_left ?? (data.limit - data.count)));
        slots.innerHTML = `Slots left: <b>${slotsLeft}</b> / ${data.limit}`;
      }

      // Top toolbar counter (ALL ACTIVE)
      const counterEl = ensureCounterEl();
      counterEl.textContent = `Active items: ${data.count}/${data.limit}`;
    }

    function renderError(msg) {
      const box = document.getElementById('my-books');
      if (box) {
        box.innerHTML = `
        <div class="item">
          <div>
            <strong>Unable to load</strong><br>
            <span class="meta">${escapeHtml(msg || 'Server error.')}</span>
          </div>
        </div>
      `;
      }
      const counterEl = ensureCounterEl();
      counterEl.textContent = 'Active items: 0/3';
      const slots = document.getElementById('slots-left');
      if (slots) slots.innerHTML = 'Slots left: <b>3</b> / 3';
    }

    // --- fetch active list ---
    function loadMyActive() {
      setLoadingState();
      $.ajax({
        type: 'POST',
        url: 'get_my_active.php',
        data: {},
        cache: false,
        success: function(resp) {
          try {
            const res = (typeof resp === 'string') ? JSON.parse(resp) : resp;
            if (res.status === 'success') {
              render(res);
            } else {
              renderError(res.message || 'Failed to fetch data.');
            }
          } catch (e) {
            console.error('Parse error', e);
            renderError('Invalid server response.');
          }
        },
        error: function(xhr) {
          console.error(xhr.responseText);
          renderError('Network error. Please try again.');
        }
      });
    }

    // --- handle Return/Cancel clicks using your AJAX format ---
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('#my-books .table-btn');
      if (!btn) return;

      const action = btn.getAttribute('data-action'); // 'return' | 'cancel'
      if (!action) return;

      const row = btn.closest('.item');
      const txId = btn.getAttribute('data-tx') || row?.getAttribute('data-tx');
      if (!txId) return;

      // --- Build detail message from data-* ---
      const title = row?.dataset.title || '';
      const bookId = row?.dataset.bookId || '';
      const author = row?.dataset.author || '';
      const category = row?.dataset.category || '';
      const status = row?.dataset.status || '';
      const borrowDt = row?.dataset.borrowDate || '';
      const returnDt = row?.dataset.returnDate || '';

      let detailLines = [
        `Action   : ${action.toUpperCase()}`,
        `Title    : ${title}`,
        `Book ID  : ${bookId}`,
        `Author   : ${author}`,
        `Category : ${category}`,
        `Status   : ${status}`
      ];

      if (status === 'Borrowed') {
        detailLines.push(`Borrowed : ${borrowDt}`);
        detailLines.push(`Due      : ${returnDt}`);
      } else if (status === 'Reserved') {
        detailLines.push(`Reserved : ${borrowDt}`);
        detailLines.push(`Pickup   : ${returnDt}`);
      }

      const msg = detailLines.join('\n');

      // If you want a simple alert (no choice), swap confirm(...) with alert(...) and remove the if (!ok) guard.
      const ok = confirm(msg + '\n\nProceed?');
      if (!ok) return;

      // Optimistic per-row loading state
      const oldHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = 'Processing...';

      $.ajax({
        type: 'POST',
        url: 'status_update.php',
        data: {
          book_id: row?.dataset.bookId, // explicit book_id
          action: action // 'return' or 'cancel'
        },
        success: function(resp) {
          try {
            const res = (typeof resp === 'string') ? JSON.parse(resp) : resp;
            if (res.status === 'success') {
              // Re-fetch to keep counters/rows accurate

              loadMyActive();

              var message = new Messaging.Message("returned");
              message.destinationName = "LISA/User";
              message.qos = 0;
              client.send(message);
            } else {
              alert(res.message || 'Action failed.');
              btn.disabled = false;
              btn.innerHTML = oldHtml;
            }
          } catch (err) {
            console.error(err);
            alert('Invalid server response.');
            btn.disabled = false;
            btn.innerHTML = oldHtml;
          }
        },
        error: function(xhr) {
          console.error(xhr.responseText);
          alert('Network error. Please try again.');
          btn.disabled = false;
          btn.innerHTML = oldHtml;
        }
      });
    });

    // First load
    loadMyActive();
  })();
</script>