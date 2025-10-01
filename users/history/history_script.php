<script>
  (function() {
    const $table = $('#booksTable');

    const dt = $table.DataTable({
      processing: true,
      ajax: {
        // Adjust path if this file lives in /admin/history/
        url: 'history_data.php',
        dataSrc: 'data'
      },
      // Default sort: most recent transaction first
      order: [
        [5, 'desc']
      ],
      scrollX: true,
      scrollCollapse: true,
      autoWidth: false,
      columns: [{
          data: 'Title',
          defaultContent: '-'
        }, // 0
        {
          data: 'Category',
          defaultContent: '-'
        }, // 1
        {
          data: 'Borrow Date',
          defaultContent: '-'
        }, // 2
        {
          data: 'Return Date',
          defaultContent: '-'
        }, // 3
        {
          data: 'Return Status',
          defaultContent: '-'
        }, // 4
        {
          data: 'Transact Date',
          defaultContent: '-'
        } // 5
      ],
      columnDefs: [
        // Optional: render status as a small pill
        {
          targets: 4,
          render: function(data, type) {
            if (type !== 'display') return data;
            const txt = (data || '').toString();
            const cls =
              txt.includes('Borrowed') ? 'pill-borrowed' :
              txt.includes('Reserved') ? 'pill-reserved' :
              txt.includes('Available') ? 'pill-available' : 'pill-neutral';
            return `<span class="pill ${cls}">${txt || '-'}</span>`;
          }
        }
      ],
      drawCallback: function() {
        // hook if you need anything after draw
      }
    });

    // Keep columns sized correctly on resize
    const debounce = (fn, ms = 150) => {
      let t;
      return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), ms);
      };
    };
    $(window).on('resize', debounce(() => dt.columns.adjust(), 150));

    // If other scripts need to trigger a reload:
    window.historyTable = dt;
  })();
</script>