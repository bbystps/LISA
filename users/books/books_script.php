<script>
  (function() {
    const $table = $('#booksTable');

    // Small debounce for search box
    const debounce = (fn, ms = 250) => {
      let t;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(null, args), ms);
      };
    };

    // Init DataTable
    const dt = $table.DataTable({
      processing: true,
      ajax: {
        url: 'books_data.php',
        dataSrc: 'data'
      },
      order: [
        [0, 'asc']
      ],
      scrollX: true, // ← enable horizontal scroll
      scrollCollapse: true, // ← collapse when not needed
      autoWidth: false, // ← let us control widths + avoid odd stretching
      columns: [{
          data: 'ISBN'
        }, // 0 Hidden
        {
          data: 'Title'
        }, // 1
        {
          data: 'Author'
        }, // 2
        {
          data: 'Category'
        }, // 3
        {
          data: 'Status'
        }, // 4
        {
          data: null,
          orderable: false,
          searchable: false,
          width: 140,
          render: function(data, type, row) {
            const sid = String(row.ISBN ?? '').replace(/"/g, '&quot;');
            const title = String(row.Title ?? '').replace(/"/g, '&quot;');

            const disabled = (row.Status === "Borrowed" || row.Status === "Reserved");
            const btnText = disabled ? "Unavailable" : "Borrow";

            return `
              <div class="row-actions">
                <button class="table-btn borrow"
                        data-sid="${sid}"
                        data-title="${title}"
                        title="Borrow"
                        ${disabled ? "disabled" : ""}>
                  ${btnText}
                </button>
              </div>`;
          }
        } // 5 
      ],
      columnDefs: [{
          targets: 0,
          visible: false,
          searchable: false
        }, // hide RFID
      ],
      // Responsive/nowrap is already in your table classes
      drawCallback: function() {
        // (Optional) hook for post-draw tweaks
      }
    });

    // Make sure widths compute correctly at start and on resize
    dt.columns.adjust();
    $(window).on('resize', debounce(() => dt.columns.adjust(), 150));

    // Expose the table so modal_script can reload after create
    window.booksTable = dt;

    // $table.on('click', '.table-btn.borrow', function() {
    //   console.log("clicked");
    //   const sid = this.getAttribute('data-sid');
    //   const title = this.getAttribute('data-title');
    //   openBorrowModal(sid, title);
    // });
  })();
</script>