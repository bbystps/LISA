<script>
  (function($) {
    function escapeHtml(s) {
      return String(s)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
    }

    function fmtDateTime(dt) {
      // dt is like "2025-09-28 16:54:42"
      if (!dt) return '';
      // Show as "Sep 28, 2025 16:54"
      const d = new Date(dt.replace(' ', 'T')); // safe enough for our case
      if (isNaN(d)) return escapeHtml(dt);
      return d.toLocaleString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
      });
    }

    function fmtDate(dstr) {
      // "2025-10-02" -> "Oct 02, 2025"
      if (!dstr) return '';
      const d = new Date(dstr);
      if (isNaN(d)) return escapeHtml(dstr);
      return d.toLocaleDateString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric'
      });
    }

    function renderNewTransactions(list) {
      // Find the "New Transactions" panel
      const $panel = $('.grid .card .panel:contains("New Transactions")').first();
      // Clear everything except the <h3>
      $panel.children('.item, .empty').remove();

      if (!list || list.length === 0) {
        $panel.append('<div class="empty">No active transactions</div>');
        return;
      }

      list.forEach(row => {
        const title = escapeHtml(row.book_title || row.book_id);
        const name = escapeHtml(row.student_name || '');
        const sid = escapeHtml(row.student_id || '');
        const ttime = fmtDateTime(row.transaction_date);
        const status = escapeHtml(row.status || '');
        const due = row.status === 'Borrowed' || row.status === 'Delivering' || row.status === 'Fetching' || row.status === 'Returning' ?
          `Due: ${fmtDate(row.return_date)}` :
          (row.status === 'Returned' ? `Returned: ${fmtDate(row.return_date)}` : '');

        $panel.append(`
        <div class="item" data-id="${row.id}">
          <div>
            <div><strong>${title}</strong></div>
            <div class="meta">Borrowed by ${name} (${sid})</div>
          </div>
          <div class="meta">
            ${ttime}
            <div>${escapeHtml(due)}</div>
          </div>
        </div>
      `);
      });
    }

    function renderOverdue(list) {
      // Find the "Overdue Books" panel
      const $panel = $('.grid .card .panel:contains("Overdue Books")').first();
      $panel.children('.item, .empty').remove();

      if (!list || list.length === 0) {
        $panel.append('<div class="empty">No overdue books</div>');
        return;
      }

      list.forEach(row => {
        const title = escapeHtml(row.book_title || row.book_id);
        const name = escapeHtml(row.student_name || '');
        const sid = escapeHtml(row.student_id || '');
        const due = fmtDate(row.return_date);

        $panel.append(`
        <div class="item" data-id="${row.id}">
          <div>
            <div><strong>${title}</strong></div>
            <div class="meta">Borrowed by ${name} (${sid})</div>
          </div>
          <div class="meta">Due: ${due}</div>
        </div>
      `);
      });
    }

    function applyKpis(kpis) {
      if (!kpis) return;
      $('[data-kpi="total-books"]').text((kpis.total_books ?? 0).toLocaleString());
      $('[data-kpi="active-students"]').text((kpis.active_students ?? 0).toLocaleString());
      $('[data-kpi="borrowed-now"]').text((kpis.borrowed_now ?? 0).toLocaleString());
      const overdueNow = (kpis.overdue_now ?? 0);
      $('[data-kpi="overdue-now"]').text(overdueNow.toLocaleString())
        .css('color', overdueNow > 0 ? '#dc2626' : '');
    }

    function loadDashboard() {
      // Using your requested POST style with an id field
      $.ajax({
        type: 'POST',
        url: 'dashboard_data.php', // adjust path if needed; from /admin/pages/dashboard/
        data: {
          id: 0,
          action: 'load'
        },
        success: function(resp) {
          if (!resp || !resp.success) {
            toastr.error(resp && resp.error ? resp.error : 'Failed to load dashboard.');
            return;
          }
          applyKpis(resp.kpis);
          renderNewTransactions(resp.new_transactions);
          renderOverdue(resp.overdue);
        },
        error: function(xhr) {
          toastr.error(xhr.responseText || 'Server error while loading dashboard.');
        }
      });
    }

    $(document).ready(function() {
      loadDashboard();
      // Optional: auto-refresh every 60s
      // setInterval(loadDashboard, 60000);
    });
  })(jQuery);
</script>