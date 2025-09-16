<?php // penalties_script.php 
?>
<script>
  (function() {
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) {
      console.warn('DataTables not found. Load jQuery and DataTables before penalties_script.php');
      return;
    }

    if (jQuery.fn.dataTable.isDataTable('#penaltiesTable')) {
      jQuery('#penaltiesTable').DataTable().destroy(true);
    }

    // ===== Helpers =====
    const strip = s => (s || '').toString().replace(/<[^>]*>/g, '');
    const peso = n => '₱' + (Number(n || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }));

    function parseDMY(s) {
      // accepts dd/mm/yyyy
      const m = (s || '').match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
      if (!m) return null;
      const d = +m[1],
        mo = +m[2] - 1,
        y = +m[3];
      return new Date(y, mo, d);
    }

    function daysBetween(a, b) { // whole days
      const ms = 24 * 60 * 60 * 1000;
      return Math.floor((a - b) / ms);
    }

    function getSettings() {
      const ls = JSON.parse(localStorage.getItem('penalty_settings') || '{}');
      const rate = Number(document.getElementById('penaltyRate')?.value ?? ls.rate ?? 5);
      const grace = Number(document.getElementById('gracePeriod')?.value ?? ls.grace ?? 1);
      return {
        rate,
        grace
      };
    }

    function computeRow(tr, today = new Date()) {
      const tds = tr.querySelectorAll('td');
      const dueStr = strip(tds[2]?.innerHTML);
      const due = parseDMY(dueStr);
      if (!due) return {
        daysLate: 0,
        penalty: 0
      };

      const {
        rate,
        grace
      } = getSettings();
      const late = Math.max(0, daysBetween(today, due));
      const billable = Math.max(0, late - Math.max(0, grace));
      const amount = billable * Math.max(0, rate);
      return {
        daysLate: late,
        penalty: amount
      };
    }

    function refreshKPIs() {
      const rows = Array.from(document.querySelectorAll('#penaltiesTable tbody tr'));
      let total = 0,
        paid = 0,
        outstanding = 0;

      rows.forEach(tr => {
        const statusText = strip(tr.querySelector('td:nth-child(6)')?.innerText).toLowerCase();
        const penStr = strip(tr.querySelector('td:nth-child(5)')?.innerText).replace(/[₱,]/g, '');
        const val = Number(penStr || 0);
        total += val;
        if (statusText.includes('paid')) paid += val;
        else outstanding += val;
      });

      document.getElementById('kpiTotal').innerText = peso(total);
      document.getElementById('kpiPaid').innerText = peso(paid);
      document.getElementById('kpiOutstanding').innerText = peso(outstanding);
    }

    function recalcAll() {
      const today = new Date();
      document.querySelectorAll('#penaltiesTable tbody tr').forEach(tr => {
        const {
          daysLate,
          penalty
        } = computeRow(tr, today);
        tr.querySelector('td:nth-child(4)').innerText = daysLate;
        tr.querySelector('td:nth-child(5)').innerText = peso(penalty);
        // If no penalty, show Paid/OK style if already Paid; keep Unpaid if marked as such.
        const statusCell = tr.querySelector('td:nth-child(6)');
        const isPaid = /paid/i.test(statusCell?.innerText || '');
        if (!isPaid && penalty <= 0) {
          statusCell.innerHTML = '<span class="badge available">OK</span>';
        } else if (!isPaid && penalty > 0) {
          statusCell.innerHTML = '<span class="badge reserved">Unpaid</span>';
        }
      });
      refreshKPIs();
    }

    // ===== DataTable =====
    const dt = jQuery('#penaltiesTable').DataTable({
      dom: 't<"dt-footer"ip>',
      pageLength: 10,
      lengthChange: false,
      order: [],
      columnDefs: [{
          targets: [6],
          orderable: false
        } // Actions
      ]
    });

    // Initial calc
    recalcAll();

    // ===== Settings handlers =====
    const rateIn = document.getElementById('penaltyRate');
    const graceIn = document.getElementById('gracePeriod');

    function persistAndRecalc() {
      const rate = Number(rateIn?.value || 0);
      const grace = Number(graceIn?.value || 0);
      // (A) Persist locally for demo; replace with your PHP endpoint if needed.
      localStorage.setItem('penalty_settings', JSON.stringify({
        rate,
        grace
      }));

      // (B) If you want to post to server instead:
      // fetch('penalty_save_settings.php', { method:'POST', body: new URLSearchParams({ rate, grace }) });

      recalcAll();
    }

    rateIn?.addEventListener('change', persistAndRecalc);
    graceIn?.addEventListener('change', persistAndRecalc);
    document.getElementById('btnSaveSettings')?.addEventListener('click', persistAndRecalc);

    // On load, try to restore saved settings
    (() => {
      const saved = JSON.parse(localStorage.getItem('penalty_settings') || '{}');
      if (saved.rate != null) rateIn.value = saved.rate;
      if (saved.grace != null) graceIn.value = saved.grace;
      recalcAll();
    })();

    // ===== Row actions =====
    document.getElementById('penaltiesTable')?.addEventListener('click', (e) => {
      const btn = e.target.closest('button.icon-btn');
      if (!btn) return;

      const rowEl = btn.closest('tr');
      const row = dt.row(rowEl);

      if (btn.classList.contains('pay')) {
        // Mark as Paid
        const statusCell = rowEl.querySelector('td:nth-child(6)');
        statusCell.innerHTML = '<span class="badge available">Paid</span>';
        recalcAll();
        return;
      }

      if (btn.classList.contains('del')) {
        if (!confirm('Delete this penalty record?')) return;
        row.remove().draw();
        recalcAll();
        return;
      }
    });
  })();
</script>