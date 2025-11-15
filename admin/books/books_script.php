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
            const sidRaw = row.ISBN ?? '';
            const sid = String(sidRaw).replace(/"/g, '&quot;'); // escape quotes for attr
            return `
              <div class="row-actions">
                <button class="table-btn edit" data-sid="${sid}" title="Edit">Edit</button>
                <button class="table-btn del"  data-sid="${sid}" title="Delete">Delete</button>
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

    $table.on('click', '.table-btn.edit', function() {
      const sid = this.getAttribute('data-sid');
      alert('Edit book: ' + sid);
    });


    $table.on('click', '.table-btn.del', function() {
      const sid = this.getAttribute('data-sid'); // this is book_id (ISBN col)
      if (!sid) {
        alert('Missing book_id');
        return;
      }
      if (!confirm(`Delete book ${sid}? This cannot be undone.`)) return;

      fetch('delete_book.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            book_id: sid
          })
        })
        .then(r => r.json())
        .then(res => {
          if (!res || !res.ok) {
            alert(res?.error || 'Delete failed.');
            return;
          }
          // stay on same page of DataTable
          dt.ajax.reload(null, false);
          // toastr.success(`Deleted ${sid}`); // optional
        })
        .catch(() => alert('Server error while deleting.'));
    });

    // Use the existing dt variable from your DataTable init
    $table.on('click', '.table-btn.edit', function() {
      const tr = $(this).closest('tr');
      const row = dt.row(tr).data();
      if (!row) return;

      // row has { ISBN, Title, Author, Category, Status, ... }
      if (window.enterEditMode) {
        window.enterEditMode(row);
      } else {
        alert('Edit mode is not available. Check modal_script.js is loaded.');
      }
    });



  })();
</script>