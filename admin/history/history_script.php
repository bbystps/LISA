<script>
  (function() {
    const $table = $('#booksTable');

    const dt = $table.DataTable({
      processing: true,
      ajax: {
        url: 'history_data.php',
        dataSrc: 'data'
      },
      order: [
        [0, 'desc']
      ],
      scrollX: true,
      scrollCollapse: true,
      autoWidth: false,
      columns: [{
          data: 'id'
        }, // 0 hidden
        {
          data: 'student_id'
        }, // 1 hidden
        {
          data: 'book_id'
        }, // 2 hidden
        {
          data: 'Name',
          defaultContent: '-'
        }, // 3
        {
          data: 'Title',
          defaultContent: '-'
        }, // 4
        {
          data: 'Author',
          defaultContent: '-'
        }, // 5
        {
          data: 'Borrow Date',
          defaultContent: '-'
        }, // 6
        {
          data: 'Return Date',
          defaultContent: '-'
        }, // 7
        {
          data: 'Transaction Date',
          defaultContent: '-'
        }, // 8
        {
          data: 'Status',
          defaultContent: '-'
        }, // 9 
      ],
      columnDefs: [{
          targets: [0, 1, 2],
          visible: false,
          searchable: false
        },
        // {
        //   targets: [6, 7, 8],
        //   type: 'date'
        // },
        {
          targets: [6, 7, 8],
          orderable: false
        } // 👈 disables sort icons
      ]
    });

    window.transactionsTable = dt;

  })();
</script>