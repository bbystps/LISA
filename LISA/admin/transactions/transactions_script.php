<?php // transactions_script.php 
?>
<script>
  (function() {
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) {
      console.warn('DataTables not found. Load jQuery and DataTables before transactions_script.php');
      return;
    }

    // Destroy if already initialized (hot reload/PJAX)
    if (jQuery.fn.dataTable.isDataTable('#transactionsTable')) {
      jQuery('#transactionsTable').DataTable().destroy(true);
    }

    // Helper: strip HTML
    const strip = (s) => (s || '').toString().replace(/<[^>]*>/g, '');

    // Helper: parse date shown as dd/mm/yyyy or yyyy-mm-dd -> yyyy-mm-dd
    function normalizeDate(input) {
      if (!input) return '';
      const s = input.trim();
      // yyyy-mm-dd (native date input)
      if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
      // dd/mm/yyyy -> yyyy-mm-dd
      const m = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
      if (m) {
        const [, d, mo, y] = m;
        const dd = String(d).padStart(2, '0');
        const mm = String(mo).padStart(2, '0');
        return `${y}-${mm}-${dd}`;
      }
      return ''; // unknown format
    }

    // Add a custom date filter (applies only to this table)
    let txDateFilter = '';
    const tableId = 'transactionsTable';
    if (!window._txFilterAdded) {
      jQuery.fn.dataTable.ext.search.push(function(settings, data) {
        if (settings.nTable && settings.nTable.id !== tableId) return true; // other tables unaffected
        if (!txDateFilter) return true;
        // Date column is index 0
        const cell = strip(data[0] || '');
        const normCell = normalizeDate(cell);
        return normCell === txDateFilter;
      });
      window._txFilterAdded = true;
    }

    const dt = jQuery('#transactionsTable').DataTable({
      dom: 't<"dt-footer"ip>',
      pageLength: 10,
      lengthChange: false,
      order: [],
      columnDefs: [{
          targets: [7],
          orderable: false
        } // Actions
      ]
    });

    // Controls
    const q = document.getElementById('txSearch');
    const selType = document.getElementById('txType');
    const dateIn = document.getElementById('txDate');
    const btnFil = document.getElementById('btnFilter');
    const btnExp = document.getElementById('btnExport');

    function applyFilters() {
      // Global search
      dt.search(q?.value || '');

      // Type filter (column index 3)
      const t = selType?.value || '';
      const esc = t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      dt.column(3).search(t ? `^${esc}$` : '', true, false);

      // Date equality filter
      txDateFilter = normalizeDate(dateIn?.value || dateIn?.getAttribute('value') || dateIn?.placeholder || '');
      // If the input is blank or unparsable, clear the filter
      if (!dateIn?.value && txDateFilter === normalizeDate('dd/mm/yyyy')) txDateFilter = '';

      dt.draw();
    }

    // Trigger filter
    btnFil?.addEventListener('click', applyFilters);
    q?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') applyFilters();
    });
    selType?.addEventListener('change', applyFilters);
    dateIn?.addEventListener('change', applyFilters);

    // Export CSV (current view)
    btnExp?.addEventListener('click', () => {
      // columns 0..6 (exclude actions)
      const headers = ['Date', 'Student', 'Book', 'Type', 'Due Date', 'Status', 'Penalty'];
      const rows = [];
      dt.rows({
        search: 'applied'
      }).every(function() {
        const d = this.data();
        rows.push([
          strip(d[0]), strip(d[1]), strip(d[2]),
          strip(d[3]), strip(d[4]), strip(d[5]), strip(d[6])
        ]);
      });

      const csv = [headers, ...rows].map(r =>
        r.map(v => {
          const s = (v ?? '').toString().replace(/"/g, '""');
          return /[",\n]/.test(s) ? `"${s}"` : s;
        }).join(',')
      ).join('\n');

      const blob = new Blob(["\ufeff" + csv], {
        type: 'text/csv;charset=utf-8;'
      }); // BOM for Excel
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      const today = new Date();
      const y = today.getFullYear(),
        m = String(today.getMonth() + 1).padStart(2, '0'),
        d = String(today.getDate()).padStart(2, '0');
      a.href = url;
      a.download = `transactions_export_${y}${m}${d}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });

    // Row actions
    document.getElementById('transactionsTable')?.addEventListener('click', (e) => {
      const btn = e.target.closest('button.icon-btn');
      if (!btn) return;

      const rowEl = btn.closest('tr');
      const row = dt.row(rowEl);
      const d = row.data();

      const date = strip(d?.[0]);
      const student = strip(d?.[1]);
      const book = strip(d?.[2]);

      if (btn.classList.contains('view')) {
        alert(`Transaction details:\n${date} — ${student} — ${book}`);
        return;
      }
      if (btn.classList.contains('del')) {
        if (!confirm('Delete this transaction record?')) return;
        // TODO: call your API; on success:
        row.remove().draw();
        return;
      }
    });
  })();
</script>