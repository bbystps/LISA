<?php // /admin/pages/penalties_script.php 
?>
<script>
  (function($) {
    function peso(n) {
      return '₱' + (Number(n || 0).toFixed(2));
    }

    function esc(s) {
      return String(s ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
    }

    function fmtPHDate(d) {
      if (!d) return '';
      const x = new Date(d);
      if (isNaN(x)) return esc(d);
      return x.toLocaleDateString('en-PH');
    }

    let dt = null;

    function loadData() {
      $.ajax({
        type: 'POST',
        url: 'penalties_data.php', // NOTE: page is in /admin/pages/
        data: {
          id: 0,
          action: 'load'
        },
        success: function(resp) {
          if (!resp?.success) {
            alert(resp?.error || 'Failed to load');
            return;
          }

          // KPIs
          $('#kpiTotal').text(peso(resp.kpis.total));
          $('#kpiPaid').text(peso(resp.kpis.paid));
          $('#kpiOutstanding').text(peso(resp.kpis.outstanding));

          // Settings
          if (resp.settings) {
            $('#penaltyRate').val(resp.settings.daily_rate);
            $('#gracePeriod').val(resp.settings.grace_days);
          }

          // Table
          renderTable(resp.rows || []);
        },
        error: function(xhr) {
          alert(xhr.responseText || 'Server error.');
        }
      });
    }

    function renderTable(rows) {
      const data = rows.map(r => {
        const actions = `
        <div class="actions">
          <button class="icon-btn pay" data-id="${r.id}" title="Mark as Paid" ${r.status==='Paid'?'disabled':''}>✓</button>
          <button class="icon-btn del" data-id="${r.id}" title="Delete">🗑</button>
        </div>
      `;
        return [
          esc(r.student_name) + ' (' + esc(r.student_id) + ')',
          esc(r.book_title) + ' (' + esc(r.book_id) + ')',
          fmtPHDate(r.due_date),
          esc(r.days_late),
          peso(r.amount),
          (r.status === 'Paid' ?
            '<span class="badge available">Paid</span>' :
            '<span class="badge reserved">Unpaid</span>'),
          actions
        ];
      });

      if (dt) {
        dt.clear().rows.add(data).draw();
        return;
      }
      $('#penaltiesTable tbody').empty();

      dt = $('#penaltiesTable').DataTable({
        data,
        columns: [{
            title: 'Student'
          },
          {
            title: 'Book'
          },
          {
            title: 'Due Date'
          },
          {
            title: 'Days Late'
          },
          {
            title: 'Penalty'
          },
          {
            title: 'Status'
          },
          {
            title: 'Actions',
            orderable: false,
            searchable: false,
            width: '140px'
          }
        ],
        pageLength: 10,
        order: [
          [5, 'asc'],
          [2, 'asc']
        ]
      });

      // Actions
      $('#penaltiesTable').on('click', '.icon-btn.pay', function() {
        const id = $(this).data('id');
        $.ajax({
          type: 'POST',
          url: 'update.php',
          data: {
            id,
            action: 'mark_paid'
          },
          success: function(resp) {
            if (!resp?.success) {
              alert(resp?.error || 'Failed');
              return;
            }
            loadData();
          },
          error: function(xhr) {
            alert(xhr.responseText || 'Server error.');
          }
        });
      });

      $('#penaltiesTable').on('click', '.icon-btn.del', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this penalty record?')) return;
        $.ajax({
          type: 'POST',
          url: 'update.php',
          data: {
            id,
            action: 'delete'
          },
          success: function(resp) {
            if (!resp?.success) {
              alert(resp?.error || 'Failed');
              return;
            }
            loadData();
          },
          error: function(xhr) {
            alert(xhr.responseText || 'Server error.');
          }
        });
      });
    }

    // Save Settings
    $('#btnSaveSettings').on('click', function() {
      const rate = parseFloat($('#penaltyRate').val() || '0');
      const grace = parseInt($('#gracePeriod').val() || '0', 10);

      $.ajax({
        type: 'POST',
        url: 'update.php',
        data: {
          id: 0,
          action: 'save_settings',
          rate,
          grace
        },
        success: function(resp) {
          if (!resp?.success) {
            alert(resp?.error || 'Failed to save');
            return;
          }
          loadData(); // reload KPIs + rows with new settings (if you recalc on server later)
        },
        error: function(xhr) {
          alert(xhr.responseText || 'Server error.');
        }
      });
    });

    $(document).ready(loadData);
  })(jQuery);
</script>